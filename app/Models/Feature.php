<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Feature extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent',
        'name',
        'score',
        'order',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Project Features') && !Auth::user()->can('Edit: Project Features') && !Auth::user()->can('Delete: Project Features'));
    }

    public function _parent()
    {
        return $this->belongsTo(Feature::class, 'parent');
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
            'score' => 'integer|between:0,' . ($api->model->parent ? $api->model->_parent->score : 100),
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['parent'] = $api->model->id;

        $order = $data['order'] ?? 0;
        $maxOrder = $this->where('parent', $api->model->id)->max('order') + 1;

        if (!$order || $order > $maxOrder) {
            $order = $maxOrder;
        } else { // re-order all higher order rows
            $this->where('parent', $api->model->id)->where('order', '>=', $order)->increment('order');
        }

        $data['order'] = $order;

        return $data;
    }

    public function restoreData($api, $request)
    {
        $model = $api->model->withTrashed()->where('parent', $api->id)->where('name', $request->input('name'))->first();
        if ($model) {
            $model->restore();
        }

        return $model;
    }

    public function postDestroy($api, $ids, $rows)
    {
        DB::statement('SET @pos := 0');
        DB::update('update ' . $this->getTable() . ' SET `order` = (SELECT @pos := @pos + 1) WHERE `parent` = ? ORDER BY `order`', [$api->model->id]);
    }

    public function datatable($api)
    {
        if (!$api->model->parent) {
            $data = Datatable::link($this, 'name', 'name', $api->meta->slug, true);
        } else {
            $data = $this;
        }

        return Datatable::data($data, array_column($this->dColumns($api), 'id'))->first();
    }

    public function dColumns($api)
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
                'id' => 'score',
                'name' => trans('labels.score'),
                'class' => 'text-center',
                'footer' => [
                    'function' => 'sum',
                ],
            ],
            [
                'id' => 'order',
                'name' => trans('labels.order'),
                'class' => 'vertical-center text-center' . (Auth::user()->can('Reorder') ? ' reorder' : ''),
            ],
        ];
    }

    public function doptions($api)
    {
        $options = [
            'dom' => 'tr',
            'parameters' => 'data-route="' . Helper::route('api.order', $api->path) . '"',
        ];

        if (!$api->model->id) {
            $options['footer'] = 'bg-dark text-white';
        }

        return $options;
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
                'visible' => Auth::user()->can('Create: Project Features'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Project Features'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Project Features'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 2 : 3, 'asc'],
        ];
    }

    public function dData($api)
    {
        if ($api->model->parent) {
            abort(404);
        }

        $data = $this->select(array_column($this->dColumns($api), 'id'))->where('parent', $api->model->id ?: null)->get();

        if (!$api->model->id) {
            $data = Datatable::link($data, 'name', 'name', $api->meta->slug, true);
        }

        return $data;
    }
}
