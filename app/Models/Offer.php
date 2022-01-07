<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'offered_at',
        'price',
        'description',
    ];

    protected $dates = [
        'offered_at',
        'deleted_at',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Offers') && !Auth::user()->can('Edit: Offers') && !Auth::user()->can('Delete: Offers'));
    }

    public function getOfferedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setOfferedAtAttribute($value)
    {
        $this->attributes['offered_at'] = $value ? Carbon::parse($value) : null;
    }

    public function createRules($request, $api)
    {
        return [
            'project_id' => 'required|numeric|exists:projects,id',
            'offered_at' => 'present|nullable|date_format:"d.m.Y"|before_or_equal:' . date('d.m.Y'),
            'price' => 'required|numeric|between:0,999999999.99',
            'description' => 'present',
        ];
    }

    public function datatable($api)
    {
        $data = Datatable::format($this, 'date', 'd.m.Y', 'offered_at', 'offered');
        $data = Datatable::render($data, 'offered', ['sort' => ['offered_at' => 'timestamp']]);
        $data = Datatable::price($data, 'price');
        $data = Datatable::popover($data, 'status');

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
            [
                'id' => 'offered',
                'name' => trans('labels.offeredAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
            ],
            [
                'id' => 'price',
                'name' => trans('labels.price'),
                'class' => 'vertical-center text-right',
            ],
            [
                'id' => 'status',
                'name' => trans('labels.status'),
                'class' => 'vertical-center text-center popovers',
            ],
        ];

        return $columns;
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
                'visible' => Auth::user()->can('Create: Offers'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Offers'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Offers'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 0 : 1, 'desc'],
        ];
    }

    public function dData($api)
    {
        $table = str_plural($api->meta->model);
        $visits = $this->selectRaw($table . '.id, ' . $table . '.price, ' . $table . '.description, ' . $table . '.offered_at')
            ->leftJoin('projects', 'projects.id', '=', $table . '.project_id')
            ->where($table . '.project_id', $api->model->_parent->id);

        $visits = $visits->groupBy($table . '.id')
            ->orderBy($table . '.offered_at', 'desc')
            ->get();

        $data = Datatable::format($visits, 'date', 'd.m.Y', 'offered_at', 'offered');
        $data = Datatable::render($data, 'offered', ['sort' => ['offered_at' => 'timestamp']]);
        $data = Datatable::price($data, 'price');
        $data = Datatable::popover($data, 'status');

        return Datatable::data($data, array_column($this->dColumns($api), 'id'));
    }
}
