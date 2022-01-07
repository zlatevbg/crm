<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Support\Facades\Auth;

class Permission extends Model
{
    protected $fillable = [
        'name',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Permissions') && !Auth::user()->can('Edit: Permissions') && !Auth::user()->can('Delete: Permissions'));
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

    public function storeModel($request)
    {
        $permission = SpatiePermission::create($request->only('name'));

        $model = $this->findOrFail($permission->id);

        return $model;
    }

    public function updateModel($api, $request)
    {
        $permission = SpatiePermission::findOrFail($api->id);
        return $permission->update($request->only('name'));
    }

    public function datatable($api)
    {
        $model = $this->findOrFail($this->id);
        return collect($model)->only(array_column($this->dColumns(), 'id'));
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
                'visible' => Auth::user()->can('Create: Permissions'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Permissions'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Permissions'),
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
