<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use Illuminate\Support\Facades\Auth;

class Domain extends Model
{
    protected $fillable = [
        'domain',
        'name',
        'auth',
        'guest',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Domains') && !Auth::user()->can('Edit: Domains') && !Auth::user()->can('Delete: Domains'));
    }

    public function createRules($request, $api)
    {
        return [
            'domain' => 'required|max:255|unique:' . str_plural($api->meta->model),
            'name' => 'required|max:255',
            'auth' => 'required|max:255',
            'guest' => 'required|max:255',
        ];
    }

    public function updateRules($request, $api)
    {
        $rules = $this->createRules($request, $api);
        $rules['domain'] .= ',domain,' . $api->id;

        return $rules;
    }

    public function datatable($api)
    {
        return collect($this)->only(array_column($this->dColumns(), 'id'));
    }

    public function doptions($api)
    {
        return [
            'dom' => 'tr',
        ];
    }

    public function dColumns()
    {
        return [
            [
                'id' => 'id',
                'checkbox' => true,
                'order' => false,
                'hidden' => $this->isNotInteractable(),
            ],
            [
                'id' => 'domain',
                'name' => trans('labels.domain'),
            ],
            [
                'id' => 'name',
                'name' => trans('labels.name'),
                'order' => false,
            ],
            [
                'id' => 'auth',
                'name' => trans('labels.authRoute'),
                'order' => false,
            ],
            [
                'id' => 'guest',
                'name' => trans('labels.guestRoute'),
                'order' => false,
            ],
        ];
    }

    public function dButtons($api)
    {
        return [
            'create' => [
                'url' => Helper::route('api.create', $api->meta->slug),
                'class' => 'btn-success',
                'icon' => 'plus',
                'method' => 'get',
                'name' => trans('buttons.create'),
                'visible' => Auth::user()->can('Create: Domains'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Domains'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Domains'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 0 : 1, 'asc'],
        ];
    }

    public function dData($api)
    {
        return $this->select(array_column($this->dColumns(), 'id'))->get();
    }
}
