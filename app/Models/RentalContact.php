<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Mailgun\Mailgun;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class RentalContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email',
        'is_subscribed',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Rental Contacts') && !Auth::user()->can('Edit: Rental Contacts') && !Auth::user()->can('Delete: Rental Contacts'));
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
            'email' => 'required|max:255|email|unique:rental_contacts',
            'is_subscribed' => 'numeric|in:0,1',
        ];
    }

    public function updateRules($request, $api)
    {
        $rules = $this->createRules($request, $api);

        $rules['email'] .= ',email,' . $api->id;

        return $rules;
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['is_subscribed'] = 1;

        return $data;
    }

    public function updateData($request)
    {
        $data = $request->all();

        if ($this->email && $this->is_subscribed != $data['is_subscribed']) {
            $mailgun = Mailgun::create(env('MAILGUN_SECRET'), 'https://api.eu.mailgun.net');
            // Add $params to delete() method on \Mailgun\Api\Suppression\Unsubscribe;

            if ($data['is_subscribed']) {
                $unsubscribes = $mailgun->suppressions()->unsubscribes()->index(env('MAILGUN_DOMAIN'));
                foreach ($unsubscribes->getItems() as $unsubscribe) {
                    if ($unsubscribe->getAddress() == $this->email) {
                        $tags = $unsubscribe->getTags();

                        if (in_array('*', $tags)) {
                            $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email);
                        } elseif (in_array('newsletter-rental-contacts', $tags)) {
                            if (count($tags) == 1) {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email);
                            } else {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-rental-contacts']);
                            }
                        }
                    }
                }
            } else {
                $mailgun->suppressions()->unsubscribes()->create(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-rental-contacts']);
            }
        }

        return $data;
    }

    public function datatable($api)
    {
        $data = Datatable::onoff($this, 'is_subscribed', 'status');

        return Datatable::data($data, array_column($this->dColumns($api), 'id'))->first();
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
                'id' => 'email',
                'name' => trans('labels.email'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'status',
                'name' => trans('labels.status'),
                'class' => 'text-center vertical-center status',
                'order' => false,
            ],
        ];

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
                'visible' => Auth::user()->can('Create: Rental Contacts'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Rental Contacts'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Rental Contacts'),
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
        $rentalContacts = $this->select($table . '.id', $table . '.email', $table . '.is_subscribed')->get();

        $data = Datatable::onoff($rentalContacts, 'is_subscribed', 'status');

        return Datatable::data($data, array_column($this->dColumns($api), 'id'));
    }
}
