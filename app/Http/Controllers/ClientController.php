<?php

namespace App\Http\Controllers;

use App\Enum\Active;
use App\Enum\RoomStatus;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

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
        $perPage = (int) $request->query('per_page', 10);
        $currentPage = (int) $request->query('page', 1);

        $search = trim((string) $request->query('search', ''));
        $roomStatus = $request->query('room_status'); // from filter dropdown

        // ✅ validate roomStatus using enum keys
        $allowedRoomStatuses = array_keys(RoomStatus::all());
        $roomStatus = ($roomStatus !== null && $roomStatus !== '' && in_array((int) $roomStatus, $allowedRoomStatuses, true))
            ? (int) $roomStatus
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
                $collection = $collection->filter(fn($c) => (int) ($c['room']['status'] ?? -1) === $roomStatus);
            }

            // ✅ Search: username / email / phone / room name
            if ($search !== '') {
                $q = mb_strtolower($search);

                $collection = $collection->filter(function ($c) use ($q) {
                    $username = mb_strtolower((string) ($c['username'] ?? ''));
                    $email = mb_strtolower((string) ($c['email'] ?? ''));
                    $phone = mb_strtolower((string) ($c['phone_number'] ?? ''));
                    $roomName = mb_strtolower((string) ($c['room']['room_name'] ?? ''));

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
                    'path' => url()->current(),
                    'query' => $request->query(),
                ]
            );

            // dd($clients);
        } catch (Throwable $e) {
            $clients = new LengthAwarePaginator([], 0, $perPage, $currentPage, [
                'path' => url()->current(),
                'query' => $request->query(),
            ]);
        }

        // ✅ send enum list to blade for dropdown
        $roomStatuses = RoomStatus::all();

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

        try {
            // Fetch client from API
            $response = $this->api()
                ->withHeaders(['Location-Id' => $locationId])
                ->get("v1/clients/{$clientId}");

            $clientRaw = $response['client'] ?? null;

            if (!$clientRaw) {
                return redirect()
                    ->route('clients.index')
                    ->withErrors(['error' => __('client.no_clients_found')]);
            }

            // Map Khmer gender to 'm'/'f' for the form select
            $genderMap = [
                'ប្រុស' => 'm',
                'ស្រី' => 'f',
            ];

            // Build a clean "form-ready" client array
            $client = [
                'id' => $clientRaw['id'] ?? null,
                'room_id' => $clientRaw['room_id'] ?? null,

                'username' => $clientRaw['username'] ?? '',
                'date_of_birth' => $clientRaw['date_of_birth'] ?? null,
                'gender_mapped' => $genderMap[$clientRaw['gender'] ?? ''] ?? null,

                'phone_number' => $clientRaw['phone_number'] ?? '',
                'email' => $clientRaw['email'] ?? null,

                'address' => $clientRaw['address'] ?? '',
                'description' => $clientRaw['description'] ?? null,

                'start_rental_date' => $clientRaw['start_rental_date'] ?? null,
                'end_rental_date' => $clientRaw['end_rental_date'] ?? null,

                'client_image' => $clientRaw['client_image'] ?? null,
                'status' => $clientRaw['status'] ?? null,

                // keep full room object for readonly display
                'room' => $clientRaw['room'] ?? [],

                // keep documents if you want to show/download them
                'documents' => $clientRaw['documents'] ?? [],
            ];

            return view('app.clients.edit', compact('client', 'buttons', 'locationId'));

        } catch (Throwable $e) {
            Log::error('Failed to fetch client for edit', [
                'client_id' => $clientId,
                'location_id' => $locationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('clients.index')
                ->withErrors(['error' => __('client.no_clients_found')]);
        }
    }

    public function store(Request $request, $roomId, $locationId)
    {
        // 1️⃣ Validate input
        $validated = $request->validate([
            'username' => 'required|string|max:100',
            'gender' => 'required|in:m,f',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'dob' => 'required|date_format:d-m-Y',
            'national_id' => 'nullable|string|max:30',
            'passport' => 'nullable|string|max:30',
            'address' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:10240',
            'start_rental_date' => 'required|date',
            'end_rental_date' => 'nullable|date|after_or_equal:start_rental_date',
            'description' => 'nullable|string|max:255',
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
                'created_by' => Session::get('user.id'),
                'updated_by' => Session::get('user.id'),
                'room_id' => $roomId,
                'username' => $validated['username'],
                'date_of_birth' => $dob,
                'gender' => $validated['gender'],
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'] ?? null,
                'national_id' => $validated['national_id'] ?? null,
                'passport' => $validated['passport'] ?? null,
                'address' => $validated['address'],
                'start_rental_date' => $startDate,
                'end_rental_date' => $endDate,
                'description' => $validated['description'] ?? null,
            ];

            // 4️⃣ Handle files — image (client_image) + document (file)
            $files = [];
            $fileFields = [];

            if ($request->hasFile('image')) {
                $files[] = $request->file('image');
                $fileFields[] = 'client_image';
            }

            if ($request->hasFile('file')) {
                $files[] = $request->file('file');
                $fileFields[] = 'file';
            }

            // 5️⃣ API call
            try {
                $response = $this->api()->post(
                    'v1/clients',
                    $payload,
                    token: null,
                    asForm: true,
                    files: $files,
                    fileField: $fileFields,  // ✅ use the array, not hardcoded 'client_image'
                    moreHeaders: ['Location-Id' => $locationId]
                );
            } catch (RequestException $e) {
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

            // 7️⃣ Handle API failure
            return back()->withErrors(['api' => $response['message'] ?? __('messages.rental_failed')])->withInput();

        } catch (Throwable $e) {
            Log::error('Client store failed', [
                'error' => $e->getMessage(),
                'room_id' => $roomId,
                'location_id' => $locationId,
            ]);

            return back()->withErrors(['api' => $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, $clientId, $roomId, $locationId)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:100',
            'gender' => 'required|string|in:m,f',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'date_of_birth' => 'required|date',
            'national_id' => 'nullable|string|max:30',
            'passport' => 'nullable|string|max:30',
            'address' => 'required|string|max:255',

            // avatar (ONLY for client_image)
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',

            // rental
            'start_rental_date' => 'nullable|date',
            'end_rental_date' => 'nullable|date',
            'description' => 'nullable|string|max:255',

            // documents (ONLY for document table)
            'documents' => 'sometimes|array',
            'documents.*' => 'file|mimes:pdf,png,jpg,jpeg,doc,docx|max:10240',

            // document ids to delete (checkbox)
            'delete_documents' => 'sometimes|array',
            'delete_documents.*' => 'string',
        ], [
            'username.required' => __('client.username_required'),
            'gender.required' => __('client.gender_required'),
            'gender.in' => __('client.gender_invalid'),
            'phone_number.required' => __('client.phone_required'),
            'date_of_birth.required' => __('client.date_of_birth_required'),
            'address.required' => __('client.address_required'),
        ]);

        $deleteIds = $request->input('delete_documents', []); // ✅ doc ids

        try {
            // 1) Update client + upload avatar/docs
            $payload = [
                '_method' => 'PATCH',
                'created_by' => Session::get('user.id'),
                'updated_by' => Session::get('user.id'),

                'username' => $validated['username'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'] ?? null,
                'national_id' => $validated['national_id'] ?? null,
                'passport' => $validated['passport'] ?? null,
                'address' => $validated['address'],
                'start_rental_date' => $validated['start_rental_date'] ?? null,
                'end_rental_date' => $validated['end_rental_date'] ?? null,
                'description' => $validated['description'] ?? null,
            ];

            $hasAvatar = $request->hasFile('image');
            $hasDocs = $request->hasFile('documents');

            if ($hasAvatar || $hasDocs) {
                $files = [];

                if ($hasAvatar) {
                    $files['client_image'] = [$request->file('image')]; // ✅ avatar only
                }

                if ($hasDocs) {
                    $files['file'] = $request->file('documents'); // ✅ documents only
                }

                $response = $this->api()->postMultipart(
                    'v1/clients/' . $clientId,
                    $payload,
                    $files,
                    moreHeaders: ['Location-Id' => $locationId]
                );
            } else {
                $response = $this->api()->post(
                    'v1/clients/' . $clientId,
                    $payload,
                    null,
                    false,
                    [],
                    '',
                    moreHeaders: ['Location-Id' => $locationId]
                );
            }

            // If client update failed, stop here
            if (($response['status'] ?? '') !== 'success') {
                return back()
                    ->withErrors(['api' => $response['message'] ?? __('client.update_failed')])
                    ->withInput();
            }

            // 2) Delete selected documents via separate API route
            $deleteErrors = [];

            foreach ($deleteIds as $docId) {
                if (!$docId)
                    continue;

                try {
                    // if your ApiService has delete()
                    $delRes = $this->api()->withHeaders(['Location-Id' => $locationId])
                        ->delete('v1/documents/' . $docId);

                    // Optional: check result format
                    if (($delRes['status'] ?? 'success') !== 'success' && ($delRes['success'] ?? true) !== true) {
                        $deleteErrors[] = $delRes['message'] ?? "Failed to delete document {$docId}";
                    }
                } catch (Throwable $e) {
                    $deleteErrors[] = "Failed to delete document {$docId}: " . $e->getMessage();
                }
            }

            // 3) Return
            if (!empty($deleteErrors)) {
                return redirect()->back()
                    ->with('success', __('client.update_success'))
                    ->withErrors(['documents' => $deleteErrors]);
            }

            return redirect()->back()->with('success', __('client.update_success'));

        } catch (Throwable $e) {
            Log::error('Client update failed', [
                'client_id' => $clientId,
                'room_id' => $roomId,
                'location_id' => $locationId,
                'error' => $e->getMessage(),
            ]);

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
        } catch (Throwable $e) {

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
