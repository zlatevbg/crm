<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Models\Library;
use App\Services\Datatable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Apartment extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $fillable = [
        'unit',
        'price',
        'apartment_area',
        'balcony_area',
        'parking_area',
        'common_area',
        'total_area',
        'project_id',
        'block_id',
        'floor_id',
        'bed_id',
        'view_id',
        'furniture_id',
        'reports',
        'public',
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $softCascade = [
        'statusesSoftDelete',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Apartments') && !Auth::user()->can('Edit: Apartments') && !Auth::user()->can('Delete: Apartments'));
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function view()
    {
        return $this->belongsTo(View::class);
    }

    public function furniture()
    {
        return $this->belongsTo(Furniture::class);
    }

    public function statuses()
    {
        return $this->belongsToMany(Status::class)->withTimestamps()->where('statuses.parent', 1);
    }

    public function statusesSoftDelete()
    {
        return $this->hasMany(ApartmentStatus::class);
    }

    public function selectProjects()
    {
        return Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->whereIn('id', Helper::project())->orderBy('projects')->pluck('projects', 'id');
    }

    public function selectBlocks($api = null)
    {
        return Block::whereIn('project_id', $api ? array_wrap($api->model->project_id) : Helper::project())->pluck('name', 'id');
    }

    public function selectFloors($api = null)
    {
        return Floor::whereIn('project_id', $api ? array_wrap($api->model->project_id) : Helper::project())->pluck('name', 'id');
    }

    public function selectBeds($api = null)
    {
        return Bed::whereIn('project_id', $api ? array_wrap($api->model->project_id) : Helper::project())->pluck('name', 'id');
    }

    public function selectViews($api = null)
    {
        return View::whereIn('project_id', $api ? array_wrap($api->model->project_id) : Helper::project())->pluck('name', 'id');
    }

    public function selectFurnitures($api = null)
    {
        return Furniture::whereIn('project_id', $api ? array_wrap($api->model->project_id) : Helper::project())->pluck('name', 'id');
    }

    public function selectReportsVisibility()
    {
        return [
            0 => trans('labels.no'),
            1 => trans('labels.yes'),
        ];
    }

    public function selectPublicVisibility()
    {
        return [
            0 => trans('labels.no'),
            1 => trans('labels.yes'),
        ];
    }

    public function createRules($request, $api)
    {
        return [
            'project_id' => 'required|numeric|exists:projects,id',
            'unit' => 'required|max:255',
            'price' => 'required|numeric|between:0,9999999.99',
            'apartment_area' => 'present|nullable|numeric|between:0,9999.99',
            'balcony_area' => 'present|nullable|numeric|between:0,9999.99',
            'parking_area' => 'present|nullable|numeric|between:0,9999.99',
            'common_area' => 'present|nullable|numeric|between:0,9999.99',
            'total_area' => 'present|nullable|numeric|between:0,9999.99',
            'block_id' => 'nullable|numeric',
            'floor_id' => 'nullable|numeric',
            'bed_id' => 'nullable|numeric',
            'view_id' => 'nullable|numeric',
            'furniture_id' => 'nullable|numeric',
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

        $status = Status::where('parent', 1)->where('default', 1)->value('id');

        $this->statuses()->attach($status, ['user_id' => Auth::user()->id]);
    }

    public function updateData($request)
    {
        $data = $request->all();
        $data['block_id'] = $data['block_id'] ?? null;
        $data['floor_id'] = $data['floor_id'] ?? null;
        $data['bed_id'] = $data['bed_id'] ?? null;
        $data['view_id'] = $data['view_id'] ?? null;
        $data['furniture_id'] = $data['furniture_id'] ?? null;

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
        $path = $api->model->id ? str_replace_last('/' . $api->model->id, '', $api->path) : $api->path;
        $data = Datatable::link($this, 'unit', 'unit', $path, true);
        $data = Datatable::price($data, 'price');
        $data = Datatable::suffix($data, 'apartment_area', ' m<sup>2</sup>', 'float');
        $data = Datatable::relationship($data, 'status', 'statuses', 'name', true);

        $data->first()->projectName = $this->project->name;

        $data->first()->block = Block::where('id', $data->first()->block_id)->value('name');
        $data->first()->floor = Floor::where('id', $data->first()->floor_id)->value('name');
        $data->first()->bed = Bed::where('id', $data->first()->bed_id)->value('name');

        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
    }

    public function tabs()
    {
        $tabs = [
            'home' => [
                'slug' => '',
                'name' => trans('buttons.unit') . ' # ' . $this->unit,
            ],
        ];

        if (Auth::user()->can('View: Apartment Status')) {
            $tabs = array_merge($tabs, [
                'apartment-status' => [
                    'slug' => 'apartment-status',
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
                'id' => 'unit',
                'name' => trans('labels.unit'),
            ],
            [
                'id' => 'block',
                'name' => trans('labels.block'),
            ],
            [
                'id' => 'floor',
                'name' => trans('labels.floor'),
            ],
            [
                'id' => 'status',
                'name' => trans('labels.status'),
            ],
            [
                'id' => 'bed',
                'name' => trans('labels.beds'),
            ],
            [
                'id' => 'apartment_area',
                'name' => trans('labels.area'),
                'class' => 'text-right',
            ],
            [
                'id' => 'price',
                'name' => trans('labels.price'),
                'class' => 'text-right',
            ],
        ];

        if (!session('project')) {
            array_push($columns, [
                'id' => 'projectName',
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
                'name' => trans('buttons.apartment'),
                'visible' => Auth::user()->can('Create: Apartments'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Apartments'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Apartments'),
            ],
        ];

        return [];
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
        $data = $this->select($table . '.id', $table . '.unit', $table . '.price', $table . '.apartment_area', 'beds.name as bed', 'blocks.name as block', 'floors.name as floor', DB::raw('TRIM(CONCAT(projects.name, " ", COALESCE(projects.location, ""))) AS projectName, (SELECT statuses.name FROM apartment_status LEFT JOIN statuses ON statuses.id = apartment_status.status_id WHERE apartment_status.apartment_id = apartments.id and apartment_status.deleted_at IS NULL ORDER BY apartment_status.created_at DESC LIMIT 1) AS status'))
            ->leftJoin('blocks', $table . '.block_id', '=', 'blocks.id')
            ->leftJoin('floors', $table . '.floor_id', '=', 'floors.id')
            ->leftJoin('beds', $table . '.bed_id', '=', 'beds.id')
            ->leftJoin('projects', 'projects.id', '=', $table . '.project_id')
            ->whereIn($table . '.project_id', Helper::project())
            ->get();
        $data = Datatable::link($data, 'unit', 'unit', $api->path, true);
        $data = Datatable::price($data, 'price');
        $data = Datatable::replace($data, 'block');
        $data = Datatable::suffix($data, 'apartment_area', ' m<sup>2</sup>', 'float');

        return $data;
    }

    public function homeView($api)
    {
        if (!in_array(session('project'), [0, $api->model->project_id])) {
            Session::forget('project');
        }

        if (!in_array($api->model->project_id, Helper::project())) {
            abort(403);
        }

        $sale = $api->model->sale;

        $buttons = [
            'edit' => [
                'url' => Helper::route('api.edit', $api->path) . '?reload=true',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Apartments'),
            ],
        ];

        $buttonsSale = [];
        $buttonsClient = [];
        $buttonsAgent = [];
        if ($sale) {
            $buttonsSale = [
                'edit' => [
                    'url' => Helper::route('api.edit', (new Sale)->getTable()) . '/' . $sale->id . '?reload=true',
                    'class' => 'btn-warning',
                    'icon' => 'edit',
                    'method' => 'get',
                    'name' => trans('buttons.edit'),
                    'visible' => Auth::user()->can('Edit: Sales'),
                ],
            ];

            if ($sale->client) {
                $buttonsClient = [
                    'edit' => [
                        'url' => Helper::route('api.edit', (new Client)->getTable()) . '/' . $sale->client->id . '?reload=true',
                        'class' => 'btn-warning',
                        'icon' => 'edit',
                        'method' => 'get',
                        'name' => trans('buttons.edit'),
                        'visible' => Auth::user()->can('Edit: Clients'),
                    ],
                ];

                if ($sale->client->agent) {
                    $buttonsAgent = [
                        'edit' => [
                            'url' => Helper::route('api.edit', (new Agent)->getTable()) . '/' . $sale->client->agent->id . '?reload=true',
                            'class' => 'btn-warning',
                            'icon' => 'edit',
                            'method' => 'get',
                            'name' => trans('buttons.edit'),
                            'visible' => Auth::user()->can('Edit: Agents'),
                        ],
                    ];
                }
            }
        }

        return view('apartment.home', compact('api', 'buttons', 'sale', 'buttonsSale', 'buttonsClient', 'buttonsAgent'));
    }
}
