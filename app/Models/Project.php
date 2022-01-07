<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Models\Library;
use App\Services\Datatable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Support\Facades\Auth;

class Project extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $fillable = [
        'name',
        'country_id',
        'location',
        'price',
        'site_area',
        'construction_area',
        'gdv',
        'equity',
        'bank',
        'period',
        'irr',
        'description',
        'contact_id',
        'status',
    ];

    public $_parent;
    public $disableUpload = true;

    protected $dates = [
        'deleted_at',
    ];

    protected $softCascade = [
        'featuresSoftDelete',
    ];

    public function isNotInteractable()
    {
        if (request()->has('database') || (request()->has('status') && request()->input('status') == 0)) {
            return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Projects Database') && !Auth::user()->can('Edit: Projects Database') && !Auth::user()->can('Delete: Projects Database'));
        } else {
            return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Projects') && !Auth::user()->can('Edit: Projects') && !Auth::user()->can('Delete: Projects'));
        }
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class)->withTimestamps();
    }

    public function selectContact()
    {
        return Contact::select('id', 'company')->orderBy('company')->pluck('company', 'id');
    }

    public function selectCountries()
    {
        return Country::orderBy('name')->pluck('name', 'id');
    }

    public function selectFeatures()
    {
        $features = Feature::orderBy('order')->get()->toArray();
        $features = Helper::arrayToTree($features);
        return $features;
    }

    public function featuresSoftDelete()
    {
        return $this->hasMany(FeatureProject::class);
    }

    public function getFullNameAttribute()
    {
        return $this->name . ', ' . $this->location . ', ' . $this->country->name;
    }

    public function createRules($request, $api)
    {
        return [
            'name' => 'required|max:255',
            'country_id' => 'required|exists:countries,id',
            'location' => 'required|max:255',
            'price' => 'required_without:status|numeric|between:0,999999999.99',
            'site_area' => 'nullable|numeric|between:0,999999.99',
            'construction_area' => 'nullable|numeric|between:0,999999.99',
            'gdv' => 'nullable|numeric|between:0,999999999.99',
            'equity' => 'nullable|numeric|between:0,999999999.99',
            'bank' => 'nullable|numeric|between:0,999999999.99',
            'period' => 'nullable|max:255',
            'irr' => 'nullable|max:255',
            'contact_id' => 'nullable|numeric',
            'description' => 'present',
            'status' => 'nullable|in:0,1',
            'features' => 'nullable|array',
            // 'features.*' => 'required_without:status|numeric|min:1',
        ];
    }

    /*public function validationMessages($request, $api)
    {
        $features = Feature::whereNull('parent')->orderBy('order')->get()->keyBy('id');

        $messages = [];
        for ($i = 0, $count = count($request->input('features')); $i < $count; $i++) {
            $messages['features.' . $i . '.min'] = trans('validation.required', ['attribute' => $features[($i + 1)]->name]);
        }
        return $messages;
    }*/

    public function postStore($api, $request)
    {
        if (!Storage::disk('public')->exists($api->meta->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id);
        }

        if (!Storage::disk('public')->exists($api->meta->id . DIRECTORY_SEPARATOR . $this->id)) {
            Storage::disk('public')->makeDirectory($api->meta->id . DIRECTORY_SEPARATOR . $this->id);
        }

        if ($request->has('features')) {
            $features = [];
            foreach (array_filter($request->input('features')) as $key => $value) {
                $features[$value] = ['parent' => $key];
            }

            $this->features()->attach($features);
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

        if ($request->has('features')) {
            $features = [];
            foreach (array_filter($request->input('features')) as $key => $value) {
                $features[$value] = ['parent' => $key];
            }

            $this->features()->sync($features);
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
        $data = Datatable::relationship($this, 'country_name', 'country');
        $data = Datatable::link($data, 'name', 'name', $api->meta->slug, true);

        if ($this->status == 0) {
            $data = Datatable::popover($data, 'name');
            $data = Datatable::price($data, ['price', 'gdv', 'equity', 'bank']);

            $contact = Contact::select('company AS contact')->where('id', $data->first()->contact_id)->value('contact');
            $data->first()->contact = $contact ?: '';

            $score = $data->first()->features->sum('score');
            $data->first()->score = '<span class="badge badge-' . ($score <= 50 ? 'danger' : 'success') . '">' . $score . '</span>';
        }

        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
    }

    public function tabs()
    {
        $tabs = [
            'home' => [
                'slug' => '',
                'name' => $this->name,
            ],
        ];

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

        /*if (Auth::user()->can('View: Library')) {
            $tabs = array_merge($tabs, [
                'library' => [
                    'slug' => 'library',
                    'name' => trans('buttons.library'),
                ],
            ]);
        }*/

        if ($this->status) {
            if (Auth::user()->can('View: Targets')) {
                $tabs = array_merge($tabs, [
                    'targets' => [
                        'slug' => 'targets',
                        'name' => trans('buttons.targets'),
                        'class' => 'ml-auto',
                    ],
                ]);
            }

            if (Auth::user()->can('View: Blocks')) {
                $tabs = array_merge($tabs, [
                    'blocks' => [
                        'slug' => 'blocks',
                        'name' => trans('buttons.blocks'),
                    ],
                ]);
            }

            if (Auth::user()->can('View: Floors')) {
                $tabs = array_merge($tabs, [
                    'floors' => [
                        'slug' => 'floors',
                        'name' => trans('buttons.floors'),
                    ],
                ]);
            }

            if (Auth::user()->can('View: Beds')) {
                $tabs = array_merge($tabs, [
                    'beds' => [
                        'slug' => 'beds',
                        'name' => trans('buttons.beds'),
                    ],
                ]);
            }

            if (Auth::user()->can('View: Views')) {
                $tabs = array_merge($tabs, [
                    'views' => [
                        'slug' => 'views',
                        'name' => trans('buttons.views'),
                    ],
                ]);
            }

            if (Auth::user()->can('View: Furniture')) {
                $tabs = array_merge($tabs, [
                    'furnitures' => [
                        'slug' => 'furnitures',
                        'name' => trans('buttons.furnitures'),
                    ],
                ]);
            }
        } else {
            if (Auth::user()->can('View: Offers')) {
                $tabs = array_merge($tabs, [
                    'offers' => [
                        'slug' => 'offers',
                        'name' => trans('buttons.offers'),
                    ],
                ]);
            }

            if (Auth::user()->can('View: Visits')) {
                $tabs = array_merge($tabs, [
                    'visits' => [
                        'slug' => 'visits',
                        'name' => trans('buttons.visits'),
                    ],
                ]);
            }
        }

        return collect($tabs);
    }

    public function dColumns()
    {
        $columns = [];

        if (request()->has('database') || (request()->has('status') && request()->input('status') == 0)) {
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
                    'class' => 'popovers',
                ],
                [
                    'id' => 'country_name',
                    'name' => trans('labels.country'),
                ],
                [
                    'id' => 'location',
                    'name' => trans('labels.location'),
                ],
                [
                    'id' => 'contact',
                    'name' => trans('labels.introducer'),
                ],
                [
                    'id' => 'price',
                    'name' => trans('labels.price'),
                    'class' => 'text-right',
                ],
                [
                    'id' => 'score',
                    'name' => trans('labels.score'),
                    'class' => 'text-right',
                ],
            ];
        } else {
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
                ],
                [
                    'id' => 'location',
                    'name' => trans('labels.location'),
                ],
                [
                    'id' => 'country_name',
                    'name' => trans('labels.country'),
                ],
            ];
        }

        return $columns;
    }

    public function dButtons($api)
    {
        $buttons = [];

        if (Auth::user()->can('View: Projects Database')) {
            if (request()->has('database')) {
                $buttons = array_merge($buttons, [
                    'database' => [
                        'url' => secure_url('projects'),
                        'class' => 'btn-info js-link',
                        'icon' => 'arrow-alt-circle-left',
                        'name' => trans('buttons.back'),
                    ],
                ]);
            } else {
                $buttons = array_merge($buttons, [
                    'database' => [
                        'url' => secure_url('projects?database'),
                        'class' => 'btn-info js-link',
                        'icon' => 'database',
                        'name' => trans('buttons.database'),
                    ],
                ]);
            }
        }

        $buttons = array_merge($buttons, [
            /*'apartments' => [
                'url' => url($api->meta->slug),
                'parameters' => 'data-url="apartments" data-disabled="1"',
                'class' => 'btn-info js-link disabled',
                'icon' => 'building',
                'name' => trans('buttons.apartments'),
                'visible' => Auth::user()->can('View: Apartments'),
            ],
            'library' => [
                'url' => url($api->meta->slug),
                'parameters' => 'data-url="library" data-disabled="1"',
                'class' => 'btn-info js-link disabled',
                'icon' => 'paperclip',
                'name' => trans('buttons.library'),
                'visible' => Auth::user()->can('View: Library'),
            ],*/
            'create' => [
                'url' => Helper::route('api.create', $api->meta->slug, [], false) . (request()->has('database') ? '?database' : ''),
                'class' => 'btn-success',
                'icon' => 'plus',
                'method' => 'get',
                'name' => trans('buttons.create'),
                'visible' => Auth::user()->can('Create: ' . (request()->has('database') ? 'Projects Database' : 'Projects')),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug, [], false),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: ' . (request()->has('database') ? 'Projects Database' : 'Projects')),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug, [], false),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: ' . (request()->has('database') ? 'Projects Database' : 'Projects')),
            ],
        ]);

        return $buttons;
    }

    public function dOrder($api)
    {
        if (request()->has('database')) {
            return [
                [$this->isNotInteractable() ? 4 : 5, 'desc'],
            ];
        } else {
            return [
                [$this->isNotInteractable() ? 0 : 1, 'asc'],
            ];
        }
    }

    public function dData($api)
    {
        if (request()->has('database')) {
            if (Auth::user()->can('View: Projects Database')) {
                $table = str_plural('project');
                $data = $this->with(['features', 'country'])->select($table . '.id', $table . '.name', $table . '.description', $table . '.country_id', $table . '.location', $table . '.price', 'contacts.company AS contact')
                    ->leftJoin('contacts', $table . '.contact_id', '=', 'contacts.id')
                    ->where('status', 0)
                    ->get();

                $data = Datatable::link($data, 'name', 'name', $api->path, true);
                $data = Datatable::popover($data, 'name');
                $data = Datatable::relationship($data, 'country_name', 'country');
                $data = Datatable::price($data, ['price', 'gdv', 'equity', 'bank']);
                $data = Datatable::default($data, 'score', function ($item) {
                    $score = $item->features->sum('score');
                    return '<span class="badge badge-' . ($score <= 50 ? 'danger' : 'success') . '">' . $score . '</span>';
                });
            } else {
                abort(403);
            }
        } else {
            $data = $this->with('country')->whereIn('id', Helper::project())->where('status', 1)->get();
            $data = Datatable::relationship($data, 'country_name', 'country');
            $data = Datatable::link($data, 'name', 'name', $api->path, true);
            $data = Datatable::data($data, array_column($this->dColumns(), 'id'));
        }

        return $data;
    }

    public function homeView($api)
    {
        if (in_array($api->model->id, Helper::project()) || ($api->model->status == 0 && Auth::user()->can('View: Projects Database'))) {

        } else {
            abort(403);
        }

        $buttons = [
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug) . '/' . $api->id . '?reload=true',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Projects'),
            ],
        ];

        $buttonsContacts = [];
        $score = 0;
        if ($api->model->status == 0) {
            if ($api->model->contact) {
                $buttonsContacts = [
                    'edit' => [
                        'url' => Helper::route('api.edit', (new Contact)->getTable()) . '/' . $api->model->contact->id . '?reload=true',
                        'class' => 'btn-warning',
                        'icon' => 'edit',
                        'method' => 'get',
                        'name' => trans('buttons.edit'),
                        'visible' => Auth::user()->can('Edit: Contacts'),
                    ],
                ];
            }

            $score = $api->model->features->sum('score');
        }

        return view('project.home', compact('api', 'buttons', 'buttonsContacts', 'score'));
    }
}
