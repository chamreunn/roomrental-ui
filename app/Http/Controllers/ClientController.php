<?php

namespace App\Http\Controllers;

use App\Enum\Active;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Session;

class ClientController extends Controller
{
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

    public function edit(Request $request, $clientId)
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
        $response = $this->api()->get('v1/clients', ['id' => $clientId]);

        $client = $response['clients']['data'][0] ?? null;

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

        return view('app.clients.edit', compact('client', 'buttons'));
    }

    public function store(Request $request, $roomId)
    {
        $validated = $request->validate([
            'username'          => 'required|string|max:100',
            'gender'            => 'required|string|in:m,f',
            'phone_number'      => 'required|string|max:20',
            'email'             => 'nullable|email|max:100',
            'dob'               => 'required|date_format:d-m-Y',
            'national_id'       => 'nullable|string|max:30',
            'passport'          => 'nullable|string|max:30',
            'address'           => 'required|string|max:255',
            'image'             => 'nullable|image|max:2048',
            'start_rental_date' => 'required|date',
            'end_rental_date'   => 'nullable|date',
            'description'       => 'nullable|string|max:255',
        ]);

        try {
            // ✅ Convert dates to correct format
            $dob = Carbon::createFromFormat('d-m-Y', $validated['dob'])->format('Y-m-d');
            $startDate = $validated['start_rental_date']
                ? Carbon::parse($validated['start_rental_date'])->format('Y-m-d')
                : now()->format('Y-m-d');
            $endDate = $validated['end_rental_date']
                ? Carbon::parse($validated['end_rental_date'])->format('Y-m-d')
                : null;

            // ✅ Prepare payload
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

            // ✅ Send to API (multipart)
            $response = $this->api()->post(
                'v1/clients',
                $payload,
                null,                      // token
                true,                      // as multipart/form-data
                $request->hasFile('image') ? [$request->file('image')] : [], // ✅ file array
                'client_image'             // ✅ API field name for file
            );

            if (($response['status'] ?? '') === 'success') {
                return redirect()->route(dashboardRoute())
                    ->with('success', __('messages.rental_success'));
            }

            return back()->withErrors([
                'api' => 'API returned: ' . json_encode($response),
            ])->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors(['api' => $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, $clientId, $roomId)
    {
        $validated = $request->validate([
            'username'          => 'required|string|max:100',
            'gender'            => 'required|string|in:m,f',
            'phone_number'      => 'required|string|max:20',
            'email'             => 'nullable|email|max:100',
            'date_of_birth'     => 'required|date_format:d-m-Y',
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
            // Convert dates to Y-m-d format
            $dob = Carbon::createFromFormat('d-m-Y', $validated['date_of_birth'])->format('Y-m-d');
            $startDate = $validated['start_rental_date'] ? Carbon::parse($validated['start_rental_date'])->format('Y-m-d') : now()->format('Y-m-d');
            $endDate = $validated['end_rental_date'] ? Carbon::parse($validated['end_rental_date'])->format('Y-m-d') : null;

            // Prepare payload
            $payload = [
                '_method'           => 'PATCH',
                'created_by'        => Session::get('user.id'),
                'updated_by'        => Session::get('user.id'),
                // 'room_id'           => $roomId,
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
                'client_image'
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

    public function updateClientStatus($clientId, $status)
    {
        try {
            // Send PATCH request to update client status
            $response = $this->api()->post("v1/clients/{$clientId}/status", [
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
}
