<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Status extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent',
        'name',
        'action',
        'order',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Statuses') && !Auth::user()->can('Edit: Statuses') && !Auth::user()->can('Delete: Statuses'));
    }

    public function selectAction()
    {
        return [
            'viewing' => trans('labels.actionViewing'),
            'deposit' => trans('labels.actionDeposit'),
            'promissory-payment' => trans('labels.actionPromissoryPayment'),
            'final-balance' => trans('labels.actionFinalBalance'),
            'one-time-payment' => trans('labels.actionOneTimePayment'),
            'reserve' => trans('labels.actionReserve'),
            'see' => trans('labels.actionSee'),
            'complete' => trans('labels.actionComplete'),
            'no-show' => trans('labels.actionNoShow'),
            'future-viewing' => trans('labels.actionFutureViewing'),
        ];
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
            'action' => 'nullable|in:viewing,deposit,promissory-payment,final-balance,one-time-payment,reserve,see,complete,no-show,future-viewing',
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
        $data = Datatable::status($this, ['default', 'conversion'], $api->path);
        $data = Datatable::trans($data, 'action', 'labels', 'action_');

        return Datatable::data($data, array_column($this->dColumns($api), 'id'))->first();
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

        if ($api->id) {
            array_push($columns, [
                'id' => 'action',
                'name' => trans('labels.mapToAction'),
                'order' => false,
            ]);

            array_push($columns, [
                'id' => 'default',
                'name' => trans('labels.default'),
                'order' => false,
                'class' => 'text-center',
            ]);

            $category = $api->model->parent ?: $api->id;
            if ($category == 2) {
                array_push($columns, [
                    'id' => 'conversion',
                    'name' => trans('labels.conversion'),
                    'order' => false,
                    'class' => 'text-center',
                ]);
            }

            array_push($columns, [
                'id' => 'order',
                'name' => trans('labels.order'),
                'class' => 'vertical-center text-center' . (Auth::user()->can('Reorder') ? ' reorder' : ''),
            ]);
        }

        return $columns;
    }

    public function doptions($api)
    {
        return [
            'dom' => 'tr',
            'parameters' => 'data-route="' . Helper::route('api.order', $api->path) . '"',
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
                    'visible' => Auth::user()->can('Create: Statuses'),
                ],
                'edit' => [
                    'url' => Helper::route('api.edit', $api->meta->slug),
                    'parameters' => 'disabled data-disabled="1" data-append-id',
                    'class' => 'btn-warning',
                    'icon' => 'edit',
                    'method' => 'get',
                    'name' => trans('buttons.edit'),
                    'visible' => Auth::user()->can('Edit: Statuses'),
                ],
                'delete' => [
                    'url' => Helper::route('api.delete', $api->meta->slug),
                    'parameters' => 'disabled data-disabled',
                    'class' => 'btn-danger',
                    'icon' => 'trash',
                    'method' => 'get',
                    'name' => trans('buttons.delete'),
                    'visible' => Auth::user()->can('Delete: Statuses'),
                ],
            ];
        }

        return [];
    }

    public function dOrder($api)
    {
        return [
            [$api->model->id ? ($api->id ? ($api->id == 2 ? ($this->isNotInteractable() ? 4 : 5) : ($this->isNotInteractable() ? 3 : 4)) : ($this->isNotInteractable() ? 0 : 1)) : ($api->id ? ($api->id == 2 ? ($this->isNotInteractable() ? 3 : 4) : ($this->isNotInteractable() ? 2 : 3)) : 0), 'asc'],
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

        $data = Datatable::status($data, ['default', 'conversion'], $api->path);
        $data = Datatable::trans($data, 'action', 'labels', 'action_');

        return $data;
    }
}
