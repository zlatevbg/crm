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
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'first_name',
        'last_name',
        'email',
        'phone_code',
        'phone_number',
        'gender',
        'country_id',
        'source_id',
        'newsletters',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Guests') && !Auth::user()->can('Edit: Guests') && !Auth::user()->can('Delete: Guests'));
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class)->where('parent', 2);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getNameAttribute()
    {
        return $this->first_name . ($this->last_name ? ' ' . $this->last_name : '');
    }

    public function getPhoneAttribute()
    {
        return $this->phone_code . ' ' . $this->phone_number;
    }

    public function getFullNameAttribute()
    {
        return $this->getNameAttribute();
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
        return Source::where('parent', 2)->orderBy('name')->pluck('name', 'id');
    }

    public function selectProjects()
    {
        return Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->whereIn('id', Helper::project())->orderBy('projects')->pluck('projects', 'id');
    }

    public function createRules($request, $api)
    {
        return [
            'project_id' => 'required|numeric|exists:projects,id',
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
            'phone_number' => 'required_with:phone_code|max:255',
            'gender' => 'required|in:female,male,not-applicable,not-known',
            'country_id' => 'required|numeric|exists:countries,id',
            'newsletters' => 'numeric|in:0,1',
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
                        } elseif (in_array('newsletter-guests', $tags)) {
                            if (count($tags) == 1) {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email);
                            } else {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-guests']);
                            }
                        }
                    }
                }
            } else {
                $mailgun->suppressions()->unsubscribes()->create(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-guests']);
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
        $data = Datatable::default($data, 'phone');

        $data->first()->projectName = $this->project->name;

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

        if (Auth::user()->can('View: Bookings')) {
            $tabs = array_merge($tabs, [
                'bookings' => [
                    'slug' => 'bookings',
                    'name' => trans('buttons.bookings'),
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
                'id' => 'fullname',
                'name' => trans('labels.name'),
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
        return [
            'create' => [
                'url' => Helper::route('api.create', $api->path),
                'class' => 'btn-success',
                'icon' => 'plus',
                'method' => 'get',
                'name' => trans('buttons.create'),
                'visible' => Auth::user()->can('Create: Guests'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Guests'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Guests'),
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
        $guests = $this->with(['country', 'source'])
            ->selectRaw($table . '.id, ' . $table . '.first_name, ' . $table . '.last_name, ' . $table . '.email, ' . $table . '.phone_code, ' . $table . '.phone_number, ' . $table . '.country_id, ' . $table . '.source_id, TRIM(CONCAT(projects.name, " ", COALESCE(projects.location, ""))) AS projectName')
            ->leftJoin('projects', 'projects.id', '=', $table . '.project_id')
            ->whereIn($table . '.project_id', Helper::project())
            ->get();

        $data = Datatable::relationship($guests, 'source_name', 'source', 'name');
        $data = Datatable::relationship($data, 'country_name', 'country');
        $data = Datatable::default($data, 'phone');
        $data = Datatable::link($data, 'fullname', 'name', $api->meta->slug, true);

        return Datatable::data($data, array_column($this->dColumns(), 'id'));
    }

    public function homeView($api)
    {
        $buttons = [
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug) . '/' . $api->id . '?reload=true',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Guests'),
            ],
        ];

        return view('guest.home', compact('api', 'buttons'));
    }
}
