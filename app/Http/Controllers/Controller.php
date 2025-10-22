<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Services\ApiService;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * @var \App\Services\ApiService|null
     */
    protected $api = null;

    /**
     * Lazy-load ApiService only when needed.
     *
     * @return \App\Services\ApiService
     */
    protected function api(): ApiService
    {
        if (!$this->api) {
            $this->api = app(ApiService::class);
        }

        return $this->api;
    }
}
