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
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => __('auth.email_required'),
            'email.email' => __('auth.email_invalid'),
            'password.required' => __('auth.password_required'),
        ]);

        // Call API
        $response = $this->api()->post('login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if (!isset($response['error']) || $response['error'] === false) {

            $user = $response['user'];
            $token = $response['token'];

            Session::put('api_token', $token);
            Session::put('user', $user);

            $role = $response['user']['role'];

            // ✅ Detect base API URL automatically
            $baseApiUrl = apiBaseUrl();

            // ✅ Store user session (lightweight)
            Session::put('user', [
                'id' => $user['id'],
                'role' => $user['role'],
                'role_badge' => AbilitiesStatus::getStatus($role),
                'name' => $user['name'],
                'email' => $user['email'],
                'phone_number' => $user['phone_number'],
                'address' => $user['address'],
                'date_of_birth' => $user['date_of_birth'],
                'profile_picture' => $baseApiUrl . '/' . ltrim($user['profile_picture'], '/'),
            ]);

            // Flash success message
            Session::flash('success', __('auth.welcome', ['name' => $response['user']['name']]));

            return match ($role) {
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

        // Fallback general error
        return back()->withErrors([
            'email' => $response['message'] ?? __('auth.login_failed'),
        ])->withInput();
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
