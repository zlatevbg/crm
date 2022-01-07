<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'agent_id',
        'project_id',
        'signed_at',
        'territory',
        'commission',
        'sub_commission',
    ];

    protected $dates = [
        'signed_at',
        'deleted_at',
    ];

    protected $table = 'agent_project';

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Agent Contracts') && !Auth::user()->can('Edit: Agent Contracts') && !Auth::user()->can('Delete: Agent Contracts'));
    }

    public function getSignedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setSignedAtAttribute($value)
    {
        $this->attributes['signed_at'] = $value ? Carbon::parse($value) : null;
    }

    public function setCommissionAttribute($value)
    {
        $this->attributes['commission'] = $value ?: 0;
    }

    public function setSubCommissionAttribute($value)
    {
        $this->attributes['sub_commission'] = $value ?: 0;
    }

    public function selectProject($api = null)
    {
        return Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, projects.id')->whereNotExists(function ($query) use ($api) {
            $query->from($this->table)->whereRaw($this->table . '.project_id = projects.id')->where($this->table . '.agent_id', $this->_parent->id)->whereNull($this->table . '.deleted_at');

            if ($api) {
                $query->where('projects.id', '!=', $api->model->project_id);
            }
        })->where('projects.status', 1)->whereIn('projects.id', Helper::project())->orderBy('projects')->pluck('projects', 'projects.id');
    }

    public function createRules($request, $api)
    {
        return [
            'project_id' => 'required|numeric|exists:projects,id',
            'signed_at' => 'present|nullable|date',
            'commission' => 'present|nullable|numeric|between:0,100',
            'sub_commission' => 'present|nullable|numeric|between:0,100',
            'territory' => 'present|max:255',
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
        $data = Datatable::format($this, 'date', 'd.m.Y', 'signed_at', 'signed');
        $data = Datatable::suffix($data, 'commission', ' %', 'float');
        $data = Datatable::suffix($data, 'sub_commission', ' %', 'float');

        $data->first()->project = Project::selectRaw('CONCAT(projects.name, ", ", projects.location, ", ", countries.name) as project')
            ->leftJoin('countries', 'countries.id', '=', 'projects.country_id')
            ->where('projects.id', $data->first()->project_id)
            ->value('project');

        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
    }

    public function doptions($api)
    {
        return [
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
                'id' => 'project',
                'name' => trans('labels.project'),
            ],
            [
                'id' => 'signed',
                'name' => trans('labels.signedAt'),
            ],
            [
                'id' => 'territory',
                'name' => trans('labels.territory'),
            ],
            [
                'id' => 'commission',
                'name' => trans('labels.commission'),
                'class' => 'text-right',
            ],
            [
                'id' => 'sub_commission',
                'name' => trans('labels.subCommission'),
                'class' => 'text-right',
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
                'visible' => Auth::user()->can('Create: Agent Contracts'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Agent Contracts'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Agent Contracts'),
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
        $data = $this->selectRaw($this->table . '.id, ' . $this->table . '.territory, ' . $this->table . '.commission, ' . $this->table . '.sub_commission, ' . $this->table . '.signed_at, CONCAT(projects.name, ", ", projects.location, ", ", countries.name) as project')
            ->leftJoin('projects', 'projects.id', '=', $this->table . '.project_id')
            ->leftJoin('countries', 'countries.id', '=', 'projects.country_id')
            ->where($this->table . '.agent_id', $api->model->_parent->id)
            ->orderBy($this->table . '.signed_at')
            ->get();

        $data = Datatable::format($data, 'date', 'd.m.Y', 'signed_at', 'signed');
        $data = Datatable::suffix($data, 'commission', ' %', 'float');
        $data = Datatable::suffix($data, 'sub_commission', ' %', 'float');

        return Datatable::data($data, array_column($this->dColumns(), 'id'));
    }
}
