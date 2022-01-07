<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class InvestorActivity extends Model
{
    use SoftDeletes;

    protected $table =  'investor_activity';

    protected $fillable = [
        'investor_id',
        'activity_id',
        'description',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Investor Activities') && !Auth::user()->can('Edit: Investor Activities') && !Auth::user()->can('Delete: Investor Activities'));
    }

    public function selectActivities()
    {
        return Activity::where('parent', 2)->orderBy('name')->pluck('name', 'id');
    }

    public function createRules($request, $api)
    {
        return [
            'activity_id' => 'required|numeric|exists:activities,id',
            'description' => 'present',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['investor_id'] = $api->model->_parent->id;

        return $data;
    }

    public function datatable($api)
    {
        $data = Datatable::nl2br($this, 'description');

        $data->first()->activity = Activity::where('id', $data->first()->activity_id)->value('name');

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
                'id' => 'activity',
                'name' => trans('labels.activity'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'description',
                'name' => trans('labels.description'),
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
                'visible' => Auth::user()->can('Create: Investor Activities'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Investor Activities'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Investor Activities'),
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
        $table = $api->meta->model;
        $data = $this->select($table . '.id', $table . '.description', 'activities.name as activity')
            ->leftJoin('activities', $table . '.activity_id', '=', 'activities.id')
            ->where('investor_id', $api->model->_parent->id)
            ->orderBy($table . '.id', 'desc')
            ->get();

        $data = Datatable::nl2br($data, 'description');

        return Datatable::data($data, array_column($this->dColumns(), 'id'));
    }
}
