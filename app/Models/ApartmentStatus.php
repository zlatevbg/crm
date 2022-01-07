<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ApartmentStatus extends Model
{
    use SoftDeletes;

    protected $table =  'apartment_status';

    protected $fillable = [
        'apartment_id',
        'status_id',
        'user_id',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public $_parent;

    public function selectStatus()
    {
        return Status::where('parent', 1)->orderBy('name')->pluck('name', 'id');
    }

    public function createRules($request, $api)
    {
        return [
            'status_id' => 'required|numeric',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['apartment_id'] = $api->model->_parent->id;
        $data['user_id'] = Auth::user()->id;

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
        $data = Datatable::render($data, 'created', ['sort' => ['created_at' => 'timestamp']]);

        $data->first()->user = User::selectRaw('CONCAT(users.first_name, " ", users.last_name) as user')->where('id', $data->first()->user_id)->value('user');
        $data->first()->status = Status::where('id', $data->first()->status_id)->value('name');

        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
    }

    public function dColumns()
    {
        return [
            /*[
                'id' => 'id',
                'checkbox' => true,
                'order' => false,
                'hidden' => (!Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Apartment Status') && !Auth::user()->can('Edit: Apartment Status') && !Auth::user()->can('Delete: Apartment Status'))),
            ],*/
            [
                'id' => 'created',
                'name' => trans('labels.createdAt'),
                'render' =>  ['sort'],
            ],
            [
                'id' => 'user',
                'name' => trans('labels.modifiedBy'),
            ],
            [
                'id' => 'status',
                'name' => trans('labels.status'),
            ],
        ];
    }

    public function dButtons($api)
    {
        return [/*
            'create' => [
                'url' => Helper::route('api.create', $api->path),
                'class' => 'btn-success',
                'icon' => 'sync-alt',
                'method' => 'get',
                'name' => trans('buttons.change'),
                'visible' => Auth::user()->can('Create: Apartment Status'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Apartment Status'),
            ],*/
        ];
    }

    public function dOrder($api)
    {
        return [
            [0, 'desc'],
        ];
    }

    public function dData($api)
    {
        $table = $api->meta->model;
        $data = $this->withTrashed()->selectRaw($table . '.id, ' . $table . '.created_at, CONCAT(users.first_name, " ", users.last_name) as user, statuses.name as status')
            ->leftJoin('users', $table . '.user_id', '=', 'users.id')
            ->leftJoin('statuses', $table . '.status_id', '=', 'statuses.id')
            ->where('apartment_id', $api->model->_parent->id)
            ->orderBy($table . '.created_at', 'desc')
            ->get();

        $data = Datatable::format($data, 'date', 'd.m.Y', 'created_at', 'created');
        $data = Datatable::render($data, 'created', ['sort' => ['created_at' => 'timestamp']]);

        return Datatable::data($data, array_column($this->dColumns(), 'id'));
    }
}
