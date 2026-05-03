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
        $locationResponse = $this->api()->get('v1/locations');
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
        $roomStatus = $request->query('room_status');

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

            $collection = collect($clientsArray)->map(function ($item) {
                $item['status_badge'] = Active::getStatus($item['status'] ?? null);

                $item['room'] = $item['room'] ?? [
                    'id' => null,
                    'location_id' => null,
                    'building_name' => '-',
                    'room_name' => '-',
                    'status' => null,
                ];

                $item['room']['status_meta'] = RoomStatus::getStatus($item['room']['status'] ?? null);

                return $item;
            });

            if ($roomStatus !== null) {
                $collection = $collection->filter(fn($client) => (int) ($client['room']['status'] ?? -1) === $roomStatus);
            }

            if ($search !== '') {
                $query = mb_strtolower($search);

                $collection = $collection->filter(function ($client) use ($query) {
                    $username = mb_strtolower((string) ($client['username'] ?? ''));
                    $email = mb_strtolower((string) ($client['email'] ?? ''));
                    $phone = mb_strtolower((string) ($client['phone_number'] ?? ''));
                    $roomName = mb_strtolower((string) ($client['room']['room_name'] ?? ''));

                    return str_contains($username, $query)
                        || str_contains($email, $query)
                        || str_contains($phone, $query)
                        || str_contains($roomName, $query);
                });
            }

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
        } catch (Throwable $e) {
            Log::error('Client list failed', [
                'location_id' => $locationId,
                'error' => $e->getMessage(),
            ]);

            $clients = new LengthAwarePaginator([], 0, $perPage, $currentPage, [
                'path' => url()->current(),
                'query' => $request->query(),
            ]);
        }

        $roomStatuses = RoomStatus::all();

        return view('app.clients.index', compact('clients', 'roomStatuses', 'locationId'));
    }

    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->query('per_page', 10);
            $currentPage = (int) $request->query('page', 1);

            $response = $this->api()->get('v1/clients');
            $data = $response['clients'] ?? [];

            $clientsArray = $data['data'] ?? [];
            $total = $data['total'] ?? count($clientsArray);

            $dataCollection = collect($clientsArray)->transform(function ($item) {
                $item['status_badge'] = Active::getStatus($item['status'] ?? null);

                $item['room'] = $item['room'] ?? [
                    'id' => null,
                    'location_id' => null,
                    'building_name' => '-',
                    'room_name' => '-',
                ];

                return $item;
            });

            $clients = new LengthAwarePaginator(
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
            Log::error('Client index failed', [
                'error' => $e->getMessage(),
            ]);

            $clients = new LengthAwarePaginator([], 0, 10);
        }

        return view('app.clients.index', compact('clients'));
    }

    public function store(Request $request, $roomId, $locationId)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:m,f'],
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'dob' => ['required', 'date_format:d-m-Y'],
            'national_id' => ['nullable', 'string', 'max:30'],
            'passport' => ['nullable', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:10240'],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'start_rental_date' => ['required', 'date'],
            'end_rental_date' => ['nullable', 'date', 'after_or_equal:start_rental_date'],
            'description' => ['nullable', 'string', 'max:255'],

            'subclients' => ['nullable', 'array'],
            'subclients.*.username' => ['required_with:subclients', 'nullable', 'string', 'max:100'],
            'subclients.*.date_of_birth' => ['required_with:subclients', 'nullable', 'date', 'before_or_equal:today'],
            'subclients.*.gender' => ['required_with:subclients', 'nullable', 'in:m,f'],
            'subclients.*.phone_number' => ['nullable', 'string', 'max:20'],
            'subclients.*.email' => ['nullable', 'email', 'max:100'],
            'subclients.*.national_id' => ['nullable', 'string', 'max:30'],
            'subclients.*.passport' => ['nullable', 'string', 'max:30'],
            'subclients.*.address' => ['required_with:subclients', 'nullable', 'string', 'max:255'],
            'subclients.*.description' => ['nullable', 'string', 'max:255'],
            'subclients.*.sub_client_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:10240'],
        ]);

        try {
            $payload = [
                'room_id' => $roomId,
                'username' => $validated['username'],
                'date_of_birth' => Carbon::createFromFormat('d-m-Y', $validated['dob'])->format('Y-m-d'),
                'gender' => $validated['gender'],
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'] ?? '',
                'national_id' => $validated['national_id'] ?? '',
                'passport' => $validated['passport'] ?? '',
                'address' => $validated['address'],
                'start_rental_date' => Carbon::parse($validated['start_rental_date'])->format('Y-m-d'),
                'end_rental_date' => !empty($validated['end_rental_date'])
                    ? Carbon::parse($validated['end_rental_date'])->format('Y-m-d')
                    : '',
                'description' => $validated['description'] ?? '',
                'subclients' => $this->normalizeSubclients($validated['subclients'] ?? []),
            ];

            $response = $this->sendClientRequest(
                endpoint: 'v1/clients',
                payload: $payload,
                request: $request,
                locationId: $locationId
            );

            if (($response['status'] ?? '') === 'success') {
                return redirect()
                    ->route('clients.clients', $locationId)
                    ->with('success', $response['message'] ?? __('messages.rental_success'));
            }

            return back()
                ->withErrors([
                    'api' => $this->apiErrorMessage($response, __('messages.rental_failed')),
                ])
                ->withInput();
        } catch (RequestException $e) {
            $response = $e->response?->json() ?? [];

            return back()
                ->withErrors([
                    'api' => $this->apiErrorMessage($response, $e->getMessage()),
                ])
                ->withInput();
        } catch (Throwable $e) {
            Log::error('Client store failed', [
                'error' => $e->getMessage(),
                'room_id' => $roomId,
                'location_id' => $locationId,
            ]);

            return back()
                ->withErrors([
                    'api' => $this->stringError($e->getMessage()),
                ])
                ->withInput();
        }
    }

    public function edit(Request $request, $clientId, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('clients.clients', $locationId),
            ],
        ];

        try {
            $response = $this->api()
                ->withHeaders(['Location-Id' => $locationId])
                ->get("v1/clients/{$clientId}");

            $clientRaw = $response['client'] ?? null;

            if (!$clientRaw) {
                return redirect()
                    ->route('clients.clients', $locationId)
                    ->withErrors([
                        'api' => __('client.no_clients_found'),
                    ]);
            }

            $genderMap = [
                'm' => 'm',
                'f' => 'f',
                'M' => 'm',
                'F' => 'f',
                'male' => 'm',
                'female' => 'f',
                'ប្រុស' => 'm',
                'ស្រី' => 'f',
            ];

            $room = is_array($clientRaw['room'] ?? null) ? $clientRaw['room'] : [];

            $roomType = is_array($room['room_type'] ?? null)
                ? $room['room_type']
                : (is_array($room['roomType'] ?? null) ? $room['roomType'] : []);

            $location = is_array($room['location'] ?? null) ? $room['location'] : [];

            $clientGender = $this->stringValue($clientRaw['gender'] ?? null, '');
            $clientImagePath = $this->stringValue($clientRaw['client_image'] ?? null, '');

            $client = [
                'id' => $this->stringValue($clientRaw['id'] ?? null, ''),
                'room_id' => $this->stringValue($clientRaw['room_id'] ?? ($room['id'] ?? null), ''),
                'username' => $this->stringValue($clientRaw['username'] ?? null, ''),
                'date_of_birth' => $this->dateValue($clientRaw['date_of_birth'] ?? null),
                'gender' => $clientGender,
                'gender_mapped' => $genderMap[$clientGender] ?? null,
                'phone_number' => $this->stringValue($clientRaw['phone_number'] ?? null, ''),
                'email' => $this->stringValue($clientRaw['email'] ?? null, ''),
                'national_id' => $this->stringValue($clientRaw['national_id'] ?? null, ''),
                'passport' => $this->stringValue($clientRaw['passport'] ?? null, ''),
                'address' => $this->stringValue($clientRaw['address'] ?? null, ''),
                'description' => $this->stringValue($clientRaw['description'] ?? null, ''),
                'start_rental_date' => $this->dateValue($clientRaw['start_rental_date'] ?? null),
                'end_rental_date' => $this->dateValue($clientRaw['end_rental_date'] ?? null),
                'client_image' => $clientImagePath,
                'client_image_url' => $clientImagePath !== ''
                    ? apiBaseUrl() . $clientImagePath
                    : asset('imgs/default-avatar.png'),
                'status' => $clientRaw['status'] ?? null,

                'room' => [
                    'id' => $this->stringValue($room['id'] ?? null, ''),
                    'room_name' => $this->stringValue($room['room_name'] ?? null),
                    'building_name' => $this->stringValue($room['building_name'] ?? null),
                    'floor_name' => $this->stringValue($room['floor_name'] ?? null),
                    'type_name' => $this->stringValue($roomType['type_name'] ?? null),
                    'price' => $this->numericValue($roomType['price'] ?? null),
                    'price_text' => $this->moneyValue($roomType['price'] ?? null),
                    'location_name' => $this->stringValue($location['location_name'] ?? null),
                ],

                'documents' => $this->documentList($clientRaw['documents'] ?? []),
                'subclients' => $this->subclientList($clientRaw['subclients'] ?? []),
            ];

            return view('app.clients.edit', compact('client', 'buttons', 'locationId'));
        } catch (Throwable $e) {
            Log::error('Failed to fetch client for edit', [
                'client_id' => $clientId,
                'location_id' => $locationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('clients.clients', $locationId)
                ->withErrors([
                    'api' => $this->stringError(__('client.no_clients_found')),
                ]);
        }
    }

    public function update(Request $request, $clientId, $roomId, $locationId)
    {
        $validated = $request->validate([
            'room_id' => ['required', 'string'],
            'username' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'string', 'in:m,f'],
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'date_of_birth' => ['required', 'date'],
            'national_id' => ['nullable', 'string', 'max:30'],
            'passport' => ['nullable', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:10240'],
            'start_rental_date' => ['required', 'date'],
            'end_rental_date' => ['nullable', 'date', 'after_or_equal:start_rental_date'],
            'description' => ['nullable', 'string', 'max:255'],
            'documents' => ['sometimes', 'array'],
            'documents.*' => ['file', 'mimes:pdf,png,jpg,jpeg,doc,docx', 'max:10240'],
            'delete_documents' => ['sometimes', 'array'],
            'delete_documents.*' => ['string'],

            'subclients' => ['nullable', 'array'],
            'subclients.*.id' => ['nullable', 'string'],
            'subclients.*.username' => ['required_with:subclients', 'nullable', 'string', 'max:100'],
            'subclients.*.date_of_birth' => ['required_with:subclients', 'nullable', 'date', 'before_or_equal:today'],
            'subclients.*.gender' => ['required_with:subclients', 'nullable', 'in:m,f'],
            'subclients.*.phone_number' => ['nullable', 'string', 'max:20'],
            'subclients.*.email' => ['nullable', 'email', 'max:100'],
            'subclients.*.national_id' => ['nullable', 'string', 'max:30'],
            'subclients.*.passport' => ['nullable', 'string', 'max:30'],
            'subclients.*.address' => ['required_with:subclients', 'nullable', 'string', 'max:255'],
            'subclients.*.description' => ['nullable', 'string', 'max:255'],
            'subclients.*.sub_client_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:10240'],

            'delete_subclients' => ['nullable', 'array'],
            'delete_subclients.*' => ['string'],
        ], [
            'username.required' => __('client.username_required'),
            'gender.required' => __('client.gender_required'),
            'gender.in' => __('client.gender_invalid'),
            'phone_number.required' => __('client.phone_required'),
            'date_of_birth.required' => __('client.date_of_birth_required'),
            'address.required' => __('client.address_required'),
            'start_rental_date.required' => __('client.start_rental_date_required'),
        ]);

        $deleteDocumentIds = $request->input('delete_documents', []);
        $deleteSubclientIds = $request->input('delete_subclients', []);

        try {
            $payload = [
                '_method' => 'PATCH',
                'room_id' => $validated['room_id'],
                'username' => $validated['username'],
                'date_of_birth' => Carbon::parse($validated['date_of_birth'])->format('Y-m-d'),
                'gender' => $validated['gender'],
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'] ?? '',
                'national_id' => $validated['national_id'] ?? '',
                'passport' => $validated['passport'] ?? '',
                'address' => $validated['address'],
                'start_rental_date' => Carbon::parse($validated['start_rental_date'])->format('Y-m-d'),
                'end_rental_date' => !empty($validated['end_rental_date'])
                    ? Carbon::parse($validated['end_rental_date'])->format('Y-m-d')
                    : '',
                'description' => $validated['description'] ?? '',
                'subclients' => $this->normalizeSubclients($validated['subclients'] ?? []),
                'delete_subclients' => $deleteSubclientIds,
            ];

            $response = $this->sendClientRequest(
                endpoint: 'v1/clients/' . $clientId,
                payload: $payload,
                request: $request,
                locationId: $locationId
            );

            if (($response['status'] ?? '') !== 'success') {
                return back()
                    ->withErrors([
                        'api' => $this->apiErrorMessage($response, __('client.update_failed')),
                    ])
                    ->withInput();
            }

            $deleteErrors = [];

            foreach ($deleteDocumentIds as $docId) {
                if (!$docId) {
                    continue;
                }

                try {
                    $deleteResponse = $this->api()
                        ->withHeaders(['Location-Id' => $locationId])
                        ->delete('v1/documents/' . $docId);

                    if (($deleteResponse['status'] ?? 'success') !== 'success' && ($deleteResponse['success'] ?? true) !== true) {
                        $deleteErrors[] = $deleteResponse['message'] ?? "Failed to delete document {$docId}";
                    }
                } catch (Throwable $e) {
                    $deleteErrors[] = "Failed to delete document {$docId}: " . $e->getMessage();
                }
            }

            if (!empty($deleteErrors)) {
                return redirect()
                    ->route('clients.edit', [
                        'id' => $clientId,
                        'locationId' => $locationId,
                    ])
                    ->with('success', __('client.update_success'))
                    ->withErrors([
                        'documents' => $this->stringError($deleteErrors),
                    ]);
            }

            return redirect()
                ->route('clients.edit', [
                    'id' => $clientId,
                    'locationId' => $locationId,
                ])
                ->with('success', __('client.update_success'));
        } catch (RequestException $e) {
            $response = $e->response?->json() ?? [];

            return back()
                ->withErrors([
                    'api' => $this->apiErrorMessage($response, $e->getMessage()),
                ])
                ->withInput();
        } catch (Throwable $e) {
            Log::error('Client update failed', [
                'client_id' => $clientId,
                'room_id' => $roomId,
                'location_id' => $locationId,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors([
                    'api' => $this->stringError($e->getMessage()),
                ])
                ->withInput();
        }
    }

    public function updateClientStatus($clientId, $status, $locationId)
    {
        try {
            $response = $this->api()
                ->withHeaders(['Location-Id' => $locationId])
                ->patch("v1/clients/{$clientId}/status", [
                    '_method' => 'PATCH',
                    'status' => $status,
                ]);

            if (($response['status'] ?? '') === 'success') {
                return back()->with('success', __('client.status_updated_successfully'));
            }

            return back()
                ->withErrors([
                    'api' => $this->apiErrorMessage($response, __('client.status_update_failed')),
                ])
                ->withInput();
        } catch (Throwable $e) {
            return back()
                ->withErrors([
                    'api' => $this->stringError($e->getMessage()),
                ])
                ->withInput();
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

            return back()
                ->withErrors([
                    'api' => $this->apiErrorMessage($response, __('client.delete_failed')),
                ]);
        } catch (Throwable $e) {
            return back()
                ->withErrors([
                    'api' => $this->stringError($e->getMessage()),
                ]);
        }
    }

    private function sendClientRequest(string $endpoint, array $payload, Request $request, string $locationId): array
    {
        [$files, $fileFields] = $this->clientMultipartFiles($request);

        return $this->api()->post(
            $endpoint,
            $this->flattenForMultipart($payload),
            token: null,
            asForm: true,
            files: $files,
            fileField: empty($fileFields) ? '' : $fileFields,
            moreHeaders: ['Location-Id' => $locationId]
        );
    }

    private function clientMultipartFiles(Request $request): array
    {
        $files = [];
        $fileFields = [];

        if ($request->hasFile('image')) {
            $files[] = $request->file('image');
            $fileFields[] = 'client_image';
        }

        if ($request->hasFile('documents')) {
            foreach ((array) $request->file('documents') as $document) {
                if ($document) {
                    $files[] = $document;
                    $fileFields[] = 'files[]';
                }
            }
        }

        foreach ((array) $request->file('subclients', []) as $index => $subFiles) {
            if (is_array($subFiles) && !empty($subFiles['sub_client_image'])) {
                $files[] = $subFiles['sub_client_image'];
                $fileFields[] = "subclients[{$index}][sub_client_image]";
            }
        }

        return [$files, $fileFields];
    }

    private function normalizeSubclients(array $subclients): array
    {
        $normalized = [];

        foreach ($subclients as $index => $subclient) {
            if (blank($subclient['username'] ?? null)) {
                continue;
            }

            $normalized[$index] = [
                'id' => $subclient['id'] ?? '',
                'username' => $subclient['username'],
                'date_of_birth' => Carbon::parse($subclient['date_of_birth'])->format('Y-m-d'),
                'gender' => $subclient['gender'],
                'phone_number' => $subclient['phone_number'] ?? '',
                'email' => $subclient['email'] ?? '',
                'national_id' => $subclient['national_id'] ?? '',
                'passport' => $subclient['passport'] ?? '',
                'address' => $subclient['address'],
                'description' => $subclient['description'] ?? '',
            ];
        }

        return $normalized;
    }

    private function flattenForMultipart(array $data, string $prefix = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $field = $prefix === ''
                ? (string) $key
                : "{$prefix}[{$key}]";

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenForMultipart($value, $field));
                continue;
            }

            if ($value !== null) {
                $result[$field] = $value;
            }
        }

        return $result;
    }

    private function documentList(mixed $documents): array
    {
        if (!is_array($documents)) {
            return [];
        }

        return collect($documents)
            ->filter(fn($document) => is_array($document))
            ->map(function (array $document) {
                $fileUrl = $this->stringValue($document['file_url'] ?? null, '');

                return [
                    'id' => $this->stringValue($document['id'] ?? null, ''),
                    'file_name' => $this->stringValue($document['file_name'] ?? null, __('Document')),
                    'file_url' => $fileUrl,
                    'view_url' => $fileUrl !== '' ? apiBaseUrl() . $fileUrl : null,
                ];
            })
            ->values()
            ->toArray();
    }

    private function subclientList(mixed $subclients): array
    {
        if (!is_array($subclients)) {
            return [];
        }

        return collect($subclients)
            ->filter(fn($subclient) => is_array($subclient))
            ->map(function (array $subclient) {
                $imagePath = $this->stringValue($subclient['sub_client_image'] ?? null, '');
                $gender = $this->stringValue($subclient['gender'] ?? null, '');

                return [
                    'id' => $this->stringValue($subclient['id'] ?? null, ''),
                    'username' => $this->stringValue($subclient['username'] ?? null, ''),
                    'date_of_birth' => $this->dateValue($subclient['date_of_birth'] ?? null),
                    'gender' => $gender,
                    'gender_mapped' => match ($gender) {
                        'm', 'M', 'male', 'ប្រុស' => 'm',
                        'f', 'F', 'female', 'ស្រី' => 'f',
                        default => '',
                    },
                    'phone_number' => $this->stringValue($subclient['phone_number'] ?? null, ''),
                    'email' => $this->stringValue($subclient['email'] ?? null, ''),
                    'national_id' => $this->stringValue($subclient['national_id'] ?? null, ''),
                    'passport' => $this->stringValue($subclient['passport'] ?? null, ''),
                    'address' => $this->stringValue($subclient['address'] ?? null, ''),
                    'description' => $this->stringValue($subclient['description'] ?? null, ''),
                    'sub_client_image' => $imagePath,
                    'sub_client_image_url' => $imagePath !== ''
                        ? apiBaseUrl() . $imagePath
                        : asset('imgs/default-avatar.png'),
                ];
            })
            ->values()
            ->toArray();
    }

    private function stringValue(mixed $value, string $default = '-'): string
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_string($value) || is_numeric($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            foreach (['name', 'title', 'label', 'value', 'en', 'km', 'room_name', 'type_name', 'location_name'] as $key) {
                if (array_key_exists($key, $value)) {
                    return $this->stringValue($value[$key], $default);
                }
            }

            $flattened = collect($value)
                ->flatten()
                ->filter(fn($item) => is_string($item) || is_numeric($item))
                ->first();

            return $flattened !== null ? (string) $flattened : $default;
        }

        if (is_object($value)) {
            return method_exists($value, '__toString') ? (string) $value : $default;
        }

        return $default;
    }

    private function dateValue(mixed $value): string
    {
        if (blank($value)) {
            return '';
        }

        try {
            return Carbon::parse($this->stringValue($value, ''))->format('Y-m-d');
        } catch (Throwable) {
            return $this->stringValue($value, '');
        }
    }

    private function numericValue(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function moneyValue(mixed $value): string
    {
        if (!is_numeric($value)) {
            return '-';
        }

        return number_format((float) $value, 2) . '៛';
    }

    private function apiErrorMessage(array $response, string $fallback): string
    {
        if (!empty($response['errors']) && is_array($response['errors'])) {
            $messages = [];

            foreach ($response['errors'] as $fieldErrors) {
                if (is_array($fieldErrors)) {
                    foreach ($fieldErrors as $fieldError) {
                        $messages[] = $this->stringError($fieldError);
                    }
                } else {
                    $messages[] = $this->stringError($fieldErrors);
                }
            }

            if (!empty($messages)) {
                return implode(' ', $messages);
            }
        }

        if (!empty($response['message'])) {
            return $this->stringError($response['message']);
        }

        return $this->stringError($fallback);
    }

    private function stringError(mixed $message): string
    {
        if (is_array($message)) {
            return implode(' ', array_map(fn($item) => $this->stringError($item), $message));
        }

        if (is_object($message)) {
            return json_encode($message, JSON_UNESCAPED_UNICODE) ?: 'Unknown error';
        }

        return (string) $message;
    }
}