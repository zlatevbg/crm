<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Mailgun\Mailgun;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Support\Facades\Auth;

class Lead extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $fillable = [
        'name',
        'phone_code',
        'phone_number',
        'email',
        'country_id',
        'newsletters',
        'notes',
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $softCascade = [
        'sourcesSoftDelete',
        'tagsSoftDelete',
    ];

    public $placeholders = [
        'name' => 'NAME',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Leads') && !Auth::user()->can('Edit: Leads') && !Auth::user()->can('Delete: Leads'));
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function sources()
    {
        return $this->belongsToMany(Source::class)->where('parent', 4)->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'model_id')->orderBy('created_at', 'desc');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'model_id')->latest()->withDefault();
    }

    public function lastReceived()
    {
        return $this->hasOne(Message::class, 'model_id')->where('type', 'new')->latest()->withDefault();
    }

    public function lastSent()
    {
        return $this->hasOne(Message::class, 'model_id')->where('type', 'reply')->latest()->withDefault();
    }

    public function getPhoneAttribute()
    {
        return $this->phone_code . ' ' . $this->phone_number;
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
        return Source::select('name', 'id')->where('parent', 4)->orderBy('name')->get()->pluck('name', 'id');
    }

    public function selectTags()
    {
        return Tag::select('name', 'id')->orderBy('name')->get()->pluck('name', 'id');
    }

    public function sourcesSoftDelete()
    {
        return $this->hasMany(LeadSource::class);
    }

    public function tagsSoftDelete()
    {
        return $this->hasMany(LeadTag::class);
    }

    public function createRules($request, $api)
    {
        return [
            'email' => [
                'present',
                'nullable',
                'email',
                'max:255',
                Rule::unique(str_plural($api->meta->model))->ignore($api->id)->where(function ($query) use ($api) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'sources' => 'required|array',
            'tags' => 'required|array',
            'name' => 'present|nullable|max:255',
            'phone_code' => 'present|nullable|max:255',
            'phone_number' => 'required_with:phone_code|max:255',
            'country_id' => 'present|nullable|exists:countries,id',
            'newsletters' => 'numeric|in:0,1',
            'notes' => 'present|nullable',
        ];
    }

    public function postStore($api, $request)
    {
        $this->sources()->attach($request->input('sources'));
        $this->tags()->attach($request->input('tags'));
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
                        } elseif (in_array('newsletter-leads', $tags)) {
                            if (count($tags) == 1) {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email);
                            } else {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-leads']);
                            }
                        }
                    }
                }
            } else {
                $mailgun->suppressions()->unsubscribes()->create(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-leads']);
            }
        }

        return $data;
    }

    public function postUpdate($api, $request, $data)
    {
        $this->sources()->sync($request->input('sources'));
        $this->tags()->sync($request->input('tags'));
    }

    public function tabs()
    {
        $tabs = [
            'home' => [
                'slug' => '',
                'name' => $this->email ?: $this->name,
            ],
        ];

        if (Auth::user()->can('View: Messages')) {
            $tabs = array_merge($tabs, [
                'messages' => [
                    'slug' => 'messages',
                    'name' => trans('buttons.messages'),
                    'overview' => [
                        'options' => [
                            'dom' => '<"card-block table-responsive"tr>',
                            'order' => false,
                            'class' => 'table-overview table-leads-highlighted table-no-hover',
                            'rowBackground' => 'highlighted',
                        ],
                    ],
                ],
            ]);
        }

        return collect($tabs);
    }

    public function datatable($api)
    {
        $data = Datatable::link($this, 'email', 'email', $api->meta->slug, true);
        // $data = Datatable::relationship($data, 'country_name', 'country');
        // $data = Datatable::default($data, 'phone');

        $data->first()->sources = Source::selectRaw('GROUP_CONCAT(DISTINCT sources.name SEPARATOR ", ") as sources')
            ->leftJoin('lead_source', 'lead_source.source_id', '=', 'sources.id')
            ->where('lead_source.lead_id', $data->first()->id)
            ->value('sources');

        $data->first()->tags = Tag::selectRaw('GROUP_CONCAT(DISTINCT tags.name SEPARATOR ", ") as tags')
            ->leftJoin('lead_tag', 'lead_tag.tag_id', '=', 'tags.id')
            ->where('lead_tag.lead_id', $data->first()->id)
            ->value('tags');

        $data = Datatable::popover($data, 'email', 'notes', 'after');

        $data = Datatable::default($data, 'difference', function ($item) {
            if ($item->lastMessage) {
                $start = Carbon::now();
                $end = Carbon::parse($item->lastMessage->getOriginal('created_at'));
                return $end->diffInDays($start);
            }
        });

        $data = Datatable::default($data, 'differenceInSeconds', function ($item) {
            if ($item->lastMessage) {
                $start = Carbon::now();
                $end = Carbon::parse($item->lastMessage->getOriginal('created_at'));
                return $end->diffInSeconds($start);
            }
        });

        $data = Datatable::default($data, 'status', function ($item) {
            if ($item->lastMessage) {
                $status = 'Done';
                if (!$item->lastMessage->is_completed) {
                    if ($item->lastMessage->type == 'new') {
                        if ($item->difference > 3) {
                            $status = 'New, never replied';
                        } else {
                            $status = 'New';
                        }
                    } elseif ($item->lastMessage->type == 'reply') {
                        if ($item->difference <= 7) {
                            $status = 'Replied from ' . $item->lastMessage->user->name;
                        } elseif ($item->difference > 7 && $item->difference < 14) {
                            $status = 'Need to follow up';
                        }
                    }
                }

                return Carbon::createFromTimeStamp(strtotime($item->lastMessage->getOriginal('created_at')))->diffForHumans(null, false, false, 2) . '<br>' . $status;
            }
        });

        $data = Datatable::default($data, 'background', function ($item) {
            if ($item->lastMessage) {
                $color = null;
                if (!$item->lastMessage->is_completed) {
                    if ($item->lastMessage->type == 'new') {
                        if ($item->difference > 3) {
                            $color = 'red';
                        } else {
                            $color = 'yellow';
                        }
                    } elseif ($item->lastMessage->type == 'reply') {
                        if ($item->difference <= 7) {
                            $color = 'green';
                        } elseif ($item->difference > 7 && $item->difference <= 14) {
                            $color = 'blue';
                        }
                    }
                }

                return $color;
            }
        });

        $data = Datatable::popover($data, 'status', 'lastMessage.message', 'after', true);

        $data = Datatable::render($data, 'status', ['sort' => ['differenceInSeconds' => ['pad', 12, 'left']]]);

        return Datatable::data($data, array_merge(array_column($this->dColumns($api), 'id'), ['background']))->first();
    }

    public function dColumns($api)
    {
        return [
            [
                'id' => 'id',
                'checkbox' => true,
                'order' => false,
                'hidden' => $this->isNotInteractable(),
            ],
            [
                'id' => 'email',
                'name' => trans('labels.email'),
                'class' => 'vertical-center popovers',
            ],/*
            [
                'id' => 'name',
                'name' => trans('labels.name'),
            ],
            [
                'id' => 'country_name',
                'name' => trans('labels.country'),
            ],
            [
                'id' => 'phone',
                'name' => trans('labels.phone'),
            ],*/
            [
                'id' => 'sources',
                'name' => trans('labels.source'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'tags',
                'name' => trans('labels.tag'),
                'class' => 'vertical-center',
            ],
            [
                'id' => 'status',
                'name' => trans('labels.lastMessage'),
                'class' => 'vertical-center bg-cell-color popovers popovers-absolute',
                'render' =>  ['sort'],
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
                'name' => trans('buttons.create'),
                'visible' => Auth::user()->can('Create: Leads'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Leads'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Leads'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 3 : 4, 'asc'],
        ];
    }

    public function doptions($api)
    {
        return [
            'cellBackground' => 'background',
        ];
    }

    public function dData($api)
    {
        $table = str_plural($api->meta->model);
        $leads = $this->with([/*'country', */'lastMessage', 'lastMessage.user'])->selectRaw($table . '.id, ' . $table . '.email, ' . $table . '.name, ' . /*$table . '.phone_code, ' . $table . '.phone_number, ' . $table . '.country_id, ' . */$table . '.notes, GROUP_CONCAT(DISTINCT sources.name SEPARATOR ", ") AS sources, GROUP_CONCAT(DISTINCT tags.name SEPARATOR ", ") AS tags')
            ->leftJoin('lead_source', 'lead_source.lead_id', '=', $table . '.id')
            ->leftJoin('sources', 'sources.id', '=', 'lead_source.source_id')
            ->leftJoin('lead_tag', 'lead_tag.lead_id', '=', $table . '.id')
            ->leftJoin('tags', 'tags.id', '=', 'lead_tag.tag_id')
            ->groupBy($table . '.id')
            ->get();

        $data = Datatable::popover($leads, 'email', 'notes', 'after');

        // $data = Datatable::default($data, 'phone');
        // $data = Datatable::relationship($data, 'country_name', 'country');

        $data = Datatable::link($data, 'email', 'email', $api->meta->slug, true);

        $data = Datatable::default($data, 'difference', function ($item) {
            if ($item->lastMessage) {
                $start = Carbon::now();
                $end = Carbon::parse($item->lastMessage->getOriginal('created_at'));
                return $end->diffInDays($start);
            }
        });

        $data = Datatable::default($data, 'differenceInSeconds', function ($item) {
            if ($item->lastMessage) {
                $start = Carbon::now();
                $end = Carbon::parse($item->lastMessage->getOriginal('created_at'));
                return $end->diffInSeconds($start);
            }
        });

        $data = Datatable::default($data, 'status', function ($item) {
            if ($item->lastMessage) {
                $status = 'Done';
                if (!$item->lastMessage->is_completed) {
                    if ($item->lastMessage->type == 'new') {
                        if ($item->difference > 3) {
                            $status = 'New, never replied';
                        } else {
                            $status = 'New';
                        }
                    } elseif ($item->lastMessage->type == 'reply') {
                        if ($item->difference <= 7) {
                            $status = 'Replied from ' . $item->lastMessage->user->name;
                        } elseif ($item->difference > 7/* && $item->difference < 14*/) {
                            $status = 'Need to follow up';
                        }
                    }
                }

                return Carbon::createFromTimeStamp(strtotime($item->lastMessage->getOriginal('created_at')))->diffForHumans(null, false, false, 2) . '<br>' . $status;
            }
        });

        $data = Datatable::default($data, 'background', function ($item) {
            if ($item->lastMessage) {
                $color = null;
                if (!$item->lastMessage->is_completed) {
                    if ($item->lastMessage->type == 'new') {
                        if ($item->difference > 3) {
                            $color = 'red';
                        } else {
                            $color = 'yellow';
                        }
                    } elseif ($item->lastMessage->type == 'reply') {
                        if ($item->difference <= 7) {
                            $color = 'green';
                        } elseif ($item->difference > 7/* && $item->difference < 14*/) {
                            $color = 'blue';
                        }
                    }
                }

                return $color;
            }
        });

        $data = Datatable::popover($data, 'status', 'lastMessage.message', 'after', true);

        $data = Datatable::render($data, 'status', ['sort' => ['differenceInSeconds' => ['pad', 12, 'left']]]);

        return Datatable::data($data, array_merge(array_column($this->dColumns($api), 'id'), ['background']));
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
                'visible' => Auth::user()->can('Edit: Leads'),
            ],
        ];

        return view('lead.home', compact('api', 'buttons'));
    }
}
