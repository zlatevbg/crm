<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use App\Models\Library;
use Mailgun\Mailgun;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Client extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $fillable = [
        'agent_id',
        'first_name',
        'last_name',
        'email',
        'phone_code',
        'phone_number',
        'gender',
        'country_id',
        'source_id',
        'city',
        'postcode',
        'address1',
        'address2',
        'passport',
        'newsletters',
        'sms',
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $softCascade = [
        'statusesSoftDelete',
        'projectsSoftDelete',
    ];

    public $_parent;

    public function isNotInteractable($api)
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Clients') && !Auth::user()->can('Edit: Clients') && !Auth::user()->can('Delete: Clients'));
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withTimestamps()->whereNull('client_project.deleted_at');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class)->where('parent', 1);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class)->withTrashed();
    }

    public function getNexmoPhoneAttribute()
    {
        return preg_replace('/^00/', '', str_replace([' ', '-', '.', '/', '+'], '', $this->phone_code . $this->phone_number));
    }

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getPhoneAttribute()
    {
        return $this->phone_code . ' ' . $this->phone_number;
    }

    public function getFullNameAttribute()
    {
        return $this->getNameAttribute();
    }

    public function statuses()
    {
        return $this->belongsToMany(Status::class)->withTimestamps()->where('statuses.parent', 2);
    }

    public function statusesSoftDelete()
    {
        return $this->hasMany(ClientStatus::class);
    }

    public function projectsSoftDelete()
    {
        return $this->hasMany(ClientProject::class);
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

    public function selectNewslettersSubscription()
    {
        return [
            0 => trans('labels.no'),
            1 => trans('labels.yes'),
        ];
    }

    public function selectSmsSubscription()
    {
        return [
            0 => trans('labels.no'),
            1 => trans('labels.yes'),
        ];
    }

    public function selectProjects()
    {
        return Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->whereIn('id', Helper::project())->orderBy('projects')->pluck('projects', 'id');
    }

    public function selectAgent()
    {
        $agents = Agent::select('id', 'company AS agent');

        if ($this->_parent) {
            $agents = $agents->where('id', $this->_parent->id);
        }

        $agents = $agents->orderBy('agent')->pluck('agent', 'id')->toArray();

        return $agents;
    }

    public function selectClients()
    {
        return Client::selectRaw('id, TRIM(CONCAT(first_name, " ", COALESCE(last_name, ""))) AS client')->orderBy('first_name')->orderBy('last_name')->pluck('client', 'id');
    }

    public function selectStatus()
    {
        return Status::where('parent', 2)->orderBy('name')->get();
    }

    public function selectPhoneCodes()
    {
        return Country::selectRaw('phone_code as id, CONCAT(name, " (", phone_code, ")") as name')->orderBy('name')->pluck('name', 'id');
    }

    public function selectCountries()
    {
        return Country::orderBy('name')->pluck('name', 'id');
    }

    public function selectSources()
    {
        return Source::where('parent', 1)->orderBy('name')->pluck('name', 'id');
    }

    public function createRules($request, $api)
    {
        return [
            'projects' => 'nullable|array',
            'project_id' => 'nullable|numeric|exists:projects,id',
            'agent_id' => 'nullable|numeric|exists:agents,id',
            'source_id' => 'present|nullable|numeric|exists:sources,id',
            'first_name' => 'required|max:255',
            'last_name' => 'present|max:255',
            'email' => [
                'present',
                'nullable',
                'email',
                'max:255',
                Rule::unique(str_plural($api->meta->model))->ignore($api->id)->where(function ($query) use ($api) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'phone_code' => 'present|max:255',
            'phone_number' => 'required_with:phone_code|numeric',
            'gender' => 'required|in:female,male,not-applicable,not-known',
            'country_id' => 'present|nullable|numeric|exists:countries,id',
            'city' => 'present|max:255',
            'postcode' => 'present|max:255',
            'address1' => 'present|max:255',
            'address2' => 'present|max:255',
            'passport' => 'present|max:255',
            'newsletters' => 'numeric|in:0,1',
            'sms' => 'numeric|in:0,1',
        ];
    }

    public function postStore($api, $request)
    {
        if (!Storage::disk('public')->exists($api->meta->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id);
        }

        if (!Storage::disk('public')->exists($api->meta->id . DIRECTORY_SEPARATOR . $this->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id . DIRECTORY_SEPARATOR . $this->id);
        }

        $this->statuses()->attach($request->input('status_id'), ['user_id' => Auth::user()->id]);
        if (session('project')) {
            $this->projects()->attach($request->input('project_id'));
        } else {
            $this->projects()->attach($request->input('projects'));
        }
    }

    public function updateData($request)
    {
        $data = $request->all();

        if ($this->email && $this->newsletters != $data['newsletters']) {
            $mailgun = Mailgun::create(env('MAILGUN_SECRET'), 'https://api.eu.mailgun.net');
            // Add $params to delete() method on \Mailgun\Api\Suppression\Unsubscribe;

            if ($data['newsletters']) {
                $unsubscribes = $mailgun->suppressions()->unsubscribes()->index(env('MAILGUN_DOMAIN'));
                foreach ($unsubscribes->getItems() as $unsubscribe) {
                    if ($unsubscribe->getAddress() == $this->email) {
                        $tags = $unsubscribe->getTags();

                        if (in_array('*', $tags)) {
                            $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email);
                        } elseif (in_array('newsletter-clients', $tags)) {
                            if (count($tags) == 1) {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email);
                            } else {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-clients']);
                            }
                        }
                    }
                }
            } else {
                $mailgun->suppressions()->unsubscribes()->create(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-clients']);
            }
        }

        return $data;
    }

    public function postUpdate($api, $request, $data)
    {
        if (!Storage::disk('public')->exists($api->meta->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id);
        }

        if (!Storage::disk('public')->exists($api->meta->id . DIRECTORY_SEPARATOR . $this->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id . DIRECTORY_SEPARATOR . $this->id);
        }

        /*$this->statuses()->updateExistingPivot($this->statuses->first()->id, [
            'status_id' => $request->input('status_id'),
            'user_id' => Auth::user()->id,
        ]);*/

        if (!session('project')) {
            $this->projects()->sync($request->input('projects'));
        }
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
        $data = Datatable::link($this, 'fullname', 'name', $api->meta->slug, true);
        $data = Datatable::relationship($data, 'country_name', 'country');
        $data = Datatable::relationship($data, 'source_name', 'source', 'name');
        $data = Datatable::relationship($data, 'status', 'statuses', 'name', true);
        $data = Datatable::default($data, 'phone');

        $data->first()->agent = Agent::select('company AS agent')->where('id', $data->first()->agent_id)->value('agent');

        $data->first()->project = $this->projects->implode('name', ', ');

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

        if (Auth::user()->can('View: Sales')) {
            $tabs = array_merge($tabs, [
                'sales' => [
                    'hidden' => true,
                    'slug' => 'sales',
                    'name' => trans('buttons.properties'),
                    'overview' => [
                        'options' => [
                            'dom' => '<"card-block table-responsive"tr>',
                            'order' => false,
                            'class' => 'table-overview',
                        ],
                        'columns' => [
                            [
                                'id' => 'number',
                                'name' => trans('labels.id'),
                                'class' => 'vertical-center',
                            ],
                            [
                                'id' => 'apartment',
                                'name' => trans('labels.apartment'),
                                'class' => 'text-center vertical-center',
                            ],
                            [
                                'id' => 'block',
                                'name' => trans('labels.block'),
                                'class' => 'text-center vertical-center',
                            ],
                            [
                                'id' => 'bed',
                                'name' => trans('labels.bed'),
                                'class' => 'text-center vertical-center',
                            ],
                            [
                                'id' => 'price',
                                'name' => trans('labels.price'),
                                'class' => 'text-right vertical-center',
                            ],
                            [
                                'id' => 'balance',
                                'name' => trans('labels.balance'),
                                'class' => 'text-right vertical-center',
                            ],
                            [
                                'id' => 'status',
                                'name' => trans('labels.status'),
                                'class' => 'vertical-center',
                            ],
                        ],
                        'buttons' => [],
                        'order' => [
                            [1, 'asc']
                        ],
                    ],
                ],
            ]);
        }

        if (Auth::user()->can('View: Client Status')) {
            $tabs = array_merge($tabs, [
                'client-status' => [
                    'slug' => 'client-status',
                    'name' => trans('buttons.status'),
                ],
            ]);
        }

        if (Auth::user()->can('View: Viewings')) {
            $tabs = array_merge($tabs, [
                'viewings' => [
                    'slug' => 'viewings',
                    'name' => trans('buttons.viewings'),
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
                'hidden' => $this->isNotInteractable($api),
            ],
            [
                'id' => 'fullname',
                'name' => trans('labels.name'),
            ],
            [
                'id' => 'status',
                'name' => trans('labels.status'),
            ],
            [
                'id' => 'country_name',
                'name' => trans('labels.country'),
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
            [
                'id' => 'source_name',
                'name' => trans('labels.source'),
            ],
        ];

        if (!$api->model->_parent) {
            array_push($columns, [
                'id' => 'agent',
                'name' => trans('labels.agent'),
            ]);
        }

        if (!session('project')) {
            array_push($columns, [
                'id' => 'project',
                'name' => trans('labels.project'),
            ]);
        }

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
                'visible' => Auth::user()->can('Create: Clients'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Clients'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Clients'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable($api) ? 0 : 1, 'asc'],
        ];
    }

    public function dData($api)
    {
        $table = str_plural($api->meta->model);
        $clients = $this->with(['country', 'source'])->selectRaw($table . '.id, ' . $table . '.first_name, ' . $table . '.last_name, ' . $table . '.email, ' . $table . '.phone_code, ' . $table . '.phone_number, ' . $table . '.country_id, ' . $table . '.source_id, agents.company AS agent, ' . (session('project') ? '' : 'GROUP_CONCAT(TRIM(CONCAT(projects.name, " ", COALESCE(projects.location, ""))) SEPARATOR ", ") AS project, ') . DB::raw('(SELECT statuses.name FROM client_status LEFT JOIN statuses ON statuses.id = client_status.status_id WHERE client_status.client_id = clients.id AND client_status.deleted_at IS NULL ORDER BY client_status.created_at DESC LIMIT 1) AS status'))
            ->leftJoin('agents', 'agents.id', '=', $table . '.agent_id')
            ->leftJoin('client_project', 'client_project.client_id', '=', $table . '.id')
            ->leftJoin('projects', 'projects.id', '=', 'client_project.project_id')
            ->where(function ($query) {
                $query->whereIn('client_project.project_id', Helper::project())->when(!session('project'), function ($q) {
                    return $q->orWhereNull('client_project.project_id');
                });
            });

        if ($api->model->_parent) {
            $clients = $clients->where($table . '.agent_id', $api->model->_parent->id);
        }

        $clients = $clients->groupBy($table . '.id')->get();

        $data = Datatable::relationship($clients, 'source_name', 'source', 'name');
        $data = Datatable::relationship($data, 'country_name', 'country');
        $data = Datatable::default($data, 'phone');
        $data = Datatable::link($data, 'fullname', 'name', $api->meta->slug, true);

        return Datatable::data($data, array_column($this->dColumns($api), 'id'));
    }

    public function homeView($api)
    {
        $projects = $api->model->projects->pluck('id')->all();
        if (count($projects)) {
            if (!in_array(session('project'), array_merge([0], $projects))) {
                Session::forget('project');
            }

            $allowedProjects = Helper::project();
            $foundProject = false;
            foreach ($projects as $project) {
                if (in_array($project, $allowedProjects)) {
                    $foundProject = true;
                }
            }

            if ($foundProject || Auth::user()->can('View All Projects')) {

            } else {
                abort(403);
            }
        }

        $buttons = [
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug) . '/' . $api->id . '?reload=true',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Clients'),
            ],
        ];

        $buttonsAgents = [];
        if ($api->model->agent) {
            $buttonsAgents = [
                'edit' => [
                    'url' => Helper::route('api.edit', (new Agent)->getTable()) . '/' . $api->model->agent->id . '?reload=true',
                    'class' => 'btn-warning',
                    'icon' => 'edit',
                    'method' => 'get',
                    'name' => trans('buttons.edit'),
                    'visible' => Auth::user()->can('Edit: Agents'),
                ],
            ];
        }

        return view('client.home', compact('api', 'buttons', 'buttonsAgents'));
    }
}
