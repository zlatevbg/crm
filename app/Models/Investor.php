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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Investor extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $fillable = [
        'fund_size_id',
        'investment_range_id',
        'source_id',
        'category_id',
        'start_at',
        'end_at',
        'first_name',
        'last_name',
        'email',
        'phone_code',
        'phone_number',
        'gender',
        'country_id',
        'city',
        'postcode',
        'address1',
        'address2',
        'bank',
        'company_name',
        'company_phone',
        'website',
        'newsletters',
        'sms',
    ];

    protected $dates = [
        'deleted_at',
        'start_at',
        'end_at',
    ];

    protected $softCascade = [
        'projectsSoftDelete',
        'investorActivitySoftDelete',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Investors') && !Auth::user()->can('Edit: Investors') && !Auth::user()->can('Delete: Investors'));
    }

    public function getStartAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setStartAtAttribute($value)
    {
        $this->attributes['start_at'] = $value ? Carbon::parse($value) : null;
    }

    public function getEndAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setEndAtAttribute($value)
    {
        $this->attributes['end_at'] = $value ? Carbon::parse($value) : null;
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withTimestamps()->whereNull('investor_project.deleted_at');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class)->where('parent', 3);
    }

    public function fundSize()
    {
        return $this->belongsTo(FundSize::class);
    }

    public function investmentRange()
    {
        return $this->belongsTo(InvestmentRange::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->where('parent', 1);
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

    public function projectsSoftDelete()
    {
        return $this->hasMany(InvestorProject::class);
    }

    public function investorActivitySoftDelete()
    {
        return $this->hasMany(InvestorActivity::class);
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

    public function selectPhoneCodes()
    {
        return Country::selectRaw('phone_code as id, CONCAT(name, " (", phone_code, ")") as name')->orderBy('name')->pluck('name', 'id');
    }

    public function selectCountries()
    {
        return Country::orderBy('name')->pluck('name', 'id');
    }

    public function selectProjects()
    {
        return Project::selectRaw('TRIM(CONCAT(name, " ", COALESCE(location, ""))) AS projects, id')->where('status', 1)->whereIn('id', Helper::project())->orderBy('projects')->pluck('projects', 'id');
    }

    public function selectSources()
    {
        return Source::where('parent', 3)->orderBy('name')->pluck('name', 'id');
    }

    public function selectFundSize()
    {
        return FundSize::orderBy('order')->pluck('name', 'id');
    }

    public function selectInvestmentRange()
    {
        return InvestmentRange::orderBy('order')->pluck('name', 'id');
    }

    public function selectInvestorCategory()
    {
        return Category::where('parent', 1)->orderBy('name')->pluck('name', 'id');
    }

    public function createRules($request, $api)
    {
        return [
            'projects' => 'nullable|array',
            'fund_size_id' => 'nullable|numeric|exists:fund_size,id',
            'investment_range_id' => 'nullable|numeric|exists:investment_range,id',
            'start_at' => 'present|nullable|date|date_format:"d.m.Y"',
            'end_at' => 'present|nullable|date|date_format:"d.m.Y"',
            'source_id' => 'present|nullable|numeric|exists:sources,id',
            'category_id' => 'present|nullable|numeric|exists:categories,id',
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
            'bank' => 'present|max:255',
            'company_name' => 'present|max:255',
            'company_phone' => 'present|max:255',
            'website' => 'present|nullable|max:255|url',
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

        $this->projects()->attach($request->input('projects'));
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
                        } elseif (in_array('newsletter-investors', $tags)) {
                            if (count($tags) == 1) {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email);
                            } else {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-investors']);
                            }
                        }
                    }
                }
            } else {
                $mailgun->suppressions()->unsubscribes()->create(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-investors']);
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

        $this->projects()->sync($request->input('projects'));
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
        $data = Datatable::relationship($data, 'source_name', 'source');
        $data = Datatable::relationship($data, 'category_name', 'category');
        $data = Datatable::relationship($data, 'fund_size_name', 'fundSize');
        $data = Datatable::relationship($data, 'investment_range_name', 'investmentRange');
        $data = Datatable::default($data, 'phone');
        /*$data = Datatable::format($data, 'date', 'd.m.Y', 'start_at', 'start');
        $data = Datatable::render($data, 'start', ['sort' => ['start_at' => 'timestamp']]);
        $data = Datatable::format($data, 'date', 'd.m.Y', 'end_at', 'end');
        $data = Datatable::render($data, 'end', ['sort' => ['end_at' => 'timestamp']]);*/

        $projects = $this->projects->implode('name', ', ');
        $data->first()->project = $projects ?: trans('labels.all');

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

        if (Auth::user()->can('View: Investor Activities')) {
            $tabs = array_merge($tabs, [
                'investor-activities' => [
                    'slug' => 'investor-activities',
                    'name' => trans('buttons.activities'),
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
                'id' => 'fullname',
                'name' => trans('labels.name'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'country_name',
                'name' => trans('labels.country'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'email',
                'name' => trans('labels.email'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'phone',
                'name' => trans('labels.phone'),
                'order' => false,
                'class' => 'nowrap vertical-center',
            ],
            [
                'id' => 'source_name',
                'name' => trans('labels.source'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'category_name',
                'name' => trans('labels.category'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'fund_size_name',
                'name' => trans('labels.fundSize'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'investment_range_name',
                'name' => trans('labels.investmentRange'),
                'class' => 'vertical-center',
            ],/*
            [
                'id' => 'start',
                'name' => trans('labels.startAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
            ],
            [
                'id' => 'end',
                'name' => trans('labels.endAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
            ],*/
        ];

        if (!session('project')) {
            array_push($columns, [
                'id' => 'project',
                'name' => trans('labels.project'),
                'class' => 'cell-max-15 vertical-center',
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
                'visible' => Auth::user()->can('Create: Investors'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Investors'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Investors'),
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
        $investors = $this->with(['country', 'source', 'fundSize', 'investmentRange', 'category'])->selectRaw($table . '.id, ' . $table . '.first_name, ' . $table . '.last_name, ' . $table . '.email, ' . $table . '.phone_code, ' . $table . '.phone_number, ' . $table . '.country_id, ' . $table . '.source_id, ' . $table . '.fund_size_id, ' . $table . '.investment_range_id, ' . $table . '.category_id, ' . $table . '.start_at, ' . $table . '.end_at' . (session('project') ? '' : ', GROUP_CONCAT(TRIM(CONCAT(projects.name, " ", COALESCE(projects.location, ""))) SEPARATOR ", ") AS project'))
            ->leftJoin('investor_project', 'investor_project.investor_id', '=', $table . '.id')
            ->leftJoin('projects', 'projects.id', '=', 'investor_project.project_id')
            ->where(function ($query) {
                $query->whereIn('investor_project.project_id', Helper::project())->when(!session('project'), function ($q) {
                    return $q->orWhereNull('investor_project.project_id');
                });
            })
            ->groupBy($table . '.id')
            ->get();

        $data = Datatable::relationship($investors, 'source_name', 'source');
        $data = Datatable::relationship($data, 'country_name', 'country');
        $data = Datatable::relationship($data, 'fund_size_name', 'fundSize');
        $data = Datatable::relationship($data, 'investment_range_name', 'investmentRange');
        $data = Datatable::relationship($data, 'category_name', 'category');
        $data = Datatable::default($data, 'phone');
        $data = Datatable::default($data, 'project', trans('labels.all'));
        $data = Datatable::link($data, 'fullname', 'name', $api->meta->slug, true);
        /*$data = Datatable::format($data, 'date', 'd.m.Y', 'start_at', 'start');
        $data = Datatable::render($data, 'start', ['sort' => ['start_at' => 'timestamp']]);
        $data = Datatable::format($data, 'date', 'd.m.Y', 'end_at', 'end');
        $data = Datatable::render($data, 'end', ['sort' => ['end_at' => 'timestamp']]);*/

        return Datatable::data($data, array_column($this->dColumns($api), 'id'));
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
                'visible' => Auth::user()->can('Edit: Investors'),
            ],
        ];

        return view('investor.home', compact('api', 'buttons'));
    }
}
