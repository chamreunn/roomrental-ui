<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Enum\AbilitiesStatus;
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

                $item['user_profile'] = api_image($item['profile_picture']);
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
            'name'  => 'required|string|max:255',
            'role'  => 'required|string',
            'email' => 'required|email|max:255', // add unique:users,email if saving locally
            'phone_number' => 'required|string|max:20',
            'dob' => 'required|date_format:d-m-Y',
            'password' => 'required|string|min:6',
            'address' => 'nullable|string|max:500',

            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            'location_id' => 'nullable|array',
            'location_id.*' => 'uuid',

            'can_cash_transaction' => 'nullable|boolean',
        ]);

        $dob = Carbon::createFromFormat('d-m-Y', $validated['dob'])->format('Y-m-d');

        $payload = [
            'name' => $validated['name'],
            'role' => $validated['role'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'date_of_birth' => $dob,
            'password' => $validated['password'],
            'address' => $validated['address'] ?? null,

            // checkbox: send 1/0
            'can_cash_transaction' => (int) $request->boolean('can_cash_transaction'),
        ];

        $files = [];
        if ($request->hasFile('profile_picture')) {
            $files['profile_picture'] = $request->file('profile_picture');
        }

        // Your API wrapper call (kept same style)
        $apiResponse = $this->api()->post('v1/users', $payload, null, true, $files, 'profile_picture');

        if (!($apiResponse['id'] ?? false)) {
            return back()->withInput()->withErrors(
                $apiResponse['errors'] ?? ['error' => __('account.creation_failed')]
            );
        }

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

        $roles = [
            AbilitiesStatus::ADMIN   => AbilitiesStatus::getStatus(AbilitiesStatus::ADMIN),
            AbilitiesStatus::MANAGER => AbilitiesStatus::getStatus(AbilitiesStatus::MANAGER),
            AbilitiesStatus::USER    => AbilitiesStatus::getStatus(AbilitiesStatus::USER),
        ];

        $userResponse = $this->api()->get("v1/users/{$id}");
        $user = $userResponse['user'] ?? null;

        if (!$user) {
            return redirect()->route('account.index')->withErrors(__('account.user_not_found'));
        }

        $response = $this->api()->get('v1/locations');
        $locations = $response['locations']['data'] ?? [];

        // user_locations -> array of location UUIDs
        $user['user_locations'] = collect($user['user_locations'] ?? [])
            ->pluck('location_id')
            ->toArray();

        // (optional) pre-format dob for blade
        $user['dob_dmy'] = !empty($user['date_of_birth'])
            ? Carbon::parse($user['date_of_birth'])->format('d-m-Y')
            : '';

        return view('app.accounts.show', compact('buttons', 'user', 'roles', 'locations'));
    }


    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'role' => 'required|string',
                'email' => 'required|email|max:255',
                'phone_number' => 'required|string|max:20',
                'dob' => 'required|date_format:d-m-Y',

                'password' => 'nullable|string|min:6',
                'address' => 'nullable|string|max:500',

                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

                'location_id' => 'nullable|array',
                'location_id.*' => 'uuid',

                'can_cash_transaction' => 'nullable|boolean',
            ]);

            // Convert DOB
            $dob = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['dob'])->format('Y-m-d');

            // Prepare payload
            $payload = [
                '_method' => 'PATCH',
                'name' => $validated['name'],
                'role' => $validated['role'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'date_of_birth' => $dob,
                'address' => $validated['address'] ?? null,

                // ✅ checkbox: always send 1/0
                'can_cash_transaction' => (int) $request->boolean('can_cash_transaction'),
            ];

            if (!empty($validated['password'])) {
                $payload['password'] = $validated['password'];
            }

            // File upload
            $files = [];
            if ($request->hasFile('profile_picture')) {
                $files['profile_picture'] = $request->file('profile_picture');
            }

            // Update user via API
            $apiResponse = $this->api()->post("v1/users/{$id}", $payload, null, true, $files, 'profile_picture');

            // If API returned an error structure
            if (!($apiResponse['success'] ?? false) && !isset($apiResponse['id'])) {
                return back()
                    ->withInput()
                    ->withErrors($apiResponse['errors'] ?? [])
                    ->with('error', __('account.update_failed'));
            }

            // ===============================
            // Sync user locations
            // ===============================

            $newLocations = $validated['location_id'] ?? [];

            // Fetch current pivot records
            $userResponse = $this->api()->get("v1/users/{$id}");
            $currentUserLocations = $userResponse['user']['user_locations'] ?? [];

            $currentLocationIds = collect($currentUserLocations)->pluck('location_id')->toArray();

            $locationsToAdd = array_values(array_diff($newLocations, $currentLocationIds));
            $locationsToRemove = array_values(array_diff($currentLocationIds, $newLocations));

            // Add
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

            // Remove
            foreach ($locationsToRemove as $location_id) {
                $userLocation = collect($currentUserLocations)->firstWhere('location_id', $location_id);

                if (!$userLocation || empty($userLocation['id'])) {
                    continue;
                }

                try {
                    $this->api()->delete('v1/user-locations/' . $userLocation['id']);
                } catch (\Illuminate\Http\Client\RequestException $e) {
                    $errorMessage = $this->mapApiErrorToTranslation($e);
                    return back()->withInput()->with('error', $errorMessage);
                }
            }

            return redirect()->back()->with('success', __('account.updated_successfully'));
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
