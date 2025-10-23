<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Traits\HandlesApiToken;

class ApiService
{
    use HandlesApiToken;

    protected $baseUrl;
    protected $verifySsl;

    public function __construct(string $overrideEnv = null)
    {
        $env = $overrideEnv ?? env('APP_ENV', 'local');
        $env = $env === 'local' ? 'local' : 'hosting';

        $this->baseUrl = rtrim(config("api.$env.api"), '/');
        $this->verifySsl = config("api.$env.ssl", false);
    }

    protected function buildUrl(string $endpoint): string
    {
        return "{$this->baseUrl}/api/" . ltrim($endpoint, '/');
    }

    protected function getHttpClient($token = null)
    {
        $headers = ['App_key' => config('custom.hrms_key')];

        $client = Http::withOptions(['verify' => $this->verifySsl])
            ->withHeaders($headers);

        if ($token) {
            $client = $client->withToken($token);
        }

        return $client;
    }

    public function get(string $endpoint, array $query = [], $token = null)
    {
        $url = $this->buildUrl($endpoint);
        $token = $token ?? $this->getApiToken();

        try {
            $response = $this->getHttpClient($token)
                ->timeout(15)
                ->retry(3, 200)
                ->get($url, $query);

            if ($response->status() === 401 && $newToken = $this->refreshApiToken()) {
                return $this->get($endpoint, $query, $newToken);
            }

            if ($response->status() === 401) {
                $this->clearApiToken();
                abort(401, 'សម័យប្រើប្រាស់ផុតកំណត់។ សូមចូលឡើងវិញ។');
            }

            return $this->handleResponse($response);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return ['error' => true, 'message' => 'មិនអាចភ្ជាប់ទៅកាន់ API បានទេ។ សូមព្យាយាមម្ដងទៀត។'];
        } catch (\Exception $e) {
            return ['error' => true, 'message' => 'មានបញ្ហាក្នុងការទាញយកទិន្នន័យ API៖ ' . $e->getMessage()];
        }
    }

    public function post(string $endpoint,  array $data = [],  $token = null, bool $asForm = false,  $files = [], $fileField = 'documents[]')
    {
        $url = $this->buildUrl($endpoint);
        $token = $token ?? $this->getApiToken();
        $http = $this->getHttpClient($token);

        // Attach files
        $files = is_array($files) ? $files : [$files];
        foreach ($files as $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                $http = $http->attach(
                    $fileField,
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                );
            }
        }

        // Determine if multipart/form-data is needed
        if (!empty($files) || $asForm) {
            // Send as multipart/form-data
            $response = $http->asMultipart()->post($url, $data);
        } else {
            // Send as JSON
            $response = $http->post($url, $data);
        }

        // Handle 401 with token refresh
        if ($response->status() === 401 && $newToken = $this->refreshApiToken()) {
            return $this->post($endpoint, $data, $newToken, $asForm, $files, $fileField);
        }

        if ($response->status() === 401) {
            $this->clearApiToken();
            abort(401, 'សម័យប្រើប្រាស់ផុតកំណត់។ សូមចូលឡើងវិញ។');
        }

        return $this->handleResponse($response);
    }


    protected function handleResponse($response)
    {
        if ($response->successful()) {
            return $response->json();
        }

        return [
            'error'   => true,
            'status'  => $response->status(),
            'message' => optional($response->json())['message'] ?? 'Request failed',
        ];
    }
}
