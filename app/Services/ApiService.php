<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Traits\HandlesApiToken;
use Illuminate\Http\Client\ConnectionException;

class ApiService
{
    use HandlesApiToken;

    protected $baseUrl;
    protected $verifySsl;
    protected $extraHeaders = []; // ✅ store headers added via withHeaders()

    public function __construct(string $overrideEnv = null)
    {
        $env = $overrideEnv ?? env('APP_ENV', 'local');
        $env = $env === 'local' ? 'local' : 'hosting';

        $this->baseUrl = rtrim(config("api.$env.api"), '/');
        $this->verifySsl = config("api.$env.ssl", false);
    }

    /** ----------------------------------------------------------
     * Core helpers
     * ---------------------------------------------------------- */
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

        // ✅ Apply any custom headers from withHeaders()
        if (!empty($this->extraHeaders)) {
            $client = $client->withHeaders($this->extraHeaders);
        }

        return $client->timeout(15)->retry(3, 200);
    }

    /** ----------------------------------------------------------
     * Fluent Header Setter
     * ---------------------------------------------------------- */
    public function withHeaders(array $headers = [])
    {
        $clone = clone $this;
        $clone->extraHeaders = $headers;
        return $clone;
    }

    /** ----------------------------------------------------------
     * GET
     * ---------------------------------------------------------- */
    public function get(string $endpoint, array $query = [], $token = null, array $extraHeaders = [])
    {
        $url = $this->buildUrl($endpoint);
        $token = $token ?? $this->getApiToken();

        try {
            $http = $this->getHttpClient($token);

            // ✅ Merge headers from parameter + withHeaders()
            if (!empty($extraHeaders)) {
                $http = $http->withHeaders($extraHeaders);
            }

            $response = $http->get($url, $query);
            return $this->handleAuthAndResponse('get', func_get_args(), $response);
        } catch (ConnectionException $e) {
            return ['error' => true, 'message' => 'មិនអាចភ្ជាប់ទៅកាន់ API បានទេ។ សូមព្យាយាមម្ដងទៀត។'];
        } catch (\Exception $e) {
            return ['error' => true, 'message' => 'មានបញ្ហាក្នុងការទាញយកទិន្នន័យ API៖ ' . $e->getMessage()];
        }
    }
    /** ----------------------------------------------------------
     * POST
     * ---------------------------------------------------------- */
    public function post(
        string $endpoint,
        array $data = [],
        $token = null,
        bool $asForm = false,
        $files = [],
        string $fileField = 'documents[]',
        array $extraHeaders = []
    ) {
        $url = $this->buildUrl($endpoint);
        $token = $token ?? $this->getApiToken();
        $http = $this->getHttpClient($token);

        // Merge headers from parameter + withHeaders()
        if (!empty($extraHeaders)) {
            $http = $http->withHeaders($extraHeaders);
        }

        // Attach files if provided
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

        $response = (!empty($files) || $asForm)
            ? $http->asMultipart()->post($url, $data)
            : $http->post($url, $data);

        return $this->handleAuthAndResponse('post', func_get_args(), $response);
    }

    /** ----------------------------------------------------------
     * PUT
     * ---------------------------------------------------------- */
    public function put(string $endpoint, array $data = [], $token = null, array $extraHeaders = [])
    {
        $url = $this->buildUrl($endpoint);
        $token = $token ?? $this->getApiToken();

        $http = $this->getHttpClient($token);
        if (!empty($extraHeaders)) {
            $http = $http->withHeaders($extraHeaders);
        }

        $response = $http->put($url, $data);
        return $this->handleAuthAndResponse('put', func_get_args(), $response);
    }

    /** ----------------------------------------------------------
     * DELETE
     * ---------------------------------------------------------- */
    public function delete(string $endpoint, array $data = [], $token = null, array $extraHeaders = [])
    {
        $url = $this->buildUrl($endpoint);
        $token = $token ?? $this->getApiToken();

        $http = $this->getHttpClient($token);
        if (!empty($extraHeaders)) {
            $http = $http->withHeaders($extraHeaders);
        }

        $response = $http->delete($url, $data);
        return $this->handleAuthAndResponse('delete', func_get_args(), $response);
    }

    /** ----------------------------------------------------------
     * Helper wrappers for headers
     * ---------------------------------------------------------- */
    public function postWithHeaders(string $endpoint, array $data = [], array $headers = [], $token = null)
    {
        return $this->post($endpoint, $data, $token, false, [], 'documents[]', $headers);
    }

    public function putWithHeaders(string $endpoint, array $data = [], array $headers = [], $token = null)
    {
        return $this->put($endpoint, $data, $token, $headers);
    }

    public function deleteWithHeaders(string $endpoint, array $data = [], array $headers = [], $token = null)
    {
        return $this->delete($endpoint, $data, $token, $headers);
    }

    /** ----------------------------------------------------------
     * Centralized 401 handling
     * ---------------------------------------------------------- */
    protected function handleAuthAndResponse(string $method, array $args, Response $response)
    {
        if ($response->status() === 401 && $newToken = $this->refreshApiToken()) {
            $args[2] = $newToken; // replace token argument
            return $this->$method(...$args);
        }

        if ($response->status() === 401) {
            $this->clearApiToken();
            abort(401, 'សម័យប្រើប្រាស់ផុតកំណត់។ សូមចូលឡើងវិញ។');
        }

        return $this->handleResponse($response);
    }

    /** ----------------------------------------------------------
     * Unified response handler
     * ---------------------------------------------------------- */
    protected function handleResponse(Response $response)
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
