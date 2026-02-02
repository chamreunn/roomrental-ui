<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Enum\Active;
use App\Enum\RoomStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientController extends Controller
{
    public function showLocation()
    {
        // Get location detail
        $locationResponse = $this->api()->get("v1/locations");
        $locations = $locationResponse['locations']['data'] ?? null;

        return view('app.clients.show-location', compact('locations'));
    }

    public function chooseLocation()
    {
        $locations = collect(Session::get('user.user_locations', []))
            ->pluck('location')
            ->values()
            ->toArray();

        return view('app.clients.choose-location', compact('locations'));
    }

    public function clients(Request $request, $locationId)
    {
        $perPage     = (int) $request->query('per_page', 10);
        $currentPage = (int) $request->query('page', 1);

        $search      = trim((string) $request->query('search', ''));
        $roomStatus  = $request->query('room_status'); // from filter dropdown

        // ✅ validate roomStatus using enum keys
        $allowedRoomStatuses = array_keys(RoomStatus::all());
        $roomStatus = ($roomStatus !== null && $roomStatus !== '' && in_array((int)$roomStatus, $allowedRoomStatuses, true))
            ? (int)$roomStatus
            : null;

        try {
            $response = $this->api()
                ->withHeaders(['Location-Id' => $locationId])
                ->get('v1/clients');

            $data = $response['clients'] ?? [];
            $clientsArray = $data['data'] ?? [];

            // ✅ Transform + safe room + badges
            $collection = collect($clientsArray)->map(function ($item) {

                // client status badge
                $item['status_badge'] = Active::getStatus($item['status'] ?? null);

                // ensure room exists
                $item['room'] = $item['room'] ?? [
                    'id' => null,
                    'location_id' => null,
                    'building_name' => '-',
                    'room_name' => '-',
                    'status' => null,
                ];

                // room status meta (enum)
                $item['room']['status_meta'] = RoomStatus::getStatus($item['room']['status'] ?? null);

                return $item;
            });

            // ✅ Filter by room status
            if ($roomStatus !== null) {
                $collection = $collection->filter(fn($c) => (int)($c['room']['status'] ?? -1) === $roomStatus);
            }

            // ✅ Search: username / email / phone / room name
            if ($search !== '') {
                $q = mb_strtolower($search);

                $collection = $collection->filter(function ($c) use ($q) {
                    $username = mb_strtolower((string)($c['username'] ?? ''));
                    $email    = mb_strtolower((string)($c['email'] ?? ''));
                    $phone    = mb_strtolower((string)($c['phone_number'] ?? ''));
                    $roomName = mb_strtolower((string)($c['room']['room_name'] ?? ''));

                    return str_contains($username, $q)
                        || str_contains($email, $q)
                        || str_contains($phone, $q)
                        || str_contains($roomName, $q);
                });
            }

            // ✅ Pagination after filtering
            $total = $collection->count();

            $items = $collection->values()
                ->slice(($currentPage - 1) * $perPage, $perPage)
                ->values();

            $clients = new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                [
                    'path'  => url()->current(),
                    'query' => $request->query(),
                ]
            );

            // dd($clients);
        } catch (\Throwable $e) {
            $clients = new LengthAwarePaginator([], 0, $perPage, $currentPage, [
                'path'  => url()->current(),
                'query' => $request->query(),
            ]);
        }

        // ✅ send enum list to blade for dropdown
        $roomStatuses = Active::all();

        return view('app.clients.index', compact('clients', 'roomStatuses', 'locationId'));
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $currentPage = $request->query('page', 1);

            $response = $this->api()->get('v1/clients');
            $data = $response['clients'] ?? [];

            // Clients list
            $clientsArray = $data['data'] ?? [];
            $total = $data['total'] ?? count($clientsArray);

            // Transform & make room safe
            $dataCollection = collect($clientsArray)->transform(function ($item) {

                $item['status_badge'] = Active::getStatus($item['status']);

                // Ensure room always exists (avoid null errors)
                $item['room'] = $item['room'] ?? [
                    'id' => null,
                    'location_id' => null,
                    'building_name' => '-',
                    'room_name' => '-',
                ];

                return $item;
            });

            // Pagination
            $clients = new LengthAwarePaginator(
                $dataCollection,
                $total,
                $perPage,
                $currentPage,
                ['path' => url()->current(), 'query' => $request->query()]
            );

        } catch (Exception $e) {
            $clients = new LengthAwarePaginator([], 0, 10);
        }

        return view('app.clients.index', compact('clients'));
    }

    public function edit(Request $request, $clientId, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('clients.index'),
            ],
        ];

        // Fetch client from API
        $response = $this->api()->withHeaders(['Location-Id' => $locationId])->get('v1/clients/' . $clientId);

        $client = $response['client'] ?? null;
        // dd($client);

        if (!$client) {
            return redirect()->route('clients.index')
                ->withErrors(['error' => __('client.no_clients_found')]);
        }

        // Map Khmer gender to 'm'/'f'
        $genderMap = [
            'ប្រុស' => 'm',
            'ស្រី' => 'f',
        ];

        $client['gender_mapped'] = $genderMap[$client['gender']] ?? null;

        return view('app.clients.edit', compact('client', 'buttons', 'locationId'));
    }

    public function store(Request $request, $roomId, $locationId)
    {
        // 1️⃣ Validate input
        $validated = $request->validate([
            'username'          => 'required|string|max:100',
            'gender'            => 'required|in:m,f',
            'phone_number'      => 'required|string|max:20',
            'email'             => 'nullable|email|max:100',
            'dob'               => 'required|date_format:d-m-Y',
            'national_id'       => 'nullable|string|max:30',
            'passport'          => 'nullable|string|max:30',
            'address'           => 'required|string|max:255',
            'image'             => 'nullable|image|max:2048',
            'start_rental_date' => 'required|date',
            'end_rental_date'   => 'nullable|date|after_or_equal:start_rental_date',
            'description'       => 'nullable|string|max:255',
        ]);

        try {
            // 2️⃣ Normalize dates
            $dob = Carbon::createFromFormat('d-m-Y', $validated['dob'])->format('Y-m-d');
            $startDate = Carbon::parse($validated['start_rental_date'])->format('Y-m-d');
            $endDate = !empty($validated['end_rental_date'])
                ? Carbon::parse($validated['end_rental_date'])->format('Y-m-d')
                : null;

            // 3️⃣ Prepare payload
            $payload = [
                'created_by'        => Session::get('user.id'),
                'updated_by'        => Session::get('user.id'),
                'room_id'           => $roomId,
                'username'          => $validated['username'],
                'date_of_birth'     => $dob,
                'gender'            => $validated['gender'],
                'phone_number'      => $validated['phone_number'],
                'email'             => $validated['email'] ?? null,
                'national_id'       => $validated['national_id'] ?? null,
                'passport'          => $validated['passport'] ?? null,
                'address'           => $validated['address'],
                'start_rental_date' => $startDate,
                'end_rental_date'   => $endDate,
                'description'       => $validated['description'] ?? null,
            ];

            // 4️⃣ Handle file
            $files = $request->hasFile('image') ? [$request->file('image')] : [];

            // 5️⃣ API call
            try {
                $response = $this->api()->post(
                    'v1/clients',
                    $payload,
                    token: null,
                    asForm: true,
                    files: $files,
                    fileField: 'client_image',
                    moreHeaders: ['Location-Id' => $locationId]
                );
            } catch (\Illuminate\Http\Client\RequestException $e) {
                // Catch 422 errors from API
                $body = $e->response->body();
                $json = json_decode($body, true);

                $errorMessage = 'Something went wrong';
                if (!empty($json['errors'])) {
                    foreach ($json['errors'] as $fieldErrors) {
                        if (is_array($fieldErrors) && count($fieldErrors) > 0) {
                            $errorMessage = $fieldErrors[0];
                            break;
                        }
                    }
                } elseif (!empty($json['message'])) {
                    $errorMessage = $json['message'];
                }

                return back()->withErrors(['api' => $errorMessage])->withInput();
            }

            // 6️⃣ Handle API success
            if (!empty($response['status']) && $response['status'] === 'success') {
                return redirect()->route('clients.clients', $locationId)
                    ->with('success', $response['message'] ?? __('messages.rental_success'));
            }

            // 7️⃣ Handle API returned failure
            $errorMessage = $response['message'] ?? __('messages.rental_failed');
            return back()->withErrors(['api' => $errorMessage])->withInput();
        } catch (\Throwable $e) {
            // 8️⃣ Catch unexpected errors
            Log::error('Client store failed', [
                'error'       => $e->getMessage(),
                'room_id'     => $roomId,
                'location_id' => $locationId,
            ]);

            return back()->withErrors(['api' => $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, $clientId, $roomId, $locationId)
    {
        $validated = $request->validate([
            'username'          => 'required|string|max:100',
            'gender'            => 'required|string|in:m,f',
            'phone_number'      => 'required|string|max:20',
            'email'             => 'nullable|email|max:100',
            'date_of_birth'     => 'required|date',
            'national_id'       => 'nullable|string|max:30',
            'passport'          => 'nullable|string|max:30',
            'address'           => 'required|string|max:255',
            'image'             => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240', // nullable string
            'start_rental_date' => 'nullable|date',
            'end_rental_date'   => 'nullable|date',
            'description'       => 'nullable|string|max:255',
        ], [
            'username.required' => __('client.username_required'),
            'gender.required'   => __('client.gender_required'),
            'gender.in'         => __('client.gender_invalid'),
            'phone_number.required' => __('client.phone_required'),
            'date_of_birth.required' => __('client.date_of_birth_required'),
            'date_of_birth.date_format' => __('client.dob_format'),
            'address.required'  => __('client.address_required'),
        ]);

        try {
            // Prepare payload
            $payload = [
                '_method'           => 'PATCH',
                'created_by'        => Session::get('user.id'),
                'updated_by'        => Session::get('user.id'),
                // 'room_id'           => $roomId,
                'username'          => $validated['username'],
                'date_of_birth'     => $validated['date_of_birth'],
                'gender'            => $validated['gender'],
                'phone_number'      => $validated['phone_number'],
                'email'             => $validated['email'] ?? null,
                'national_id'       => $validated['national_id'] ?? null,
                'passport'          => $validated['passport'] ?? null,
                'address'           => $validated['address'],
                'start_rental_date' => $validated['start_rental_date'],
                'end_rental_date'   => $validated['end_rental_date'] ?? null,
                'description'       => $validated['description'] ?? null,
            ];

            // Only include client_image if provided (handle file upload)
            $files = [];
            $isMultipart = false;
            if ($request->hasFile('image')) {
                $isMultipart = true;
                $files = [$request->file('image')];
            }

            // Send payload to API (support multipart if file provided)
            $response = $this->api()->post(
                'v1/clients/' . $clientId,
                $payload,
                null,
                $isMultipart,
                $files,
                'client_image',
                moreHeaders: ['Location-Id' => $locationId]
            );

            if (($response['status'] ?? '') === 'success') {
                return redirect()
                    ->back()
                    ->with('success', __('client.update_success'));
            }

            return back()->withErrors(['api' => __('client.update_failed')])->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors(['api' => $e->getMessage()])->withInput();
        }
    }

    public function updateClientStatus($clientId, $status, $locationId)
    {
        try {
            // Send PATCH request to update client status
            $response = $this->api()->withHeaders(['Location-Id' => $locationId])->patch("v1/clients/{$clientId}/status", [
                '_method' => 'PATCH',
                'updated_by' => session('user.id'),
                'status' => $status,
            ]);

            // Optional: check if API returns success
            if (isset($response['status']) && $response['status'] === "success") {
                return back()->with('success', __('client.status_updated_successfully'));
            }

            return back()->withErrors(['api' => __('client.status_update_failed')])->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors(['api' => $e->getMessage()])->withInput();
        }
    }

    public function destroy($clientId, $locationId)
    {
        try {
            $response = $this->api()
                ->withHeaders(['Location-Id' => $locationId])
                ->delete("v1/clients/{$clientId}");

            if (($response['status'] ?? null) === 'success') {
                return back()->with('success', __('client.delete_success'));
            }

            return back()->withErrors(['api' => $response['message'] ?? __('client.delete_failed')]);
        } catch (\Throwable $e) {

            // Default fallback
            $msg = __('client.delete_failed');

            // Try to extract JSON from exception message
            $raw = $e->getMessage();

            // Find first "{" and parse JSON after it
            $pos = strpos($raw, '{');
            if ($pos !== false) {
                $jsonPart = substr($raw, $pos);
                $data = json_decode($jsonPart, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $msg = $data['message'] ?? $msg;
                }
            }

            return back()->withErrors(['api' => $msg]);
        }
    }
}
