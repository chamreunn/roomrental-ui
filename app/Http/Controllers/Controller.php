<?php

namespace App\Http\Controllers;

use App\Enum\AbilitiesStatus;
use App\Services\ApiService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * @var \App\Services\ApiService|null
     */
    protected $api = null;

    /**
     * @var \App\Enum\AbilitiesStatus|null
     */
    protected $ability = null;

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

    /**
     * Lazy-load AbilitiesStatus only when needed.
     *
     * @return \App\Enum\AbilitiesStatus
     */
    protected function abilitiesStatus(): AbilitiesStatus
    {
        if (!$this->ability) {
            $this->ability = app(AbilitiesStatus::class);
        }

        return $this->ability;
    }
}
