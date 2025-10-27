<?php

namespace App\Http\Controllers;

use App\Enum\AbilitiesStatus;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $buttons = [
            [
                'text' => __('titles.create') . __('titles.account'),
                'icon' => 'plus',
                'class' => 'btn btn-primary btn-5 d-none d-sm-inline-block',
                'url' => route('account.create'),
                // 'attrs' => 'data-bs-toggle=modal data-bs-target=#modal-report',
            ]
        ];

        try {
            $perPage = $request->query('per_page', 10);
            $currentPage = $request->query('page', 1);

            // Make sure your API call actually gets the correct page
            $response = $this->api()->get('v1/users');
            $paginatedData = $response['users'] ?? [];

            $alldatas = collect($paginatedData['data'] ?? [])->transform(function ($item) {

                $item['user_profile'] = apiBaseUrl() . '/' . ltrim($item['profile_picture'], '/');
                $item['role_badge'] = AbilitiesStatus::getStatus($item['role']);

                return $item;
            });

            // Slice the collection manually for the current page
            $currentItems = $alldatas->slice(($currentPage - 1) * $perPage, $perPage)->values();

            $users = new LengthAwarePaginator(
                $currentItems,
                $paginatedData['total'] ?? $alldatas->count(),
                $perPage,
                $currentPage,
                ['path' => url()->current(), 'query' => $request->query()]
            );
        } catch (Exception $e) {
            $users = new LengthAwarePaginator([], 0, 10);
        }

        return view('app.accounts.index', compact('buttons', 'users'));
    }

    public function create(Request $request)
    {
        $buttons = [
            [
                'text' => __('titles.index') . __('titles.account'),
                'icon' => 'user',
                'class' => 'btn btn-primary btn-5 d-none d-sm-inline-block',
                'url' => route('account.index'),
            ]
        ];

        // Prepare roles with name and class
        $roles = [
            AbilitiesStatus::ADMIN => AbilitiesStatus::getStatus(AbilitiesStatus::ADMIN),
            AbilitiesStatus::MANAGER => AbilitiesStatus::getStatus(AbilitiesStatus::MANAGER),
            AbilitiesStatus::USER => AbilitiesStatus::getStatus(AbilitiesStatus::USER),
        ];

        $response = $this->api()->get('v1/locations');
        $locations = $response['locations']['data'];

        return view('app.accounts.create', compact('buttons', 'roles', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'dob' => 'required|date_format:d-m-Y',
            'password' => 'required|string',
            'address' => 'nullable|string|max:500',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'location_id' => 'nullable|array',
            'location_id.*' => 'uuid',
        ]);

        // Convert DOB to API format
        $dob = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['dob'])->format('Y-m-d');

        // Prepare payload
        $payload = [
            'name' => $validated['name'],
            'role' => $validated['role'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'date_of_birth' => $dob,
            'password' => $validated['password'],
            'address' => $validated['address'] ?? null,
        ];

        // Handle file upload
        $files = [];
        if ($request->hasFile('profile_picture')) {
            $files['profile_picture'] = $request->file('profile_picture');
        }

        // Send to API (multipart/form-data)
        $apiResponse = $this->api()->post('v1/users', $payload, null, true, $files, 'profile_picture');

        if (!($apiResponse['id'] ?? false)) {
            return back()->withInput()->withErrors(
                $apiResponse['errors'] ?? ['error' => __('account.creation_failed')]
            );
        }

        // Attach user to locations if any selected
        if (!empty($validated['location_id'])) {
            foreach ($validated['location_id'] as $location_id) {
                $this->api()->post('v1/user-locations', [
                    'user_id' => $apiResponse['id'],
                    'location_id' => $location_id,
                ]);
            }
        }

        return redirect()
            ->route('account.index')
            ->with('success', __('account.created_successfully'));
    }

    public function show(Request $request, $id)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('account.index'),
            ],
        ];

        // Roles
        $roles = [
            AbilitiesStatus::ADMIN => AbilitiesStatus::getStatus(AbilitiesStatus::ADMIN),
            AbilitiesStatus::MANAGER => AbilitiesStatus::getStatus(AbilitiesStatus::MANAGER),
            AbilitiesStatus::USER => AbilitiesStatus::getStatus(AbilitiesStatus::USER),
        ];

        // Get user detail
        $userResponse = $this->api()->get("v1/users/{$id}");
        $user = $userResponse['user'] ?? null;

        $response = $this->api()->get('v1/locations');
        $locations = $response['locations']['data'];

        if (!$user) {
            return redirect()->route('account.index')->withErrors(__('account.user_not_found'));
        }

        // ✅ user_locations are already IDs, so just assign directly
        // ✅ Extract only location_id from user_locations
        $user['user_locations'] = collect($user['user_locations'] ?? [])
            ->pluck('location_id')
            ->toArray();

        return view('app.accounts.show', compact('buttons', 'user', 'roles', 'locations'));
    }


    public function update(Request $request, $id)
    {
        try {
            // ✅ Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'role' => 'required',
                'email' => 'required|email|max:255',
                'phone_number' => 'required|string|max:20',
                'dob' => 'required|date_format:d-m-Y',
                'password' => 'nullable|string',
                'address' => 'nullable|string|max:500',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'location_id' => 'nullable|array',
                'location_id.*' => 'uuid',
            ]);

            // ✅ Convert DOB to API format
            $dob = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['dob'])->format('Y-m-d');

            // ✅ Prepare payload for API
            $payload = [
                '_method' => 'PATCH',
                'name' => $validated['name'],
                'role' => $validated['role'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'date_of_birth' => $dob,
                'address' => $validated['address'] ?? null,
            ];

            if (!empty($validated['password'])) {
                $payload['password'] = $validated['password'];
            }

            // ✅ Handle profile picture upload
            $files = [];
            if ($request->hasFile('profile_picture')) {
                $files['profile_picture'] = $request->file('profile_picture');
            }

            // ✅ Update user info via API
            $apiResponse = $this->api()->post("v1/users/{$id}", $payload, null, true, $files, 'profile_picture');

            // ===============================
            // ✅ Sync user locations
            // ===============================

            $newLocations = $validated['location_id'] ?? [];

            // Get full current user locations (pivot records)
            $currentUserLocations = $this->api()->get("v1/users/{$id}")['user']['user_locations'] ?? [];

            // Current location IDs for comparison
            $currentLocationIds = collect($currentUserLocations)->pluck('location_id')->toArray();

            // Determine locations to add and remove
            $locationsToAdd = array_diff($newLocations, $currentLocationIds);
            $locationsToRemove = array_diff($currentLocationIds, $newLocations);

            // Add new locations
            foreach ($locationsToAdd as $location_id) {
                try {
                    $this->api()->post('v1/user-locations', [
                        'user_id' => $id,
                        'location_id' => $location_id,
                    ]);
                } catch (\Illuminate\Http\Client\RequestException $e) {
                    $errorMessage = $this->mapApiErrorToTranslation($e);
                    return back()->withInput()->with('error', $errorMessage);
                }
            }

            // Remove unchecked locations
            foreach ($locationsToRemove as $location_id) {
                // Find the corresponding pivot ID
                $userLocation = collect($currentUserLocations)->firstWhere('location_id', $location_id);
                if ($userLocation) {
                    try {
                        $this->api()->delete('v1/user-locations/' . $userLocation['id']);
                    } catch (\Illuminate\Http\Client\RequestException $e) {
                        $errorMessage = $this->mapApiErrorToTranslation($e);
                        return back()->withInput()->with('error', $errorMessage);
                    }
                }
            }

            // ✅ Handle success
            if (($apiResponse['success'] ?? false) || isset($apiResponse['id'])) {
                return redirect()->back()->with('success', __('account.updated_successfully'));
            }

            return back()
                ->withInput()
                ->with('error', __('account.update_failed'));
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $errorMessage = $this->mapApiErrorToTranslation($e);
            return back()->withInput()->with('error', $errorMessage);
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    /**
     * Map API error messages to translation keys
     */
    protected function mapApiErrorToTranslation(\Illuminate\Http\Client\RequestException $e)
    {
        $response = $e->response->json();
        $errorMessage = $response['message'] ?? null;

        if (isset($response['errors'])) {
            $flatErrors = collect($response['errors'])->flatten()->toArray();
            $errorMessage = implode(', ', $flatErrors);
        }

        if (str_contains($errorMessage, 'already assigned')) {
            return __('account.location_already_assigned');
        } elseif (str_contains($errorMessage, 'not found')) {
            return __('account.location_not_found');
        } elseif (str_contains($errorMessage, 'validation')) {
            return __('account.validation_error');
        }

        return $errorMessage ?? __('account.update_failed');
    }
}
