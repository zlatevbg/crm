<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Mailgun\Mailgun;

class AgentContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'title',
        'phone_code',
        'phone_number',
        'email',
        'gender',
        'newsletters',
        'agent_id',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Agent Contacts') && !Auth::user()->can('Edit: Agent Contacts') && !Auth::user()->can('Delete: Agent Contacts'));
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getPhoneAttribute()
    {
        return $this->phone_code . ' ' . $this->phone_number;
    }

    public function selectPhoneCodes()
    {
        return Country::selectRaw('phone_code as id, CONCAT(name, " (", phone_code, ")") as name')->orderBy('name')->pluck('name', 'id');
    }


    public function selectNewslettersSubscription()
    {
        return [
            0 => trans('labels.no'),
            1 => trans('labels.yes'),
        ];
    }

    public function createRules($request, $api)
    {
        return [
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
            'title' => 'present|max:255',
            'newsletters' => 'numeric|in:0,1',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['agent_id'] = $api->model->_parent->id;

        return $data;
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
                        } elseif (in_array('newsletter-agent-contacts', $tags)) {
                            if (count($tags) == 1) {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email);
                            } else {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-agent-contacts']);
                            }
                        }
                    }
                }
            } else {
                $mailgun->suppressions()->unsubscribes()->create(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-agent-contacts']);
            }
        }

        return $data;
    }

    public function datatable($api)
    {
        $data = Datatable::concat($this, 'fullname', ['first_name', 'last_name']);
        $data = Datatable::default($data, 'phone');
        $data = Datatable::gender($data, 'fullname');
        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
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

    public function dColumns()
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
                'id' => 'title',
                'name' => trans('labels.title'),
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
                'visible' => Auth::user()->can('Create: Agent Contacts'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Agent Contacts'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Agent Contacts'),
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
        $data = $this->select('id', 'first_name', 'last_name', 'title', 'email', 'phone_code', 'phone_number', 'gender')->where('agent_id', $api->model->_parent->id)->orderBy('first_name')->orderBy('last_name')->get();

        $data = Datatable::concat($data, 'fullname', ['first_name', 'last_name']);
        $data = Datatable::default($data, 'phone');
        $data = Datatable::gender($data, 'fullname');

        return Datatable::data($data, array_column($this->dColumns(), 'id'));
    }
}
