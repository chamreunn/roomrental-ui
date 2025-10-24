<?php

namespace App\Http\Controllers;

use Exception;
use App\Utils\Util;
use App\Enum\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

class RoomtypeController extends Controller
{
    public function index(Request $request)
    {
        $buttons = [
            [
                'text' => __('titles.create') . __('titles.roomtype'),
                'icon' => 'plus',
                'class' => 'btn btn-primary btn-5 d-none d-sm-inline-block',
                'url' => route('roomtype.create'),
                // 'attrs' => 'data-bs-toggle=modal data-bs-target=#modal-report',
            ]
        ];

        try {
            $perPage = $request->query('per_page', 10);
            $currentPage = $request->query('page', 1);

            // Get API response
            $response = $this->api()->get('v1/room-types');
            $paginatedData = $response['room_types'] ?? [];

            // Convert to collection and map 'is_active' to status badge
            $dataCollection = collect($paginatedData['data'] ?? [])->transform(function ($item) {
                $item['status_badge'] = Status::getStatus($item['is_active']); // use 'is_active'
                $item['create_date_kh'] = Util::translateDateToKhmer($item['created_at'], 'd F, Y h:i A');
                $item['update_date_kh'] = Util::translateDateToKhmer($item['updated_at'], 'd F, Y h:i A');
                return $item;
            });

            // Manually paginate
            $roomtypes = new LengthAwarePaginator(
                $dataCollection->forPage($currentPage, $perPage),
                $paginatedData['total'] ?? $dataCollection->count(),
                $perPage,
                $currentPage,
                ['path' => url()->current(), 'query' => $request->query()]
            );

        } catch (Exception $e) {
            $roomtypes = new LengthAwarePaginator([], 0, 10);
        }

        return view('app.room_typs.index', compact('buttons','roomtypes'));
    }

    public function create()
    {
        $buttons = [
            [
                'text' => __('titles.index') . __('titles.roomtype'),
                'icon' => 'plus',
                'class' => 'btn btn-primary btn-5 d-none d-sm-inline-block',
                'url' => route('roomtype.index'),
                // 'attrs' => 'data-bs-toggle=modal data-bs-target=#modal-report',
            ]
        ];

        return view('app.room_typs.create', compact('buttons'));
    }

    public function store(Request $request)
    {
        // ✅ Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'size' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => __('roomtype.name_required'),
            'name.string' => __('roomtype.name_string'),
            'name.max' => __('roomtype.name_max'),

            'size.required' => __('roomtype.size_required'),
            'size.string' => __('roomtype.size_string'),
            'size.max' => __('roomtype.size_max'),

            'price.required' => __('roomtype.price_required'),
            'price.numeric' => __('roomtype.price_numeric'),
            'price.min' => __('roomtype.price_min'),

            'description.string' => __('roomtype.description_string'),
            'description.max' => __('roomtype.description_max'),
        ]);

        // ✅ Prepare payload for API
        $payload = [
            'created_by' => Session::get('user')['id'] ?? null,
            'updated_by' => Session::get('user')['id'] ?? null,
            'type_name' => $validated['name'],
            'room_size' => $validated['size'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
        ];

        // ✅ Send data to API endpoint
        $apiResponse = $this->api()->post('v1/room-types', $payload);

        // ✅ Handle success
        if (($apiResponse['status'] ?? '') === 'success') {
            return redirect()
                ->route('roomtype.index')
                ->with('success', __('roomtype.created_successfully'));
        }

        // ❌ Handle failure
        return back()
            ->withInput()
            ->withErrors($apiResponse['errors'] ?? [
                'error' => $apiResponse['message'] ?? __('roomtype.create_failed'),
            ]);
    }

    public function destroy($id)
    {
        try {
            // Call the API to delete the location
            $apiResponse = $this->api()->post("v1/room-types/{$id}", [
                '_method' => 'DELETE',
            ]);

            // Check if API responded successfully
            if (($apiResponse['status'] ?? '') === 'success') {
                return redirect()
                    ->route('roomtype.index')
                    ->with('success', __('roomtype.deleted_successfully'));
            }

            // Handle failure
            return back()->withErrors([
                'error' => $apiResponse['errors'] ?? $apiResponse['message'] ?? __('roomtype.delete_failed')
            ]);
        } catch (Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage() ?: __('roomtype.delete_failed')
            ]);
        }
    }
}
