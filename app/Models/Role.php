<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Support\Facades\Auth;

class Role extends Model
{
    protected $fillable = [
        'name',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Roles') && !Auth::user()->can('Edit: Roles') && !Auth::user()->can('Delete: Roles'));
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, config('permission.table_names.role_has_permissions'));
    }

    public function selectPermissions()
    {
        return Permission::select('name', 'id')->orderBy('name')->get()->pluck('name', 'id');
    }

    public function createRules($request, $api)
    {
        return [
            'name' => 'required|max:255|unique:' . str_plural($api->meta->model),
            'permissions' => 'nullable|array',
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
        $role = SpatieRole::create($request->only('name'));
        $role->syncPermissions($request->input('permissions'));

        $model = $this->findOrFail($role->id);

        return $model;
    }

    public function updateModel($api, $request)
    {
        $role = SpatieRole::findOrFail($api->id);
        $role->syncPermissions($request->input('permissions'));
        return $role->update($request->only('name'));
    }

    public function datatable($api)
    {
        $model = $this->findOrFail($this->id);
        $model = collect([$model]);
        $model->first()->permissions = Permission::selectRaw('COUNT(' . config('permission.table_names.permissions') . '.id) as permissions')
            ->leftJoin(config('permission.table_names.role_has_permissions'), config('permission.table_names.role_has_permissions') . '.permission_id', '=', config('permission.table_names.permissions') . '.id')
            ->where(config('permission.table_names.role_has_permissions') . '.role_id', $model->first()->id)
            ->value('permissions');

        return Datatable::data($model, array_column($this->dColumns($api), 'id'))->first();
    }

    public function dColumns()
    {
        $total = Permission::count();

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
                'class' => 'vertical-center',
            ],
            [
                'id' => 'permissions',
                'name' => trans('labels.permissions') . ' (' . $total . ')',
                'class' => 'vertical-center cell-max-15',
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
                'visible' => Auth::user()->can('Create: Roles'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Roles'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Roles'),
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
        $table = str_plural($api->meta->model);
        $roles = $this->selectRaw($table . '.id, ' . $table . '.name, COUNT(' . config('permission.table_names.permissions') . '.id) as permissions')
            ->leftJoin(config('permission.table_names.role_has_permissions'), config('permission.table_names.role_has_permissions') . '.role_id', '=', $table . '.id')
            ->leftJoin(config('permission.table_names.permissions'), config('permission.table_names.permissions') . '.id', '=', config('permission.table_names.role_has_permissions') . '.permission_id')
            ->groupBy($table . '.id')
            ->orderBy(config('permission.table_names.permissions') . '.name')
            ->get();

        return Datatable::data($roles, array_column($this->dColumns($api), 'id'));
    }
}
