<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Mailgun\Mailgun;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Support\Facades\Auth;

class Contact extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $fillable = [
        'first_name',
        'last_name',
        'company',
        'phone_code',
        'phone_number',
        'email',
        'website',
        'gender',
        'country_id',
        'city',
        'postcode',
        'address1',
        'address2',
        'newsletters',
        'notes',
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $softCascade = [
        'tagsSoftDelete',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Contacts') && !Auth::user()->can('Edit: Contacts') && !Auth::user()->can('Delete: Contacts'));
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
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

    public function selectTags()
    {
        return Tag::select('name', 'id')->orderBy('name')->get()->pluck('name', 'id');
    }

    public function tagsSoftDelete()
    {
        return $this->hasMany(ContactTag::class);
    }

    public function createRules($request, $api)
    {
        return [
            'first_name' => 'required|max:255',
            'last_name' => 'present|max:255',
            'company' => 'present|max:255',
            'phone_code' => 'present|max:255',
            'phone_number' => 'required_with:phone_code|max:255',
            'email' => [
                'present',
                'nullable',
                'email',
                'max:255',
                Rule::unique(str_plural($api->meta->model))->ignore($api->id)->where(function ($query) use ($api) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'website' => 'present|nullable|max:255|url',
            'gender' => 'required|in:female,male,not-applicable,not-known',
            'country_id' => 'required|exists:countries,id',
            'city' => 'present|max:255',
            'postcode' => 'present|max:255',
            'address1' => 'present|max:255',
            'address2' => 'present|max:255',
            'newsletters' => 'numeric|in:0,1',
            'notes' => 'present',
        ];
    }

    public function postStore($api, $request)
    {
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
                        } elseif (in_array('newsletter-mespil', $tags)) {
                            if (count($tags) == 1) {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email);
                            } else {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-contacts']);
                            }
                        }
                    }
                }
            } else {
                $mailgun->suppressions()->unsubscribes()->create(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-contacts']);
            }
        }

        return $data;
    }

    public function postUpdate($api, $request, $data)
    {
        $this->tags()->sync($request->input('tags'));
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

        return collect($tabs);
    }

    public function datatable($api)
    {
        $data = Datatable::relationship($this, 'country_name', 'country');
        $data = Datatable::default($data, 'phone');
        $data = Datatable::link($data, 'fullname', 'name', $api->meta->slug, true);

        $data->first()->tags = Tag::selectRaw('GROUP_CONCAT(DISTINCT tags.name SEPARATOR ", ") as tags')
            ->leftJoin('contact_tag', 'contact_tag.tag_id', '=', 'tags.id')
            ->where('contact_tag.contact_id', $data->first()->id)
            ->value('tags');

        $data = Datatable::popover($data, 'note', 'notes');
        $data = Datatable::url($data, 'website', 'website');

        return Datatable::data($data, array_column($this->dColumns($api), 'id'))->first();
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
                'id' => 'fullname',
                'name' => trans('labels.name'),
            ],
            [
                'id' => 'company',
                'name' => trans('labels.company'),
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
                'id' => 'website',
                'name' => trans('labels.website'),
                'order' => false,
            ],
            [
                'id' => 'tags',
                'name' => trans('labels.tags'),
                'order' => false,
                'class' => 'vertical-center cell-max-15',
            ],
            [
                'id' => 'note',
                'name' => trans('labels.notes'),
                'order' => false,
                'class' => 'text-center vertical-center popovers',
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
                'visible' => Auth::user()->can('Create: Contacts'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Contacts'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Contacts'),
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
        $contacts = $this->with('country')->selectRaw($table . '.id, ' . $table . '.first_name, ' . $table . '.last_name, ' . $table . '.company, ' . $table . '.email, ' . $table . '.website, ' . $table . '.phone_code, ' . $table . '.phone_number, ' . $table . '.country_id, ' . $table . '.notes, GROUP_CONCAT(DISTINCT tags.name SEPARATOR ", ") as tags')
            ->leftJoin('contact_tag', 'contact_tag.contact_id', '=', $table . '.id')
            ->leftJoin('tags', 'tags.id', '=', 'contact_tag.tag_id')
            ->groupBy($table . '.id')
            ->get();

        $data = Datatable::relationship($contacts, 'country_name', 'country');
        $data = Datatable::default($data, 'phone');
        $data = Datatable::popover($data, 'note', 'notes');
        $data = Datatable::url($data, 'website', 'website');
        $data = Datatable::link($data, 'fullname', 'name', $api->meta->slug, true);

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
                'visible' => Auth::user()->can('Edit: Contacts'),
            ],
        ];

        return view('contact.home', compact('api', 'buttons'));
    }
}
