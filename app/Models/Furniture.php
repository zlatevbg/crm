<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Furniture extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'project_id',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Furniture') && !Auth::user()->can('Edit: Furniture') && !Auth::user()->can('Delete: Furniture'));
    }

    public function createRules($request, $api)
    {
        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique(str_plural($api->meta->model))->ignore($api->id)->where(function ($query) use ($api) {
                    return $query->where('project_id', $api->model->_parent ? $api->model->_parent->id : ($api->model->project_id ?: $api->id))->whereNull('deleted_at');
                }),
            ],
            'price' => 'present|nullable|numeric|between:0,99999.99',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['project_id'] = $api->model->_parent->id;

        return $data;
    }

    public function datatable($api)
    {
        $data = Datatable::price($this, 'price');
        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
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
            [
                'id' => 'price',
                'name' => trans('labels.price'),
                'class' => 'text-right',
            ],
        ];
    }

    public function dButtons($api)
    {
        return [
            'create' => [
                'url' => Helper::route('api.create', $api->path),
                'class' => 'btn-success',
                'icon' => 'plus',
                'method' => 'get',
                'name' => trans('buttons.create'),
                'visible' => Auth::user()->can('Create: Furniture'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Furniture'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Furniture'),
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
        $data = $this->select(array_column($this->dColumns(), 'id'))->where('project_id', $api->model->_parent->id)->get();

        $data = Datatable::price($data, 'price');

        return $data;
    }
}
