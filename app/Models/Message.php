<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_at',
        'type',
        'message',
        'is_completed',
        'user_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'model_id',
        'model',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Messages') && !Auth::user()->can('Edit: Messages') && !Auth::user()->can('Delete: Messages'));
    }

    public function selectMessageType()
    {
        return [
            'new' => trans('labels.new'),
            'reply' => trans('labels.reply'),
        ];
    }

    public function selectHistory()
    {
        return [
            0 => trans('labels.no'),
            1 => trans('labels.yes'),
        ];
    }

    public function selectTemplate()
    {
        return [
            'mespil.ie' => trans('labels.wwwMespilIe'),
            'portugal-golden-visa.pt' => trans('labels.wwwPortugalGoldenVisaPt'),
        ];
    }

    public function selectUsers()
    {
        return User::select('first_name', 'last_name', 'id')->orderBy('first_name')->orderBy('last_name')->get()->pluck('name', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = $value ? Carbon::parse($value)->setTime(date('H'), date('i'), date('s')) : null;
    }

    public function createRules($request, $api)
    {
        return [
            'type' => 'required|in:new,reply',
            'created_at' => 'present|nullable|date',
            'message' => 'required',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['created_at'] = $data['created_at'] ?? Carbon::now();
        $data['user_id'] = $data['from'] ?? null;
        $data['created_by'] = Auth::user()->id;
        $data['is_completed'] = $request->has('complete') ? 1 : 0;
        $data['model_id'] = $api->model->_parent->id;
        $data['model'] = $api->meta->_parent->model;

        $data['message'] = Helper::linkUrlsInTrustedHtml($data['message']);

        return $data;
    }

    public function updateData($request)
    {
        $data = $request->all();

        if ($this->created_at == $data['created_at']) {
            unset($data['created_at']);
        } else {
            $data['created_at'] = $data['created_at'] ?? Carbon::now();
        }

        $data['user_id'] = $data['from'] ?? null;
        $data['updated_by'] = Auth::user()->id;
        $data['is_completed'] = $request->has('complete') ? 1 : 0;

        $data['message'] = Helper::linkUrlsInTrustedHtml($data['message']);

        return $data;
    }

    public function postDestroy($api, $ids, $rows)
    {
        $this->withTrashed()->whereIn('id', $ids)->update(['deleted_by' => Auth::user()->id]);
    }

    public function datatable($api)
    {
        $data = Datatable::format($this, 'date', 'd.m.Y', 'created_at', 'created');
        $data = Datatable::render($data, 'created', ['sort' => ['created_at' => 'timestamp']]);
        $data = Datatable::default($data, 'user', function ($item) {
            return $item->type == 'new' ? 'Lead' : User::selectRaw('CONCAT(first_name, " ", last_name) as user')->withTrashed()->where('id', $item->user_id)->value('user');
        });
        // $data = Datatable::nl2br($data, 'message');

        $data = Datatable::default($data, 'highlighted', function ($item) {
            if ($item->type == 'new') {
                return true;
            }
        });

        return Datatable::data($data, array_merge(array_column($this->dColumns(), 'id'), ['highlighted']))->first();
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
                'name' => trans('labels.date'),
                'class' => 'vertical-center',
                'render' =>  ['sort'],
            ],
            [
                'id' => 'user',
                'name' => trans('labels.from'),
                'class' => 'vertical-center',
                'order' => false,
            ],
            [
                'id' => 'message',
                'name' => trans('labels.message'),
                'class' => 'w-75',
                'order' => false,
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
                'name' => trans('buttons.new'),
                'visible' => Auth::user()->can('Create: Messages'),
            ],
            'reply' => [
                'url' => Helper::route('api.create', $api->path),
                'query' => 'data-query="reply"',
                'parameters' => 'data-append-id',
                'class' => 'btn-info',
                'icon' => 'reply',
                'method' => 'get',
                'name' => trans('buttons.reply'),
                'visible' => Auth::user()->can('Reply to messages'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Messages'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Messages'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 0 : 1, 'asc'],
        ];
    }

    public function doptions($api)
    {
        return [
            'class' => 'table-leads-highlighted table-no-hover',
            'rowBackground' => 'highlighted',
        ];
    }

    public function dData($api)
    {
        $table = str_plural($api->meta->model);
        $data = $this->selectRaw($table . '.id, ' . $table . '.type, ' . $table . '.message, ' . $table . '.created_at, ' . $table . '.updated_at, CONCAT(users.first_name, " ", COALESCE(users.last_name, "")) as user')
            ->leftJoin('users', $table . '.user_id', '=', 'users.id')
            ->where($table . '.model_id', $api->model->_parent->id)
            ->where($table . '.model', $api->meta->_parent->model)
            ->orderBy($table . '.created_at')
            ->get();

        $data = Datatable::format($data, 'date', 'd.m.Y', 'created_at', 'created');
        $data = Datatable::render($data, 'created', ['sort' => ['created_at' => 'timestamp']]);
        $data = Datatable::default($data, 'user', function ($item) {
            return $item->type == 'new' ? 'Lead' : $item->user;
        });
        // $data = Datatable::nl2br($data, 'message');

        $data = Datatable::default($data, 'highlighted', function ($item) {
            if ($item->type == 'new') {
                return true;
            }
        });

        return Datatable::data($data, array_merge(array_column($this->dColumns(), 'id'), ['highlighted']));
    }
}
