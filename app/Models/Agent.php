<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use App\Models\Library;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Support\Facades\Auth;

class Agent extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    public $withTrashed = true;

    protected $fillable = [
        'company',
        'website',
        'type',
        'goldenvisa',
        'country_id',
        'city',
        'postcode',
        'address1',
        'address2',
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $softCascade = [
        'agentActivitySoftDelete',
        'agentContactsSoftDelete',
        'projectsSoftDelete',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Agents') && !Auth::user()->can('Edit: Agents') && !Auth::user()->can('Delete: Agents'));
    }

    public function agentActivitySoftDelete()
    {
        return $this->hasMany(AgentActivity::class);
    }

    public function agentContactsSoftDelete()
    {
        return $this->hasMany(AgentContact::class);
    }

    public function projectsSoftDelete()
    {
        return $this->hasMany(AgentProject::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withTimestamps()->whereNull('agent_project.deleted_at')->withPivot('commission', 'sub_commission');
    }

    public function selectPhoneCodes()
    {
        return Country::selectRaw('phone_code as id, CONCAT(name, " (", phone_code, ")") as name')->orderBy('name')->pluck('name', 'id');
    }

    public function selectCountries()
    {
        return Country::orderBy('name')->pluck('name', 'id');
    }

    public function selectGoldenVisaAgents()
    {
        return [
            0 => trans('labels.no'),
            1 => trans('labels.yes'),
        ];
    }

    public function createRules($request, $api)
    {
        return [
            'company' => 'required|max:255',
            'website' => 'present|nullable|max:255|url',
            'type' => 'required|in:main,referral,direct',
            'goldenvisa' => 'numeric|in:0,1',
            'country_id' => 'present|nullable|exists:countries,id',
            'city' => 'present|max:255',
            'postcode' => 'present|max:255',
            'address1' => 'present|max:255',
            'address2' => 'present|max:255',
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
    }

    public function postUpdate($api, $request, $data)
    {
        if (!Storage::disk('public')->exists($api->meta->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id);
        }

        if (!Storage::disk('public')->exists($api->meta->id . DIRECTORY_SEPARATOR . $this->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id . DIRECTORY_SEPARATOR . $this->id);
        }
    }

    public function postDestroy($api, $ids, $rows)
    {
        /*Library::where('meta_id', $api->meta->id)->whereIn('model_id', $ids)->delete();

        foreach ($ids as $id) {
            Storage::disk('public')->deleteDirectory($api->meta->id . DIRECTORY_SEPARATOR . $id);
        }*/
    }

    public function datatable($api)
    {
        $data = Datatable::link($this, 'company', 'company', $api->meta->slug, true);
        $data = Datatable::relationship($data, 'country_name', 'country');
        $data = Datatable::trans($data, 'type', 'labels');
        $data = Datatable::onoff($data, 'deleted_at', 'status', true);

        $data->first()->project = $this->projects->implode('name', ', ');

        return Datatable::data($data, array_merge(array_column($this->dColumns(), 'id'), ['deleted_at']))->first();
    }

    public function tabs($api)
    {
        $tabs = [
            'home' => [
                'slug' => '',
                'name' => $this->company,
            ],
        ];

        if (Auth::user()->can('View: Clients')) {
            $tabs = array_merge($tabs, [
                'clients' => [
                    'slug' => 'clients',
                    'name' => trans('buttons.leads'),
                ],
            ]);
        }

        if (Auth::user()->can('View: Agent Contacts')) {
            $tabs = array_merge($tabs, [
                'agent-contacts' => [
                    'slug' => 'agent-contacts',
                    'name' => trans('buttons.contacts'),
                ],
            ]);
        }

        if (Auth::user()->can('View: Agent Contracts')) {
            $tabs = array_merge($tabs, [
                'contracts' => [
                    'slug' => 'contracts',
                    'name' => trans('buttons.contracts'),
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
                                'hidden' => (!Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Agent Contracts') && !Auth::user()->can('Edit: Agent Contracts') && !Auth::user()->can('Delete: Agent Contracts'))),
                            ],
                            [
                                'id' => 'project',
                                'name' => trans('labels.project'),
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
                        ],
                    ],
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

        if (Auth::user()->can('View: Agent Activities')) {
            $tabs = array_merge($tabs, [
                'agent-activities' => [
                    'slug' => 'agent-activities',
                    'name' => trans('buttons.activities'),
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

    public function selectType()
    {
        return [
            'main' => trans('labels.main'),
            'referral' => trans('labels.referral'),
            'direct' => trans('labels.direct'),
        ];
    }

    public function dColumns()
    {
        $columns = [
            [
                'id' => 'id',
                'checkbox' => true,
                'order' => false,
                'hidden' => $this->isNotInteractable(),
            ],
            [
                'id' => 'company',
                'name' => trans('labels.company'),
            ],
            [
                'id' => 'type',
                'name' => trans('labels.type'),
            ],
            [
                'id' => 'country_name',
                'name' => trans('labels.country'),
            ],
            [
                'id' => 'status',
                'name' => trans('labels.status'),
                'class' => 'text-center status',
                'order' => false,
            ],
        ];

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
                'url' => Helper::route('api.create', $api->meta->slug),
                'class' => 'btn-success',
                'icon' => 'plus',
                'method' => 'get',
                'name' => trans('buttons.create'),
                'visible' => Auth::user()->can('Create: Agents'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Agents'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Agents'),
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
        $table = str_plural($api->meta->model);
        $data = $this->withTrashed()
            ->with('country')
            ->selectRaw((session('project') ? '' : 'GROUP_CONCAT(TRIM(CONCAT(projects.name, " ", COALESCE(projects.location, ""))) SEPARATOR ", ") AS project, ') . $table . '.id, ' . $table . '.company, ' . $table . '.country_id, ' . $table . '.type, ' . $table . '.deleted_at')
            ->leftJoin('agent_project', 'agent_project.agent_id', '=', $table . '.id')
            ->leftJoin('projects', 'projects.id', '=', 'agent_project.project_id')
            ->where(function ($query) {
                $query->whereIn('agent_project.project_id', Helper::project())->when(!session('project'), function ($q) {
                    return $q->orWhereNull('agent_project.project_id');
                });
            })
            ->groupBy($table . '.id')
            ->get();

        $data = Datatable::link($data, 'company', 'company', $api->meta->slug, true);
        $data = Datatable::relationship($data, 'country_name', 'country');
        $data = Datatable::trans($data, 'type', 'labels');
        $data = Datatable::onoff($data, 'deleted_at', 'status', true);
        return Datatable::data($data, array_merge(array_column($this->dColumns(), 'id'), ['deleted_at']));
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
                'visible' => Auth::user()->can('Edit: Agents'),
            ],
        ];

        return view('agent.home', compact('api', 'buttons'));
    }
}
