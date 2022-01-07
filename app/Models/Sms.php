<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Sms extends Model
{
    use SoftDeletes;

    protected $table = 'sms';

    protected $fillable = [
        'message',
        'group',
        'status',
        'projects',
        'recipients',
        'numbers',
    ];

    protected $dates = [
        'sent_at',
        'deleted_at',
    ];

    public $groups = [
        'clients',
        'custom',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: SMS') && !Auth::user()->can('Edit: SMS') && !Auth::user()->can('Delete: SMS'));
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class)->withTimestamps()->where('sms', 1)->whereNotNull('email');
    }

    public function setRecipientsAttribute($value)
    {
        $this->attributes['recipients'] = $value ? implode(',', $value) : null;
    }

    public function getRecipientsAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = ($value && is_array($value)) ? implode(',', $value) : $value;
    }

    public function getStatusAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function setProjectsAttribute($value)
    {
        $this->attributes['projects'] = ($value && is_array($value)) ? implode(',', $value) : $value;
    }

    public function getProjectsAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function getSentAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setSentAtAttribute($value)
    {
        $this->attributes['sent_at'] = $value ? Carbon::parse($value) : null;
    }

    public function selectGroup()
    {
        return [
            '' => trans('labels.all'),
            'clients' => trans('labels.clients'),
            'custom' => trans('labels.custom'),
        ];
    }

    public function selectRecipients($status = null, $projects = null)
    {
        if ($this->group == 'clients') {
            return Client::selectRaw('clients.id, TRIM(CONCAT(clients.first_name, " ", COALESCE(clients.last_name, ""))) AS recipient')->when($status, function ($query) use ($status) {
                return $query->leftJoin('client_status', 'client_status.client_id', '=', 'clients.id')->whereNull('client_status.deleted_at')->whereIn('client_status.status_id', $status);
            })->when($projects, function ($query) use ($projects) {
                return $query->leftJoin('client_project', 'client_project.client_id', '=', 'clients.id')->whereNull('client_project.deleted_at')->whereIn('client_project.project_id', $projects);
            })->where('clients.sms', 1)->whereNotNull('clients.phone_number')->orderBy('recipient')->pluck('recipient', 'id');
        }

        return [];
    }

    public function selectStatus()
    {
        return Status::select('name', 'id')->where('parent', 2)->orderBy('name')->get()->pluck('name', 'id');
    }

    public function selectProjects()
    {
        return Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->whereIn('id', Helper::project())->orderBy('projects')->pluck('projects', 'id');
    }

    public function createRules($request, $api)
    {
        return [
            'message' => 'required|max:255',
            'group' => 'nullable|present|in:' . implode(',', $this->groups),
            'status' => 'nullable|array',
            'projects' => 'nullable|array',
            'recipients' => 'array',
        ];
    }

    public function updateData($request)
    {
        $data = $request->all();
        $data['status'] = $data['status'] ?? null;
        $data['projects'] = $data['projects'] ?? null;
        $data['recipients'] = $data['recipients'] ?? null;
        $data['numbers'] = $data['numbers'] ?? null;

        return $data;
    }

    public function datatable($api)
    {
        $data = Datatable::format($this, 'date', 'd.m.Y', 'sent_at', 'sent');
        $data = Datatable::render($data, 'sent', ['sort' => ['sent_at' => 'timestamp']]);
        $data = Datatable::trans($data, 'group', 'labels');
        $data = Datatable::popover($data, 'group', 'numbers', 'after');
        $data = Datatable::default($data, 'group', trans('labels.all'));
        $data = Datatable::actions($api, $data);

        $statuses = Status::select('id', 'name')->where('parent', 2)->get();

        $data = Datatable::default($data, 'status', function ($item) use ($statuses) {
            if ($item->status) {
                return $statuses->whereIn('id', $item->status)->implode('name', ', ');
            } else {
                return trans('labels.all');
            }
        });

        $projects = Project::selectRaw('id, TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS name')->where('status', 1)->get();

        $data = Datatable::default($data, 'projects', function ($item) use ($projects) {
            if ($item->projects) {
                return $projects->whereIn('id', $item->projects)->implode('name', ', ');
            } else {
                return trans('labels.all');
            }
        });

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
                'id' => 'message',
                'name' => trans('labels.message'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'group',
                'name' => trans('labels.group'),
                'order' => false,
                'class' => 'vertical-center popovers',
            ],
            [
                'id' => 'status',
                'name' => trans('labels.status'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'projects',
                'name' => trans('labels.projects'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'sent',
                'name' => trans('labels.sentAt'),
                'render' =>  ['sort'],
                'class' => 'text-center vertical-center',
            ],
            [
                'id' => 'actions',
                'name' => trans('labels.actions'),
                'class' => 'text-right datatable-actions vertical-center',
                'order' => false,
            ],
        ];
    }

    public function dButtons($api)
    {
        return [
            /*'send' => [
                'url' => Helper::route('api.send-newsletter', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-ajax data-append-id',
                'class' => 'btn-primary',
                'icon' => 'envelope',
                'method' => 'get',
                'name' => trans('buttons.sendNewsletter'),
                'visible' => Auth::user()->can('Send SMS'),
            ],
            'test' => [
                'url' => Helper::route('api.test-newsletter', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-ajax data-append-id',
                'class' => 'btn-info mr-auto',
                'icon' => 'at',
                'method' => 'get',
                'name' => trans('buttons.testNewsletter'),
                'visible' => Auth::user()->can('Test SMS'),
            ],*/
            'create' => [
                'url' => Helper::route('api.create', $api->meta->slug),
                'class' => 'btn-success',
                'icon' => 'plus',
                'method' => 'get',
                'name' => trans('buttons.create'),
                'visible' => Auth::user()->can('Create: SMS'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: SMS'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: SMS'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 4 : 5, 'desc'],
        ];
    }

    public function dActions($api)
    {
        return [
            'send' => [
                'url' => Helper::route('api.send-sms', $api->meta->slug),
                'parameters' => 'data-ajax',
                'class' => 'btn-primary',
                'icon' => 'envelope',
                'method' => 'get',
                'name' => trans('buttons.send'),
                'hide' => 'sent_at',
                'visible' => Auth::user()->can('Send SMS'),
            ],
            'test' => [
                'url' => Helper::route('api.test-sms', $api->meta->slug),
                'parameters' => 'data-ajax',
                'class' => 'btn-info',
                'icon' => 'at',
                'method' => 'get',
                'name' => trans('buttons.test'),
                'hide' => 'sent_at',
                'visible' => Auth::user()->can('Test SMS'),
            ],
        ];
    }

    public function dData($api)
    {
        $data = $this->select('id', 'message', 'sent_at', 'group', 'status', 'projects', 'numbers')->get();
        $data = Datatable::format($data, 'date', 'd.m.Y', 'sent_at', 'sent');
        $data = Datatable::render($data, 'sent', ['sort' => ['sent_at' => 'timestamp']]);
        $data = Datatable::trans($data, 'group', 'labels');
        $data = Datatable::popover($data, 'group', 'numbers', 'after');
        $data = Datatable::default($data, 'group', trans('labels.all'));
        $data = Datatable::actions($api, $data);

        $statuses = Status::select('id', 'name')->where('parent', 2)->get();

        $data = Datatable::default($data, 'status', function ($item) use ($statuses) {
            if ($item->status) {
                return $statuses->whereIn('id', $item->status)->implode('name', ', ');
            } else {
                return trans('labels.all');
            }
        });

        $projects = Project::selectRaw('id, TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS name')->where('status', 1)->get();

        $data = Datatable::default($data, 'projects', function ($item) use ($projects) {
            if ($item->projects) {
                return $projects->whereIn('id', $item->projects)->implode('name', ', ');
            } else {
                return trans('labels.all');
            }
        });

        return $data;
    }

    public function actions($api)
    {
        return [
            'edit' => [
                'url' => Helper::route('api.edit', $api->path) . '?reload=true',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: SMS'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path) . '?reload=true',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: SMS'),
            ],
        ];
    }
}
