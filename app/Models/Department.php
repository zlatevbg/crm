<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'order',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Departments') && !Auth::user()->can('Edit: Departments') && !Auth::user()->can('Delete: Departments'));
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

    public function storeData($api, $request)
    {
        $data = $request->all();

        $order = $data['order'] ?? 0;
        $maxOrder = $this->max('order') + 1;

        if (!$order || $order > $maxOrder) {
            $order = $maxOrder;
        } else { // re-order all higher order rows
            $this->where('order', '>=', $order)->increment('order');
        }

        $data['order'] = $order;

        return $data;
    }

    public function postDestroy($api, $ids, $rows)
    {
        DB::statement('SET @pos := 0');
        DB::update('update ' . $this->getTable() . ' SET `order` = (SELECT @pos := @pos + 1) ORDER BY `order`');
    }

    public function datatable($api)
    {
        return collect($this)->only(array_column($this->dColumns(), 'id'));
    }

    public function doptions($api)
    {
        return [
            'parameters' => 'data-route="' . Helper::route('api.order', $api->path) . '"',
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
            [
                'id' => 'order',
                'name' => trans('labels.order'),
                'class' => 'vertical-center text-center' . (Auth::user()->can('Reorder') ? ' reorder' : ''),
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
                'visible' => Auth::user()->can('Create: Departments'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Departments'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Departments'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 1 : 2, 'asc'],
        ];
    }

    public function dData($api)
    {
        return $this->select(array_column($this->dColumns(), 'id'))->get();
    }
}
