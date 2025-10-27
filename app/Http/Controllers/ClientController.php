<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ClientController extends Controller
{
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
            'start_rental_date' => 'nullable|date',
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

            if (isset($response['status']) && $response['status'] === 'success') {
                return back()->with('success', __('messages.rental_success'));
            }

            Log::error('Client API failed', ['response' => $response]);
            return back()->withErrors([
                'api' => 'API returned: ' . json_encode($response),
            ])->withInput();
        } catch (\Throwable $e) {
            Log::error('Client Store Exception', ['error' => $e->getMessage()]);
            return back()->withErrors(['api' => $e->getMessage()])->withInput();
        }
    }
}
