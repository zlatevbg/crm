<?php

namespace App\Services;

use App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Lang;
use App\Models\Domain as DomainModel;

class Domain
{
    protected $domains = [];
    protected $domain;

    public function __construct()
    {
        if (Schema::hasTable('domains')) {
            $domains = DomainModel::all()->keyBy('domain');
            $this->domains = $domains;
            $this->domain = $this->domain();

            foreach ($domains as $key => $value) {
                Lang::addNamespace($key, resource_path() . DIRECTORY_SEPARATOR . 'lang');
            }
        }
    }

    public function current()
    {
        return App::getDomain() ?: env('APP_SUBDOMAIN');
    }

    public function get($domain = null)
    {
        $domain = $domain ?: $this->current();
        return $this->domains[$domain] ?? null;
    }

    public function domain($domain = null)
    {
        return optional($this->get($domain))->domain;
    }

    public function name($domain = null)
    {
        return optional($this->get($domain))->name;
    }

    public function id($domain = null)
    {
        return optional($this->get($domain))->id;
    }

    public function guest($domain = null)
    {
        return optional($this->get($domain))->guest;
    }

    public function auth($domain = null)
    {
        return optional($this->get($domain))->auth;
    }
}
