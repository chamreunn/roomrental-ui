<?php

namespace App\Http\Controllers;

use App\Enum\Active;
use App\Enum\InvoiceStatus;
use App\Enum\RoomStatus;
use App\Enum\Status;
use App\Utils\Util;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        // Get location detail
        $locationResponse = $this->api()->get("v1/locations");
        $locations = $locationResponse['locations']['data'] ?? null;

        return view('app.rooms.index', compact('locations'));
    }

    public function rooms(Request $request, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('room.index'),
            ],
        ];

        $colors = ['primary', 'success', 'warning', 'info', 'danger', 'purple', 'teal', 'orange'];

        try {
            $perPage = $request->query('per_page', 10);
            $currentPage = $request->query('page', 1);

            // ✅ Correct API call (header is the 4th argument)
            $response = $this->api()->get('v1/rooms', $request->query(), null, [
                'Location-Id' => $locationId,
            ]);

            // ✅ Extract rooms data correctly
            $roomsData = $response['rooms'] ?? [];

            // ✅ Use the API’s own pagination data if available
            $paginatedData = $roomsData['data'] ?? [];
            $total = $roomsData['total'] ?? count($paginatedData);
            $perPage = $roomsData['per_page'] ?? $perPage;
            $currentPage = $roomsData['current_page'] ?? $currentPage;

            // ✅ Transform each room entry
            $dataCollection = collect($paginatedData)->transform(function ($item) {
                $item['status_badge'] = RoomStatus::getStatus($item['status']); // Use 'status' not 'is_active'
                $item['create_date_kh'] = Util::translateDateToKhmer($item['created_at'], 'd F, Y h:i A');
                $item['update_date_kh'] = Util::translateDateToKhmer($item['updated_at'], 'd F, Y h:i A');

                // Optional: attach location and type info more conveniently
                $item['location_name'] = $item['location']['location_name'] ?? '-';
                $item['type_name'] = $item['room_type']['type_name'] ?? '-';
                $item['room_size'] = $item['room_type']['room_size'] ?? '-';
                $item['price'] = $item['room_type']['price'] ?? '-';

                return $item;
            });

            // ✅ Manual pagination for the frontend
            $rooms = new LengthAwarePaginator(
                $dataCollection,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => url()->current(),
                    'query' => $request->query(),
                ]
            );

        } catch (Exception $e) {
            // Handle gracefully if API fails
            $rooms = new LengthAwarePaginator([], 0, 10);
            Log::error('Room fetch failed: ' . $e->getMessage());
        }

        return view('app.rooms.room', compact('buttons', 'rooms', 'locationId', 'colors'));
    }

    public function location(Request $request)
    {
        // Get location detail
        $locationResponse = $this->api()->get("v1/locations");
        $locations = $locationResponse['locations']['data'] ?? null;

        return view('app.rooms.choose_location', compact('locations'));
    }

    public function create(Request $request, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('room.choose_location'),
            ],
        ];

        // Get location detail
        $roomtypeResponse = $this->api()->get("v1/room-types");
        $roomtypes = $roomtypeResponse['room_types']['data'] ?? null;

        return view('app.rooms.create', compact('buttons', 'locationId', 'roomtypes'));
    }

    public function store(Request $request, $locationId)
    {
        // ✅ Validate request
        $validated = $request->validate([
            'building_name' => 'required|string|max:255',
            'floor_name' => 'required|string|max:255',
            'room_name' => 'required|string|max:255',
            'room_type_id' => 'required|uuid',
            'description' => 'nullable|string|max:1000',
        ]);

        // ✅ Prepare payload
        $payload = [
            'building_name' => $validated['building_name'],
            'floor_name' => $validated['floor_name'],
            'room_name' => $validated['room_name'],
            'room_type_id' => $validated['room_type_id'],
            'description' => $validated['description'] ?? null,
            'created_by' => Session::get('user')['id'] ?? null,
            'updated_by' => Session::get('user')['id'] ?? null,
        ];

        // ✅ Send API request with locationId in header
        $apiResponse = $this->api()->post(
            'v1/rooms',
            $payload,
            token: null,
            asForm: false,
            files: [],
            fileField: 'documents[]',
            moreHeaders: ['Location-Id' => $locationId]
        );

        // ✅ Handle success
        if (($apiResponse['status'] ?? '') === 'success') {
            return redirect()
                ->route('room.index', $locationId)
                ->with('success', __('room.created_successfully'));
        }

        // ❌ Handle failure
        return back()
            ->withInput()
            ->with('error', $apiResponse['errors']['room_name'] ?? __('room.create_failed'));
    }

    public function edit(Request $request, $roomId, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('room.room_list', $locationId), // <-- go back to previous page
            ],
        ];

        $color = $request->query('color');

        // Get location detail
        $roomtypeResponse = $this->api()->get("v1/room-types");
        $roomtypes = $roomtypeResponse['room_types']['data'] ?? null;

        $roomResponse = $this->api()->withHeaders(['Location-Id' => $locationId])->get("v1/rooms/{$roomId}");
        $room = $roomResponse['room'];

        return view('app.rooms.edit', compact('buttons', 'locationId', 'roomtypes', 'room', 'color'));
    }

    public function update(Request $request, $roomId, $locationId)
    {
        // ✅ Validate request
        $validated = $request->validate([
            'building_name' => 'required|string|max:255',
            'floor_name' => 'required|string|max:100',
            'room_name' => 'required|string|max:100',
            'room_type_id' => 'required|uuid',
            'description' => 'nullable|string|max:500',
        ], [
            'building_name.required' => __('room.building_name_required'),
            'building_name.string' => __('room.building_name_string'),
            'building_name.max' => __('room.building_name_max'),

            'floor_name.required' => __('room.floor_name_required'),
            'floor_name.string' => __('room.floor_name_string'),
            'floor_name.max' => __('room.floor_name_max'),

            'room_name.required' => __('room.name_required'),
            'room_name.string' => __('room.name_string'),
            'room_name.max' => __('room.name_max'),

            'room_type_id.required' => __('roomtype.select_required'),
            'room_type_id.uuid' => __('roomtype.select_invalid'),

            'description.string' => __('room.description_string'),
            'description.max' => __('room.description_max'),
        ]);

        // ✅ Prepare payload for API
        $payload = [
            '_method' => 'PATCH', // If your API expects PATCH
            'building_name' => $validated['building_name'],
            'floor_name' => $validated['floor_name'],
            'room_name' => $validated['room_name'],
            'room_type_id' => $validated['room_type_id'],
            'description' => $validated['description'] ?? null,
            'updated_by' => Session::get('user')['id'] ?? null,
        ];

        try {
            // ✅ Send to API (assuming your helper $this->api() is a wrapper for HTTP client)
            $apiResponse = $this->api()
                ->post(
                    "v1/rooms/{$roomId}",
                    $payload,
                    token: null,
                    asForm: false,
                    files: [],
                    fileField: 'documents[]',
                    moreHeaders: ['Location-Id' => $locationId]
                );

            if (($apiResponse['status'] ?? '') === 'success') {
                return redirect()->back()->with('success', __('room.updated_successfully'));
            }

            // ❌ API returned failure
            return back()
                ->withInput()
                ->withErrors($apiResponse['errors'] ?? [
                    'error' => $apiResponse['message'] ?? __('room.update_failed')
                ]);
        } catch (Exception $e) {
            // ❌ Handle network or other exceptions
            return back()
                ->withInput()
                ->withErrors(['error' => __('room.update_failed')]);
        }
    }

    public function show(Request $request, $roomId, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('room.room_list', $locationId),
            ],
        ];

        $roomResponse = $this->api()
            ->withHeaders(['Location-Id' => $locationId])
            ->get("v1/rooms/{$roomId}");

        $room = $roomResponse['room'] ?? null;

        if (!$room) {
            abort(404, __('room.not_found'));
        }

        $roomstatus = RoomStatus::getStatus($room['status'] ?? null);
        $statuses = RoomStatus::all();
        $inactive = Active::INACTIVE;

        $clients = collect($room['clients'] ?? [])
            ->map(fn($client) => $this->formatRoomClient($client, $locationId))
            ->values();

        $allInvoices = $clients
            ->flatMap(fn($client) => $client['invoices_formatted'] ?? [])
            ->filter()
            ->values();

        $latestInvoice = $allInvoices
            ->sortByDesc(fn($invoice) => $invoice['invoice_date_raw'] ?? null)
            ->first();

        $latestInvoiceStatus = $latestInvoice['status_meta'] ?? null;

        $clientStats = [
            'total' => $clients->count(),
            'nearly_end' => $clients->where('nearly_end', true)->count(),
            'expired' => $clients->where('is_expired', true)->count(),
            'subclients' => $clients->sum(fn($client) => count($client['subclients_formatted'] ?? [])),
            'documents' => $clients->sum(fn($client) => count($client['documents_formatted'] ?? [])),
            'invoices' => $clients->sum(fn($client) => count($client['invoices_formatted'] ?? [])),
        ];

        return view('app.rooms.show', compact(
            'room',
            'buttons',
            'roomstatus',
            'statuses',
            'clients',
            'inactive',
            'locationId',
            'latestInvoice',
            'latestInvoiceStatus',
            'clientStats'
        ));
    }

    private function formatRoomClient(array $client, string $locationId): array
    {
        $client['clientstatus'] = Active::getStatus($client['status'] ?? null);

        $client['client_image_url'] = $this->apiImageUrl(
            $client['client_image'] ?? null,
            'images/default-avatar.png'
        );

        $client['gender_text'] = $this->genderText($client['gender'] ?? null);

        $client['dob_text'] = $this->dateText($client['date_of_birth'] ?? null);
        $client['start_text'] = $this->dateText($client['start_rental_date'] ?? null);
        $client['end_text'] = $this->dateText($client['end_rental_date'] ?? null);

        $daysLeft = $this->daysLeft($client['end_rental_date'] ?? null);

        $client['days_left'] = $daysLeft;
        $client['nearly_end'] = false;
        $client['is_expired'] = false;
        $client['dot_color'] = 'bg-success';
        $client['alert_text_class'] = 'text-success';
        $client['alert_message'] = __('room.rental_active');

        if (!is_null($daysLeft)) {
            if ($daysLeft < 0) {
                $client['nearly_end'] = true;
                $client['is_expired'] = true;
                $client['dot_color'] = 'bg-danger';
                $client['alert_text_class'] = 'text-danger';
                $client['alert_message'] = __('room.rental_expired');
            } elseif ($daysLeft <= 7) {
                $client['nearly_end'] = true;
                $client['dot_color'] = 'bg-warning';
                $client['alert_text_class'] = 'text-warning';
                $client['alert_message'] = trans_choice('room.rental_ending', $daysLeft, [
                    'days' => $daysLeft,
                ]);
            }
        }

        $client['edit_url'] = !empty($client['id'])
            ? route('clients.edit', [
                'id' => $client['id'],
                'locationId' => $locationId,
            ])
            : null;

        $client['documents_formatted'] = collect($client['documents'] ?? [])
            ->map(fn($document) => $this->formatClientDocument($document))
            ->values()
            ->toArray();

        $client['subclients_formatted'] = collect($client['subclients'] ?? [])
            ->map(fn($subclient) => $this->formatSubclient($subclient))
            ->values()
            ->toArray();

        $client['invoices_formatted'] = collect($client['invoices'] ?? [])
            ->map(fn($invoice) => $this->formatInvoice($invoice, $locationId))
            ->sortByDesc('invoice_date_raw')
            ->values()
            ->toArray();

        return $client;
    }

    private function formatClientDocument(array $document): array
    {
        $fileUrl = $document['file_url'] ?? null;

        return [
            'id' => $document['id'] ?? null,
            'file_name' => $document['file_name'] ?? __('Document'),
            'file_url' => $fileUrl,
            'view_url' => !empty($fileUrl) ? apiBaseUrl() . $fileUrl : null,
            'status' => $document['status'] ?? null,
        ];
    }

    private function formatSubclient(array $subclient): array
    {
        return [
            'id' => $subclient['id'] ?? null,
            'username' => $subclient['username'] ?? '-',
            'gender' => $subclient['gender'] ?? null,
            'gender_text' => $this->genderText($subclient['gender'] ?? null),
            'phone_number' => $subclient['phone_number'] ?? '-',
            'email' => $subclient['email'] ?? '-',
            'national_id' => $subclient['national_id'] ?? '-',
            'passport' => $subclient['passport'] ?? '-',
            'address' => $subclient['address'] ?? '-',
            'description' => $subclient['description'] ?? '-',
            'date_of_birth' => $subclient['date_of_birth'] ?? null,
            'dob_text' => $this->dateText($subclient['date_of_birth'] ?? null),
            'sub_client_image' => $subclient['sub_client_image'] ?? null,
            'sub_client_image_url' => $this->apiImageUrl(
                $subclient['sub_client_image'] ?? null,
                'images/default-avatar.png'
            ),
        ];
    }

    private function formatInvoice(array $invoice, string $locationId): array
    {
        $oldElectric = (float) ($invoice['old_electric'] ?? 0);
        $newElectric = (float) ($invoice['new_electric'] ?? 0);
        $electricRate = (float) ($invoice['electric_rate'] ?? 0);

        $oldWater = (float) ($invoice['old_water'] ?? 0);
        $newWater = (float) ($invoice['new_water'] ?? 0);
        $waterRate = (float) ($invoice['water_rate'] ?? 0);

        $electricTotal = max(0, $newElectric - $oldElectric) * $electricRate;
        $waterTotal = max(0, $newWater - $oldWater) * $waterRate;

        $invoice['invoice_date_raw'] = $invoice['invoice_date'] ?? null;
        $invoice['invoice_date_text'] = $this->dateText($invoice['invoice_date'] ?? null);
        $invoice['due_date_text'] = $this->dateText($invoice['due_date'] ?? null);

        $invoice['room_fee_text'] = $this->moneyText($invoice['room_fee'] ?? 0);
        $invoice['electric_total'] = $electricTotal;
        $invoice['electric_total_text'] = $this->moneyText($electricTotal);
        $invoice['water_total'] = $waterTotal;
        $invoice['water_total_text'] = $this->moneyText($waterTotal);
        $invoice['other_charge_text'] = $this->moneyText($invoice['other_charge'] ?? 0);
        $invoice['total_text'] = $this->moneyText($invoice['total'] ?? 0);

        $invoice['status_meta'] = InvoiceStatus::getStatus($invoice['status'] ?? null);

        $invoice['show_url'] = !empty($invoice['id'])
            ? route('invoice.show', [
                'id' => $invoice['id'],
                'locationId' => $locationId,
            ])
            : null;

        return $invoice;
    }

    private function dateText(mixed $date): string
    {
        if (blank($date)) {
            return __('N/A');
        }

        try {
            return Carbon::parse($date)->translatedFormat('d F Y');
        } catch (Exception) {
            return (string) $date;
        }
    }

    private function daysLeft(mixed $endDate): ?int
    {
        if (blank($endDate)) {
            return null;
        }

        try {
            return now()->startOfDay()->diffInDays(
                Carbon::parse($endDate)->startOfDay(),
                false
            );
        } catch (Exception) {
            return null;
        }
    }

    private function moneyText(mixed $amount): string
    {
        return number_format((float) ($amount ?? 0), 2) . '(៛)';
    }

    private function genderText(mixed $gender): string
    {
        return match ((string) $gender) {
            'm', 'M', 'male', 'ប្រុស' => __('client.male'),
            'f', 'F', 'female', 'ស្រី' => __('client.female'),
            default => __('N/A'),
        };
    }

    private function apiImageUrl(?string $path, string $fallback): string
    {
        return !empty($path)
            ? apiBaseUrl() . $path
            : asset($fallback);
    }

    public function destroy($id, $locationId)
    {
        try {
            // ✅ Call API DELETE endpoint
            $apiResponse = $this->api()->withHeaders(['Location-Id' => $locationId])->delete("v1/rooms/{$id}");

            // ✅ Handle success
            if (($apiResponse['status'] ?? '') === 'success') {
                return redirect()->route('room.index')->with('success', __('room.deleted_successfully'));
            }

            // ❌ Handle failure
            return back()->withErrors([
                'error' => $apiResponse['errors'] ?? $apiResponse['message'] ?? __('room.delete_failed'),
            ]);
        } catch (Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage() ?: __('room.delete_failed'),
            ]);
        }
    }

    public function multiDestroy(Request $request, $locationId)
    {
        $roomIds = explode(',', $request->room_ids);

        try {
            foreach ($roomIds as $id) {
                $this->api()->withHeaders(['Location-Id' => $locationId])->delete("v1/rooms/{$id}");
            }

            return back()->with('success', __('room.deleted_successfully'));
        } catch (Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage() ?: __('room.delete_failed'),
            ]);
        }
    }

    // for booking
    public function booking(Request $request, $roomId, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('room.room_list', $locationId),
            ],
        ];

        $roomResponse = $this->api()->withHeaders(['Location-Id' => $locationId])->get("v1/rooms/{$roomId}");
        $room = $roomResponse['room'];

        $roomstatus = RoomStatus::getStatus($room['status']);

        return view('app.rooms.booking', compact('room', 'buttons', 'roomstatus', 'locationId'));
    }

    public function updateStatus(Request $request, $roomId, $locationId)
    {
        // ✅ Validate the incoming status value
        $validated = $request->validate([
            'status' => 'required|in:0,1,2,3',
        ], [
            'status.required' => __('room.status_required'),
            'status.in' => __('room.invalid_status'),
        ]);

        // ✅ Prepare payload for API
        $payload = [
            '_method' => 'PATCH',
            'updated_by' => session('user.id') ?? null,
            'status' => $validated['status'],
        ];

        try {
            // ✅ Send PATCH request to your API
            $apiResponse = $this->api()
                ->post(
                    "v1/rooms/{$roomId}/status",
                    $payload,
                    token: null,
                    asForm: false,
                    files: [],
                    fileField: 'documents[]',
                    moreHeaders: ['Location-Id' => $locationId]
                );

            // ✅ Check for success flag
            if (($apiResponse['status'] ?? '') === 'success' || ($apiResponse['success'] ?? false)) {
                return redirect()
                    ->back()
                    ->with('success', __('room.updated_successfully'));
            }

            // ❌ API returned an error response
            return back()
                ->withInput()
                ->withErrors([
                    'error' => $apiResponse['message'] ?? __('room.update_failed'),
                ]);
        } catch (\Throwable $e) {
            // ❌ Catch network, connection, or unexpected exceptions
            report($e); // optional: log it for debugging

            return back()
                ->withInput()
                ->withErrors(['error' => __('room.update_failed')]);
        }
    }
}
