<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Target extends Model
{
    protected $fillable = [
        'parent',
        'project_id',
        'name',
        'start_at',
        'end_at',
        'sales',
        'revenue',
    ];

    protected $dates = [
        'deleted_at',
        'start_at',
        'end_at',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Targets') && !Auth::user()->can('Edit: Targets') && !Auth::user()->can('Delete: Targets'));
    }

    public function getStartAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setStartAtAttribute($value)
    {
        $this->attributes['start_at'] = $value ? Carbon::parse($value) : null;
    }

    public function getEndAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setEndAtAttribute($value)
    {
        $this->attributes['end_at'] = $value ? Carbon::parse($value) : null;
    }

    public function createRules($request, $api)
    {
        if ($api->model->parent) {
            $rules = [
                'name' => [
                    'required',
                    'max:255',
                    Rule::unique(str_plural($api->meta->model))->ignore($api->id)->where(function ($query) use ($api) {
                        return $query->where('parent', $api->model->parent ?: $api->id);
                    }),
                ],
                'start_at' => 'required|date',
                'end_at' => 'required|date',
                'sales' => 'required|numeric',
                'revenue' => 'required|numeric',
            ];
        } else {
            $rules = [
                'name' => [
                    'required',
                    'max:255',
                    Rule::unique(str_plural($api->meta->model))->ignore($api->id)->where(function ($query) use ($api) {
                        return $query->where('project_id', $api->model->_parent ? $api->model->_parent->id : ($api->model->project_id ?: $api->id))->whereNull('parent');
                    }),
                ],
            ];
        }

        return $rules;
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['parent'] = $api->model->id;
        $data['project_id'] = $api->model->_parent->id;

        return $data;
    }

    public function datatable($api)
    {
        $data = Datatable::format($this, 'date', 'd.m.Y', 'start_at', 'start');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'end_at', 'end');
        $data = Datatable::render($data, 'start', ['sort' => ['start_at' => 'timestamp']]);
        $data = Datatable::render($data, 'end', ['sort' => ['end_at' => 'timestamp']]);

        if (!$this->parent) {
            $data = Datatable::link($data, 'name', 'name', $api->path, true);
            $data = Datatable::sum($data, 'sales');
            $data = Datatable::sum($data, 'revenue', true);
        } else {
            $data = Datatable::price($data, 'revenue');
        }

        return Datatable::data($data, array_column($this->dColumns($api), 'id'))->first();
    }

    public function dColumns($api)
    {
        $columns = [
            [
                'id' => 'id',
                'checkbox' => true,
                'order' => false,
                'hidden' => $this->isNotInteractable(),
            ],
        ];

        if ($api->id) {
            array_push($columns, [
                'id' => 'name',
                'name' => trans('labels.name'),
            ], [
                'id' => 'start',
                'name' => trans('labels.dateFrom'),
                'render' =>  ['sort'],
            ], [
                'id' => 'end',
                'name' => trans('labels.dateTo'),
                'render' =>  ['sort'],
            ], [
                'id' => 'sales',
                'name' => trans('labels.sales'),
                'class' => 'text-right',
            ], [
                'id' => 'revenue',
                'name' => trans('labels.revenue'),
                'class' => 'text-right',
            ]);
        } else {
            array_push($columns, [
                'id' => 'name',
                'name' => trans('labels.year'),
            ], [
                'id' => 'sales',
                'name' => trans('labels.sales'),
                'class' => 'text-right',
            ], [
                'id' => 'revenue',
                'name' => trans('labels.revenue'),
                'class' => 'text-right',
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
        return [
            'create' => [
                'url' => Helper::route('api.create', $api->path),
                'class' => 'btn-success',
                'icon' => 'plus',
                'method' => 'get',
                'name' => ($api->id ? trans('buttons.create') : trans('buttons.addYear')),
                'visible' => Auth::user()->can('Create: Targets'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Targets'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Targets'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 0 : 1, $api->id ? 'asc' : 'desc'],
        ];
    }

    public function dData($api)
    {
        $data = $this->where('parent', $api->id ?: null)->where('project_id', $api->model->_parent->id)->get();

        if (!$api->id) {
            $data = Datatable::link($data, 'name', 'name', $api->path, true);
            $data = Datatable::sum($data, 'sales');
            $data = Datatable::sum($data, 'revenue', true);
        } else {
            $data = Datatable::price($data, 'revenue');
        }

        $data = Datatable::format($data, 'date', 'd.m.Y', 'start_at', 'start');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'end_at', 'end');
        $data = Datatable::render($data, 'start', ['sort' => ['start_at' => 'timestamp']]);
        $data = Datatable::render($data, 'end', ['sort' => ['end_at' => 'timestamp']]);

        return Datatable::data($data, array_column($this->dColumns($api), 'id'));
    }
}
