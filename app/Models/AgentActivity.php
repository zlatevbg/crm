<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class AgentActivity extends Model
{
    use SoftDeletes;

    protected $table =  'agent_activity';

    protected $fillable = [
        'agent_id',
        'activity_id',
        'started_at',
        'finished_at',
        'description',
    ];

    protected $dates = [
        'deleted_at',
        'started_at',
        'finished_at',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Agent Activities') && !Auth::user()->can('Edit: Agent Activities') && !Auth::user()->can('Delete: Agent Activities'));
    }

    public function getStartedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setStartedAtAttribute($value)
    {
        $this->attributes['started_at'] = $value ? Carbon::parse($value) : null;
    }

    public function getFinishedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setFinishedAtAttribute($value)
    {
        $this->attributes['finished_at'] = $value ? Carbon::parse($value) : null;
    }

    public function selectActivities()
    {
        return Activity::where('parent', 1)->orderBy('name')->pluck('name', 'id');
    }

    public function createRules($request, $api)
    {
        return [
            'activity_id' => 'required|numeric|exists:activities,id',
            'started_at' => 'required|date',
            'finished_at' => 'present|nullable|date',
            'description' => 'present',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['agent_id'] = $api->model->_parent->id;

        return $data;
    }

    public function datatable($api)
    {
        $data = Datatable::nl2br($this, 'description');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'started_at', 'started');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'finished_at', 'finished');
        $data = Datatable::render($data, 'started', ['sort' => ['started_at' => 'timestamp']]);
        $data = Datatable::render($data, 'finished', ['sort' => ['finished_at' => 'timestamp']]);

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
                'id' => 'started',
                'name' => trans('labels.startedAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
            ],
            [
                'id' => 'finished',
                'name' => trans('labels.finishedAt'),
                'render' =>  ['sort'],
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
                'visible' => Auth::user()->can('Create: Agent Activities'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Agent Activities'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Agent Activities'),
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
        $data = $this->select($table . '.id', $table . '.started_at', $table . '.finished_at', $table . '.description', 'activities.name as activity')
            ->leftJoin('activities', $table . '.activity_id', '=', 'activities.id')
            ->where('agent_id', $api->model->_parent->id)
            ->orderBy($table . '.started_at', 'desc')
            ->get();

        $data = Datatable::nl2br($data, 'description');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'started_at', 'started');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'finished_at', 'finished');
        $data = Datatable::render($data, 'started', ['sort' => ['started_at' => 'timestamp']]);
        $data = Datatable::render($data, 'finished', ['sort' => ['finished_at' => 'timestamp']]);

        return Datatable::data($data, array_column($this->dColumns(), 'id'));
    }
}
