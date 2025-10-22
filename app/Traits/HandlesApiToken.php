<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait HandlesApiToken
{
    /**
     * Get the current API token from session, if available.
     */
    protected function getApiToken(): ?string
    {
        return session('api_token');
    }

    /**
     * Store the API token in session.
     */
    protected function storeApiToken(string $token): void
    {
        session(['api_token' => $token]);
    }

    /**
     * Remove the token from session.
     */
    protected function clearApiToken(): void
    {
        session()->forget('api_token');
    }

    /**
     * Refresh token if your API supports it.
     * You can adjust this method to call your refresh endpoint.
     */
    protected function refreshApiToken(): ?string
    {
        try {
            // Example refresh flow (adjust endpoint/fields as needed)
            $refreshToken = session('refresh_token');
            if (!$refreshToken) {
                return null;
            }

            $response = Http::post(config('api.refresh_url'), [
                'refresh_token' => $refreshToken,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $newToken = $data['access_token'] ?? null;

                if ($newToken) {
                    $this->storeApiToken($newToken);
                    return $newToken;
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('Failed to refresh API token: ' . $e->getMessage());
            return null;
        }
    }
}
