<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Visit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'visited_at',
        'name',
        'description',
    ];

    protected $dates = [
        'visited_at',
        'deleted_at',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Visits') && !Auth::user()->can('Edit: Visits') && !Auth::user()->can('Delete: Visits'));
    }

    public function getVisitedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setVisitedAtAttribute($value)
    {
        $this->attributes['visited_at'] = $value ? Carbon::parse($value) : null;
    }

    public function createRules($request, $api)
    {
        return [
            'project_id' => 'required|numeric|exists:projects,id',
            'visited_at' => 'present|nullable|date_format:"d.m.Y"|before_or_equal:' . date('d.m.Y'),
            'name' => 'required|max:255',
            'description' => 'present',
        ];
    }

    public function datatable($api)
    {
        $data = Datatable::format($this, 'date', 'd.m.Y', 'visited_at', 'visited');
        $data = Datatable::render($data, 'visited', ['sort' => ['visited_at' => 'timestamp']]);

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
                'id' => 'visited',
                'name' => trans('labels.visitedAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
            ],
            [
                'id' => 'name',
                'name' => trans('labels.name'),
                'class' => 'vertical-center',
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
                'visible' => Auth::user()->can('Create: Visits'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Visits'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Visits'),
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
        $visits = $this->selectRaw($table . '.id, ' . $table . '.name, ' . $table . '.description, ' . $table . '.visited_at')
            ->leftJoin('projects', 'projects.id', '=', $table . '.project_id')
            ->where($table . '.project_id', $api->model->_parent->id);

        $visits = $visits->groupBy($table . '.id')
            ->orderBy($table . '.visited_at', 'desc')
            ->get();

        $data = Datatable::format($visits, 'date', 'd.m.Y', 'visited_at', 'visited');
        $data = Datatable::render($data, 'visited', ['sort' => ['visited_at' => 'timestamp']]);
        $data = Datatable::popover($data, 'status');

        return Datatable::data($data, array_column($this->dColumns($api), 'id'));
    }
}
