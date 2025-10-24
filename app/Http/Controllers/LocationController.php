<?php

namespace App\Http\Controllers;

use Exception;
use App\Utils\Util;
use App\Enum\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

class LocationController extends Controller
{

    public function index(Request $request)
    {
        $buttons = [
            [
                'text' => __('titles.create') . __('titles.location'),
                'icon' => 'plus',
                'class' => 'btn btn-primary btn-5 d-none d-sm-inline-block',
                'url' => route('location.create'),
            ]
        ];

        try {
            $perPage = $request->query('per_page', 10);
            $currentPage = $request->query('page', 1);

            // Get API response
            $response = $this->api()->get('v1/locations');
            $paginatedData = $response['locations'] ?? [];

            // Convert to collection and map 'is_active' to status badge
            $dataCollection = collect($paginatedData['data'] ?? [])->transform(function ($item) {
                $item['status_badge'] = Status::getStatus($item['is_active']); // use 'is_active'
                $item['create_date_kh'] = Util::translateDateToKhmer($item['created_at'], 'd F, Y h:i A');
                $item['update_date_kh'] = Util::translateDateToKhmer($item['updated_at'], 'd F, Y h:i A');
                return $item;
            });

            // Manually paginate
            $locations = new LengthAwarePaginator(
                $dataCollection->forPage($currentPage, $perPage),
                $paginatedData['total'] ?? $dataCollection->count(),
                $perPage,
                $currentPage,
                ['path' => url()->current(), 'query' => $request->query()]
            );
        } catch (Exception $e) {
            $locations = new LengthAwarePaginator([], 0, 10);
        }

        return view('app.locations.index', compact('buttons', 'locations'));
    }

    public function create()
    {
        $buttons = [
            [
                'text' => __('titles.index') . __('titles.location'),
                'icon' => 'plus',
                'class' => 'btn btn-primary btn-5 d-none d-sm-inline-block',
                'url' => route('location.index'),
                // 'attrs' => 'data-bs-toggle=modal data-bs-target=#modal-report',
            ]
        ];

        return view('app.locations.create', compact('buttons'));
    }

    public function store(Request $request)
    {
        // ✅ Validate input
        $validated = $request->validate([
            'location_name' => 'required|string|max:255',
            'location_address' => 'required|string|max:255',
            'location_description' => 'nullable|string|max:1000',
        ], [
            'location_name.required' => __('location.name_required'),
            'location_name.string' => __('location.name_string'),
            'location_name.max' => __('location.name_max'),

            'location_address.required' => __('location.address_required'),
            'location_address.string' => __('location.address_string'),
            'location_address.max' => __('location.address_max'),

            'location_description.string' => __('location.description_string'),
            'location_description.max' => __('location.description_max'),
        ]);

        // ✅ Prepare payload
        $payload = [
            'created_by' => Session::get('user')['id'] ?? null,
            'updated_by' => Session::get('user')['id'] ?? null,
            'location_name' => $validated['location_name'],
            'address' => $validated['location_address'],
            'description' => $validated['location_description'] ?? null,
        ];

        // ✅ Send to API
        $apiResponse = $this->api()->post('v1/locations', $payload);

        // ✅ Handle API response
        if (($apiResponse['status'] ?? '') === 'success') {
            return redirect()
                ->route('location.index')
                ->with('success', __('location.created_successfully'));
        }

        // ❌ Handle failure
        return back()
            ->withInput()
            ->withErrors($apiResponse['errors'] ?? ['error' => $apiResponse['message'] ?? __('location.create_failed')]);
    }

    public function edit(Request $request, $id)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('location.index'),
            ],
        ];

        $statusses = [
            Status::ACTIVE => Status::getStatus(Status::ACTIVE),
            Status::INACTIVE => Status::getStatus(Status::INACTIVE),
        ];

        // Get location detail
        $locationResponse = $this->api()->get("v1/locations", ['id' => $id]);
        $location = $locationResponse['locations']['data'][0] ?? null;

        return view('app.locations.edit', compact('buttons', 'location', 'statusses'));
    }

    public function update(Request $request, $id)
    {
        // ✅ Validate input
        $validated = $request->validate([
            'location_name' => 'required|string|max:255',
            'location_address' => 'required|string|max:255',
            'location_description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|in:0,1',
        ], [
            'location_name.required' => __('location.name_required'),
            'location_name.string' => __('location.name_string'),
            'location_name.max' => __('location.name_max'),

            'location_address.required' => __('location.address_required'),
            'location_address.string' => __('location.address_string'),
            'location_address.max' => __('location.address_max'),

            'location_description.string' => __('location.description_string'),
            'location_description.max' => __('location.description_max'),
        ]);

        // ✅ Prepare payload for API
        $payload = [
            '_method' => 'PATCH', // required for APIs expecting PATCH
            'updated_by' => Session::get('user')['id'] ?? null,
            'location_name' => $validated['location_name'],
            'address' => $validated['location_address'],
            'description' => $validated['location_description'] ?? null,
            'is_active' => $validated['is_active'],
        ];

        // ✅ Send to API
        $apiResponse = $this->api()->post("v1/locations/{$id}", $payload);

        // ✅ Handle API response
        if (($apiResponse['status'] ?? '') === 'success') {
            return redirect()
                ->route('location.index')
                ->with('success', __('location.updated_successfully'));
        }

        // ❌ Handle failure
        return back()
            ->withInput()
            ->withErrors($apiResponse['errors'] ?? ['error' => $apiResponse['message'] ?? __('location.update_failed')]);
    }

    public function destroy($id)
    {
        try {
            // ✅ Send real DELETE request to API
            $apiResponse = $this->api()->delete("v1/locations/{$id}");

            // ✅ Handle success
            if (($apiResponse['status'] ?? '') === 'success') {
                return redirect()
                    ->route('location.index')
                    ->with('success', __('location.deleted_successfully'));
            }

            // ❌ Handle failure
            return back()->withErrors([
                'error' => $apiResponse['errors'] ?? $apiResponse['message'] ?? __('location.delete_failed'),
            ]);
        } catch (Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage() ?: __('location.delete_failed'),
            ]);
        }
    }
}
