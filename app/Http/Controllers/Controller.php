<?php

namespace App\Http\Controllers;

use App\Utils\Util;
use App\Enum\Status;
use App\Services\ApiService;
use App\Enum\AbilitiesStatus;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
     * @var \App\Enum\Status|null
     */
    protected $status = null;

    /**
     * @var \App\Utils\Util|null
     */
    protected $util = null;

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

    /**
     * Lazy-load Status only when needed.
     *
     * @return \App\Enum\Status
     */
    protected function Status(): Status
    {
        if (!$this->status) {
            $this->status = app(Status::class);
        }

        return $this->status;
    }

    /**
     * Lazy-load Status only when needed.
     *
     * @return \App\Utils\Util
     */
    protected function Util(): Util
    {
        if (!$this->util) {
            $this->util = app(Util::class);
        }

        return $this->util;
    }
}
