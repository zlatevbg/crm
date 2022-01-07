<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use App\Models\Library;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Facades\Domain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use App\Notifications\TaskCreated;

class Task extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $fillable = [
        'priority_id',
        'department_id',
        'name',
        'end_at',
        'description',
        'user_id',
        'project_id',
    ];

    protected $dates = [
        'deleted_at',
        'end_at',
        'completed_at',
    ];

    protected $softCascade = [
        'usersSoftDelete',
        'statusesSoftDelete',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Tasks') && !Auth::user()->can('Edit: Tasks') && !Auth::user()->can('Delete: Tasks'));
    }

    public function priority()
    {
        return $this->belongsTo(Status::class)->where('statuses.parent', 5);
    }

    public function statuses()
    {
        return $this->belongsToMany(Status::class, 'task_status')->withTimestamps()->where('statuses.parent', 6);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTrashed()->withTimestamps()->withPivot('viewed_at');
    }

    public function selectUsers()
    {
        return User::select('first_name', 'last_name', 'id')->orderBy('first_name')->orderBy('last_name')->get()->pluck('name', 'id');
    }

    public function selectProjects()
    {
        return Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->whereIn('id', Helper::project())->orderBy('projects')->pluck('projects', 'id');
    }

    public function selectPriorities()
    {
        return Status::where('parent', 5)->orderBy('order')->get();
    }

    public function selectDepartments()
    {
        return Department::orderBy('name')->get();
    }

    public function getEndAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setEndAtAttribute($value)
    {
        $this->attributes['end_at'] = $value ? Carbon::parse($value) : null;
    }

    public function getCompletedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setCompletedAtAttribute($value)
    {
        $this->attributes['completed_at'] = $value ? Carbon::parse($value) : null;
    }

    public function usersSoftDelete()
    {
        return $this->hasMany(TaskUser::class);
    }

    public function statusesSoftDelete()
    {
        return $this->hasMany(TaskStatus::class);
    }

    public function createRules($request, $api)
    {
        return [
            'project_id' => 'nullable|numeric|exists:projects,id',
            'priority_id' => 'required|numeric',
            'department_id' => 'required|numeric',
            'users' => 'required|array',
            'name' => 'required|max:255',
            'end_at' => 'present|nullable|date_format:"d.m.Y"|after_or_equal:' . date('d.m.Y'),
            'description' => 'present',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;

        return $data;
    }

    public function updateData($request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;

        return $data;
    }

    public function postStore($api, $request)
    {
        if (!Storage::disk('public')->exists($api->meta->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id);
        }

        if (!Storage::disk('public')->exists($api->meta->id . DIRECTORY_SEPARATOR . $this->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id . DIRECTORY_SEPARATOR . $this->id);
        }

        $this->users()->attach($request->input('users'));

        $status = Status::where('parent', 6)->where('default', 1)->value('id');
        $this->statuses()->attach($status, ['user_id' => Auth::user()->id]);

        $users = User::find($request->input('users'));
        Notification::send($users, new TaskCreated(Domain::domain(), $this));
    }

    public function postUpdate($api, $request, $data)
    {
        if (!Storage::disk('public')->exists($api->meta->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id);
        }

        if (!Storage::disk('public')->exists($api->meta->id . DIRECTORY_SEPARATOR . $this->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id . DIRECTORY_SEPARATOR . $this->id);
        }

        $this->users()->sync($request->input('users'));
    }

    public function postDestroy($api, $ids, $rows)
    {
        /*Library::where('meta_id', $api->meta->id)->whereIn('model_id', $ids)->delete();

        foreach ($ids AS $id) {
            Storage::disk('public')->deleteDirectory($api->meta->id . DIRECTORY_SEPARATOR . $id);
        }*/
    }

    public function datatable($api)
    {
        $data = Datatable::relationship($this, 'priority_name', 'priority');
        $data = Datatable::relationship($data, 'status', 'statuses', 'name', true);
        $data = Datatable::relationship($data, 'department_name', 'department');
        $data = Datatable::link($data, 'name', 'name', $api->meta->slug, true);
        $data = Datatable::format($data, 'date', 'd.m.Y', 'end_at', 'deadline');
        $data = Datatable::render($data, 'deadline', ['sort' => ['end_at' => 'timestamp']]);
        $data = Datatable::format($data, 'date', 'd.m.Y', 'completed_at', 'completed');
        $data = Datatable::render($data, 'completed', ['sort' => ['completed_at' => 'timestamp']]);
        $data = Datatable::render($data, 'priority_name', ['sort' => 'order']);
        $data = Datatable::actions($api, $data);

        $data->first()->projectName = optional($this->project)->name;

        $data->first()->people = User::selectRaw('GROUP_CONCAT(DISTINCT CONCAT(users.first_name, " ", COALESCE(users.last_name, "")) SEPARATOR ", ") AS people')
            ->leftJoin('task_user', 'task_user.user_id', '=', 'users.id')
            ->where('task_user.task_id', $data->first()->id)
            ->value('people');

        return Datatable::data($data, array_column($this->dColumns($api), 'id'))->first();
    }

    public function tabs()
    {
        $tabs = [
            'home' => [
                'slug' => '',
                'name' => $this->name,
            ],
        ];

        if (Auth::user()->can('View: Task Status')) {
            $tabs = array_merge($tabs, [
                'task-status' => [
                    'slug' => 'task-status',
                    'name' => trans('buttons.status'),
                ],
            ]);
        }

        if (Auth::user()->can('View: Notes')) {
            $tabs = array_merge($tabs, [
                'notes' => [
                    'slug' => 'notes',
                    'name' => trans('buttons.notes'),
                    'overview' => [
                        'options' => [
                            'dom' => '<"card-block table-responsive"tr>',
                            'order' => false,
                            'class' => 'table-overview',
                        ],
                        'columns' => [
                            [
                                'id' => 'id',
                                'checkbox' => true,
                                'hidden' => (!Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Notes') && !Auth::user()->can('Edit: Notes') && !Auth::user()->can('Delete: Notes'))),
                            ],
                            [
                                'id' => 'created',
                                'name' => trans('labels.createdAt'),
                                'class' => 'vertical-center',
                            ],
                            [
                                'id' => 'user',
                                'name' => trans('labels.modifiedBy'),
                                'class' => 'vertical-center',
                            ],
                            [
                                'id' => 'title',
                                'name' => trans('labels.title'),
                                'class' => 'vertical-center',
                            ],
                            [
                                'id' => 'description',
                                'name' => trans('labels.description'),
                            ],
                        ],
                    ],
                ],
            ]);
        }

        if (Auth::user()->can('View: Library')) {
            $tabs = array_merge($tabs, [
                'library' => [
                    'slug' => 'library',
                    'name' => trans('buttons.library'),
                ],
            ]);
        }

        return collect($tabs);
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
                'id' => 'name',
                'name' => trans('labels.name'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'people',
                'name' => trans('labels.assignedTo'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'department_name',
                'name' => trans('labels.department'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'deadline',
                'name' => trans('labels.deadline'),
                'render' =>  ['sort'],
                'class' => 'text-center vertical-center',
            ],
            [
                'id' => 'priority_name',
                'name' => trans('labels.priority'),
                'class' => 'text-center vertical-center task-priority',
                'render' =>  ['sort'],
            ],
        ];

        if (!request()->has('archive')) {
            array_push($columns, [
                'id' => 'status',
                'name' => trans('labels.status'),
                'class' => 'text-center vertical-center',
            ]);
        }

        if (request()->has('archive')) {
            array_push($columns, [
                'id' => 'completed',
                'name' => trans('labels.completed'),
                'render' =>  ['sort'],
                'class' => 'text-center vertical-center',
            ]);
        }

        if (Auth::user()->can('Complete Task') && !request()->has('archive')) {
            array_push($columns, [
                'id' => 'actions',
                'name' => trans('labels.actions'),
                'class' => 'text-right datatable-actions vertical-center',
                'order' => false,
            ]);
        }

        if (!session('project')) {
            array_push($columns, [
                'id' => 'projectName',
                'name' => trans('labels.project'),
                'class' => 'vertical-center',
            ]);
        }

        return $columns;
    }

    public function dButtons($api)
    {
        $buttons = [];

        if (Auth::user()->can('View: Tasks Archive')) {
            if (request()->has('archive')) {
                $buttons = array_merge($buttons, [
                    'archive' => [
                        'url' => secure_url('tasks'),
                        'class' => 'btn-info js-link',
                        'icon' => 'arrow-alt-circle-left',
                        'name' => trans('buttons.back'),
                    ],
                ]);
            } else {
                $buttons = array_merge($buttons, [
                    'archive' => [
                        'url' => secure_url('tasks?archive'),
                        'class' => 'btn-info js-link',
                        'icon' => 'archive',
                        'name' => trans('buttons.archive'),
                    ],
                ]);
            }
        }

        if (!request()->has('archive')) {
            $buttons = array_merge($buttons, [
                'create' => [
                    'url' => Helper::route('api.create', $api->path),
                    'class' => 'btn-success',
                    'icon' => 'plus',
                    'method' => 'get',
                    'name' => trans('buttons.create'),
                    'visible' => Auth::user()->can('Create: Tasks'),
                ],
                'edit' => [
                    'url' => Helper::route('api.edit', $api->path),
                    'parameters' => 'disabled data-disabled="1" data-append-id',
                    'class' => 'btn-warning',
                    'icon' => 'edit',
                    'method' => 'get',
                    'name' => trans('buttons.edit'),
                    'visible' => Auth::user()->can('Edit: Tasks'),
                ],
                'delete' => [
                    'url' => Helper::route('api.delete', $api->meta->slug),
                    'parameters' => 'disabled data-disabled',
                    'class' => 'btn-danger',
                    'icon' => 'trash',
                    'method' => 'get',
                    'name' => trans('buttons.delete'),
                    'visible' => Auth::user()->can('Delete: Tasks'),
                ],
            ]);
        }

        return $buttons;
    }

    public function doptions($api)
    {
        return [
            'priorities' => 'order',
        ];
    }

    public function dOrder($api)
    {
        if (request()->has('archive')) {
            return [
                [$this->isNotInteractable() ? 5 : 6, 'desc'],
            ];
        } else {
            return [
                [$this->isNotInteractable() ? 3 : 4, 'asc'],
                [$this->isNotInteractable() ? 4 : 5, 'desc'],
            ];
        }
    }

    public function dActions($api)
    {
        return [
            'complete' => [
                'url' => Helper::route('api.complete-task', $api->meta->slug),
                'parameters' => 'data-ajax',
                'class' => 'btn-info',
                'icon' => 'check',
                'method' => 'get',
                'name' => trans('buttons.complete'),
                'hide' => 'completed_at',
                'visible' => Auth::user()->can('Complete Task'),
            ],
        ];
    }

    public function dData($api)
    {
        $table = str_plural($api->meta->model);
        $tasks = $this->with(['users' => function ($query) {
            $query->where('users.id', Auth::user()->id);
        }])->selectRaw($table . '.id, ' . $table . '.name, ' . $table . '.end_at, ' . $table . '.completed_at, ' . $table . '.description, ' . $table . '.priority_id, ' . $table . '.department_id, departments.name AS department_name, TRIM(CONCAT(projects.name, " ", COALESCE(projects.location, ""))) AS projectName, priorities.order, priorities.name AS priority_name, GROUP_CONCAT(DISTINCT CONCAT(users.first_name, " ", COALESCE(users.last_name, "")) SEPARATOR ", ") AS people, ' . DB::raw('(SELECT statuses.name FROM task_status LEFT JOIN statuses ON statuses.id = task_status.status_id WHERE task_status.task_id = tasks.id AND task_status.deleted_at IS NULL ORDER BY task_status.created_at DESC LIMIT 1) AS status'))
            ->leftJoin('departments', 'departments.id', '=', $table . '.department_id')
            ->leftJoin('statuses AS priorities', 'priorities.id', '=', $table . '.priority_id')
            ->leftJoin('task_user', 'task_user.task_id', '=', $table . '.id')
            ->leftJoin('users', 'users.id', '=', 'task_user.user_id')
            ->leftJoin('projects', 'projects.id', '=', $table . '.project_id')
            ->where(function ($query) use ($table) {
                $query->whereIn($table . '.project_id', Helper::project())->when(Auth::user()->can('View All Tasks'), function ($q) use ($table) {
                    return $q->orWhereNull($table . '.project_id');
                });
            });

        if (!Auth::user()->can('View All Tasks')) {
            $tasks = $tasks->where('task_user.user_id', Auth::user()->id);
        }

        if (request()->has('archive')) {
            $tasks = $tasks->whereNotNull('completed_at');
        } else {
            $tasks = $tasks->whereNull('completed_at');
        }

        $tasks = $tasks->groupBy($table . '.id')->orderBy($table . '.completed_at')->orderBy($table . '.end_at')->get();

        $data = Datatable::link($tasks, 'name', 'name', $api->meta->slug, true);
        $data = Datatable::format($data, 'date', 'd.m.Y', 'end_at', 'deadline');
        $data = Datatable::render($data, 'deadline', ['sort' => ['end_at' => 'timestamp']]);
        $data = Datatable::format($data, 'date', 'd.m.Y', 'completed_at', 'completed');
        $data = Datatable::render($data, 'completed', ['sort' => ['completed_at' => 'timestamp']]);
        $data = Datatable::render($data, 'priority_name', ['sort' => 'order']);
        $data = Datatable::actions($api, $data);

        return Datatable::data($data, array_merge(array_column($this->dColumns($api), 'id'), ['order']));
    }

    public function homeView($api)
    {
        if (!in_array(session('project'), [0, $api->model->project_id])) {
            Session::forget('project');
        }

        if (!Auth::user()->can('View All Tasks') && !in_array($api->model->project_id, Helper::project())) {
            abort(403);
        }

        $user = $api->model->users->where('id', Auth::user()->id)->first();
        if ($user && is_null($user->pivot->viewed_at)) {
            $api->model->users()->updateExistingPivot(Auth::user()->id, ['viewed_at' => Carbon::now()]);

            $see = Status::where('parent', 6)->where('action', 'see')->value('id');
            $complete = Status::where('parent', 6)->where('action', 'complete')->value('id');
            $ids = $api->model->statuses()->whereNull('task_status.deleted_at')->pluck('task_status.status_id')->all();
            if (!in_array($see, $ids) && !in_array($complete, $ids)) {
                // TaskStatus::where('task_id', $api->model->id)->delete();
                TaskStatus::where('task_id', $api->model->id)->update([
                    'deleted_at' => Carbon::now(),
                ]);

                $api->model->statuses()->attach($see, ['user_id' => Auth::user()->id]);
            }
        }

        $buttons = [];

        $buttons = [
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug) . '/' . $api->id . '?reload=true',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Tasks'),
            ],
        ];

        $attachments = Library::where('meta_id', $api->meta->id)->where('model_id', $api->model->id)->get();
        $attachments = Datatable::icon($attachments, 'name', (new Library)->getTable());
        $attachments = Datatable::filesize($attachments, 'size');

        return view('task.home', compact('api', 'buttons', 'attachments'));
    }
}
