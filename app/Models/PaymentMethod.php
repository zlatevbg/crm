<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Payment Methods') && !Auth::user()->can('Edit: Payment Methods') && !Auth::user()->can('Delete: Payment Methods'));
    }

    public function createRules($request, $api)
    {
        return [
            'name' => 'required|max:255|unique:' . str_plural($api->meta->model),
        ];
    }

    public function updateRules($request, $api)
    {
        $rules = $this->createRules($request, $api);
        $rules['name'] .= ',name,' . $api->id;

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
                'id' => 'name',
                'name' => trans('labels.name'),
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
                'visible' => Auth::user()->can('Create: Payment Methods'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Payment Methods'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Payment Methods'),
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
