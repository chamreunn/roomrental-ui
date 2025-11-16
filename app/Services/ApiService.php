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

    // Store ALL custom headers safely
    protected array $extraHeaders = [];

    public function __construct(string $overrideEnv = null)
    {
        $env = $overrideEnv ?? env('APP_ENV', 'local');
        $env = $env === 'local' ? 'local' : 'hosting';

        $this->baseUrl  = rtrim(config("api.$env.api"), '/');
        $this->verifySsl = config("api.$env.ssl", false);
    }

    /* -----------------------------------------------------------
     * Build URL
     * ----------------------------------------------------------- */
    protected function buildUrl(string $endpoint): string
    {
        return "{$this->baseUrl}/api/" . ltrim($endpoint, '/');
    }

    /* -----------------------------------------------------------
     * Build HTTP client
     * ----------------------------------------------------------- */
    protected function getHttpClient($token = null)
    {
        // base headers
        $headers = ['App_key' => config('custom.hrms_key')];

        $client = Http::withOptions([
            'verify' => $this->verifySsl,
        ])
            ->withHeaders($headers);

        if ($token) {
            $client = $client->withToken($token);
        }

        // APPLY all custom headers set via withHeaders()
        if (!empty($this->extraHeaders)) {
            $client = $client->withHeaders($this->extraHeaders);
        }

        return $client->timeout(15)->retry(3, 200);
    }

    /* -----------------------------------------------------------
     * Allow chaining headers (fixed)
     * ----------------------------------------------------------- */
    public function withHeaders(array $headers = [])
    {
        $clone = clone $this;
        $clone->extraHeaders = array_merge($this->extraHeaders, $headers);
        return $clone;
    }

    /* -----------------------------------------------------------
     * GET
     * ----------------------------------------------------------- */
    public function get(string $endpoint, array $query = [], $token = null, array $moreHeaders = [])
    {
        $url   = $this->buildUrl($endpoint);
        $token = $token ?? $this->getApiToken();

        try {
            $http = $this->getHttpClient($token);

            if (!empty($moreHeaders)) {
                $http = $http->withHeaders($moreHeaders);
            }

            $response = $http->get($url, $query);

            return $this->handleAuthAndResponse('get', func_get_args(), $response);
        } catch (ConnectionException $e) {
            return [
                'error' => true,
                'message' => 'Cannot connect to API.'
            ];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => 'API error: ' . $e->getMessage()
            ];
        }
    }

    /* -----------------------------------------------------------
     * POST (fixed)
     * ----------------------------------------------------------- */
    public function post(
        string $endpoint,
        array $data = [],
        $token = null,
        bool $asForm = false,
        $files = [],
        string $fileField = 'documents[]',
        array $moreHeaders = []
    ) {
        $url   = $this->buildUrl($endpoint);
        $token = $token ?? $this->getApiToken();

        $http = $this->getHttpClient($token);

        if (!empty($moreHeaders)) {
            $http = $http->withHeaders($moreHeaders);
        }

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

        // Normal post or multipart
        $response = (!empty($files) || $asForm)
            ? $http->asMultipart()->post($url, $data)
            : $http->post($url, $data);

        return $this->handleAuthAndResponse('post', func_get_args(), $response);
    }

    /* -----------------------------------------------------------
     * PATCH (fixed — real PATCH instead of PUT)
     * ----------------------------------------------------------- */
    public function patch(string $endpoint, array $data = [], $token = null, array $moreHeaders = [])
    {
        $url   = $this->buildUrl($endpoint);
        $token = $token ?? $this->getApiToken();

        $http = $this->getHttpClient($token);

        if (!empty($moreHeaders)) {
            $http = $http->withHeaders($moreHeaders);
        }

        $response = $http->patch($url, $data);

        return $this->handleAuthAndResponse('patch', func_get_args(), $response);
    }

    /* -----------------------------------------------------------
     * DELETE
     * ----------------------------------------------------------- */
    public function delete(string $endpoint, array $data = [], $token = null, array $moreHeaders = [])
    {
        $url   = $this->buildUrl($endpoint);
        $token = $token ?? $this->getApiToken();

        $http = $this->getHttpClient($token);

        if (!empty($moreHeaders)) {
            $http = $http->withHeaders($moreHeaders);
        }

        $response = $http->delete($url, $data);

        return $this->handleAuthAndResponse('delete', func_get_args(), $response);
    }

    /* -----------------------------------------------------------
     * Auto-refresh token and handle response
     * ----------------------------------------------------------- */
    protected function handleAuthAndResponse(string $method, array $args, Response $response)
    {
        // TOKEN EXPIRED?
        if ($response->status() === 401) {
            if ($newToken = $this->refreshApiToken()) {
                $args[2] = $newToken;
                return $this->$method(...$args);
            }

            $this->clearApiToken();
            abort(401, 'Session expired.');
        }

        return $this->handleResponse($response);
    }

    /* -----------------------------------------------------------
     * Universal Response Handler (NO MORE EXCEPTIONS)
     * ----------------------------------------------------------- */
    protected function handleResponse(Response $response)
    {
        // Success → return JSON
        if ($response->successful()) {
            return $response->json();
        }

        // Return clean error response (NO exceptions)
        $json = $response->json();

        return [
            'error'   => true,
            'status'  => $response->status(),
            'message' => $json['message'] ?? 'Request failed',
            'errors'  => $json['errors'] ?? null,
        ];
    }
}
