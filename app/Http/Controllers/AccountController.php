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
        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'dob' => 'required|date_format:d-m-Y',
            'password' => 'required|string',
            'address' => 'nullable|string|max:500',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Convert DOB to API format
        $dob = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['dob'])->format('Y-m-d');

        // Prepare data payload (non-file fields)
        $payload = [
            'name' => $validated['name'],
            'role' => $validated['role'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'date_of_birth' => $dob,
            'password' => $validated['password'],
            'address' => $validated['address'] ?? null,
        ];

        // Prepare files array for attach()
        $files = [];
        if ($request->hasFile('profile_picture')) {
            $files['profile_picture'] = $request->file('profile_picture');
        }

        // Send to API as multipart/form-data
        $apiResponse = $this->api()->post('v1/users', $payload, null, true, $files, 'profile_picture');

        if ($apiResponse['success'] ?? false) {
            return redirect()->route('account.index')->with('success', __('account.created_successfully'));
        }

        return back()->withInput()->withErrors($apiResponse['errors'] ?? ['error' => __('account.creation_failed')]);
    }
}
