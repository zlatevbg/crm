<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Activity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent',
        'name',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Activities') && !Auth::user()->can('Edit: Activities') && !Auth::user()->can('Delete: Activities'));
    }

    public function createRules($request, $api)
    {
        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique(str_plural($api->meta->model))->ignore($api->id)->where(function ($query) use ($api) {
                    return $query->where('parent', $api->model->parent ?: $api->id)->whereNull('deleted_at');
                }),
            ],
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['parent'] = $api->model->id;

        return $data;
    }

    public function datatable($api)
    {
        return collect($this)->only(array_column($this->dColumns($api), 'id'));
    }

    public function dColumns($api)
    {
        $columns = [
            [
                'id' => 'name',
                'name' => trans('labels.name'),
            ],
        ];

        if ($api->model->id) {
            array_unshift($columns, [
                'id' => 'id',
                'checkbox' => true,
                'order' => false,
                'hidden' => $this->isNotInteractable(),
            ]);
        }

        return $columns;
    }

    public function doptions($api)
    {
        return [
            'dom' => 'tr',
        ];
    }

    public function dButtons($api)
    {
        if ($api->model->id) {
            return [
                'create' => [
                    'url' => Helper::route('api.create', $api->path),
                    'class' => 'btn-success',
                    'icon' => 'plus',
                    'method' => 'get',
                    'name' => trans('buttons.create'),
                    'visible' => Auth::user()->can('Create: Activities'),
                ],
                'edit' => [
                    'url' => Helper::route('api.edit', $api->meta->slug),
                    'parameters' => 'disabled data-disabled="1" data-append-id',
                    'class' => 'btn-warning',
                    'icon' => 'edit',
                    'method' => 'get',
                    'name' => trans('buttons.edit'),
                    'visible' => Auth::user()->can('Edit: Activities'),
                ],
                'delete' => [
                    'url' => Helper::route('api.delete', $api->meta->slug),
                    'parameters' => 'disabled data-disabled',
                    'class' => 'btn-danger',
                    'icon' => 'trash',
                    'method' => 'get',
                    'name' => trans('buttons.delete'),
                    'visible' => Auth::user()->can('Delete: Activities'),
                ],
            ];
        }

        return [];
    }

    public function dOrder($api)
    {
        return [
            [$api->model->id ? ($this->isNotInteractable() ? 0 : 1) : 0, 'asc'],
        ];
    }

    public function dData($api)
    {
        if ($api->model->parent) {
            abort(404);
        }

        $data = $this->select(array_merge(array_column($this->dColumns($api), 'id'), ['id']))->where('parent', $api->model->id ?: null)->get();

        if (!$api->model->id) {
            $data = Datatable::link($data, 'name', 'name', $api->meta->slug, true);
        }

        return $data;
    }
}
