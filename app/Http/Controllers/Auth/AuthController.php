<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // For API calls
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Call your API endpoint
        $response = Http::post(config('services.api.url') . '/login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            // Store token in session
            Session::put('api_token', $data['token']);
            Session::put('user', $data['user']);

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials or server error.']);
    }
}
