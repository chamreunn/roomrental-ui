<?php

namespace App\Http\Controllers\Auth;

use App\Enum\AbilitiesStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // ✅ Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => __('auth.email_required'),
            'email.email' => __('auth.email_invalid'),
            'password.required' => __('auth.password_required'),
        ]);

        try {
            // Call API
            $response = $this->api()->post('login', [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if (!isset($response['error']) || $response['error'] === false) {
                $user = $response['user'];
                $token = $response['token'];

                Session::put('api_token', $token);

                $baseApiUrl = apiBaseUrl();
                Session::put('user', [
                    'id' => $user['id'],
                    'role' => $user['role'],
                    'role_badge' => AbilitiesStatus::getStatus($user['role']),
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone_number' => $user['phone_number'],
                    'address' => $user['address'],
                    'date_of_birth' => $user['date_of_birth'],
                    'profile_picture' => api_image($user['profile_picture']),

                    'user_locations' => $user['user_locations'] ?? [],
                ]);

                Session::flash('success', __('auth.welcome', ['name' => $user['name']]));

                return match ($user['role']) {
                    'admin' => redirect()->route('dashboard.admin'),
                    'manager' => redirect()->route('dashboard.manager'),
                    'user' => redirect()->route('dashboard.user'),
                    default => redirect()->route('home'),
                };
            }

            // Handle API validation errors
            if (isset($response['errors'])) {
                return back()->withErrors($response['errors'])->withInput();
            }

            // Fallback general API error
            Session::flash('error', $response['message'] ?? __('auth.login_failed'));
            return back()->withInput();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // ✅ Catch HTTP exceptions (like 401 Unauthorized)
            $resp = $e->response->json();
            $errorMessage = $resp['message'] ?? __('auth.login_failed');
            Session::flash('error', $errorMessage);
            return back()->withInput();
        } catch (\Exception $e) {
            // ✅ Catch unexpected errors
            Session::flash('error', 'Unexpected error: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    // Example logout
    public function logout()
    {
        try {
            // ✅ Include token if your API requires authentication for logout
            $token = Session::get('api_token');

            // Only call API logout if token exists
            if ($token) {
                $response = $this->api()->post('v1/logout', [], $token);

                // Optional: check if API responded successfully
                if (isset($response['message']) && $response['message'] === 'Logged out successfully') {
                    // You can flash a message if you want
                    Session::flash('success', __('auth.logout_success'));
                }
            }
        } catch (\Exception $e) {
            // Just log or ignore the error — don’t break logout if API fails
            Log::warning('Logout API failed: ' . $e->getMessage());
        }

        // ✅ Always clear session locally
        Session::forget(['api_token', 'user']);
        // Session::flush();

        // ✅ Redirect to login page
        return redirect()->route('login')->with('success', __('auth.logout_success'));
    }
}
