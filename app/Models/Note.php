<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'model_id',
        'model',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Notes') && !Auth::user()->can('Edit: Notes') && !Auth::user()->can('Delete: Notes'));
    }

    public function createRules($request, $api)
    {
        return [
            'title' => 'required_without:description|max:255',
            'description' => 'required_without:title',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        $data['model_id'] = $api->model->_parent->id;
        $data['model'] = $api->meta->_parent->model;

        return $data;
    }

    public function updateData($request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;

        return $data;
    }

    public function datatable($api)
    {
        $data = Datatable::format($this, 'date', 'd.m.Y', 'created_at', 'created');
        $data = Datatable::nl2br($data, 'description');

        $data->first()->user = User::selectRaw('CONCAT(first_name, " ", last_name) as user')->where('id', $data->first()->user_id)->value('user');

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
                'id' => 'created',
                'name' => trans('labels.createdAt'),
            ],
            [
                'id' => 'user',
                'name' => trans('labels.modifiedBy'),
            ],
            [
                'id' => 'title',
                'name' => trans('labels.title'),
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
                'visible' => Auth::user()->can('Create: Notes'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Notes'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Notes'),
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
        $data = $this->selectRaw($table . '.id, ' . $table . '.title, ' . $table . '.description, ' . $table . '.created_at, CONCAT(users.first_name, " ", COALESCE(users.last_name, "")) as user')
            ->leftJoin('users', $table . '.user_id', '=', 'users.id')
            ->where($table . '.model_id', $api->model->_parent->id)
            ->where($table . '.model', $api->meta->_parent->model)
            ->orderBy($table . '.created_at', 'desc')
            ->get();

        $data = Datatable::format($data, 'date', 'd.m.Y', 'created_at', 'created');
        $data = Datatable::nl2br($data, 'description');

        return Datatable::data($data, array_column($this->dColumns(), 'id'));
    }
}
