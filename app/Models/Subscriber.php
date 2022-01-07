<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Mailgun\Mailgun;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Subscriber extends Model
{
    protected $fillable = [
        'source',
        'website',
        'email',
        'email_verified_at',
        'is_subscribed',
    ];

    public $_parent;

    protected $dates = [
        'email_verified_at',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Subscribers') && !Auth::user()->can('Edit: Subscribers') && !Auth::user()->can('Delete: Subscribers'));
    }

    public function selectNewslettersSubscription()
    {
        return [
            0 => trans('labels.no'),
            1 => trans('labels.yes'),
        ];
    }

    public function selectWebsite()
    {
        return [
            'mespil' => trans('text.mespil'),
            'ph' => trans('text.ph'),
            'pgv' => trans('text.pgv'),
        ];
    }

    public function selectSource()
    {
        return [
            'subscribe-newsletter' => trans('text.subscribeNewsletter'),
            'join-investor-club' => trans('text.joinInvestorClub'),
            'download-investor-brochure' => trans('text.downloadInvestorBrochure'),
        ];
    }

    public function createRules($request, $api)
    {
        $website = '';
        if ($api->meta->_parent) {
            if ($api->model->_parent->id == 1) {
                $website = 'mespil';
            } elseif ($api->model->_parent->id == 2) {
                $website = 'ph';
            } elseif ($api->model->_parent->id == 3) {
                $website = 'pgv';
            }
        }

        return [
            'website' => ($api->model->_parent ? 'nullable' : 'required|in:mespil,ph,pgv'),
            'source' => 'required|in:subscribe-newsletter,join-investor-club,download-investor-brochure',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(str_plural($api->meta->model))->ignore($api->id)->where(function ($query) use ($api, $request, $website) {
                    if ($api->model->_parent) {
                        return $query->where('website', '=', $website)->where('source', '=', $request->input('source'));
                    } else {
                        return $query->where('website', '=', $request->input('website'))->where('source', '=', $request->input('source'));
                    }
                }),
            ],
            'is_subscribed' => 'numeric|in:0,1',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['is_subscribed'] = 1;
        $data['email_verified_at'] = Carbon::now();

        if ($api->model->_parent) {
            if ($api->model->_parent->id == 1) {
                $data['website'] = 'mespil';
            } elseif ($api->model->_parent->id == 2) {
                $data['website'] = 'ph';
            } elseif ($api->model->_parent->id == 3) {
                $data['website'] = 'pgv';
            }
        }

        return $data;
    }

    public function updateData($request)
    {
        $data = $request->all();

        if ($this->email && $this->is_subscribed != $data['is_subscribed']) {
            $mailgun = Mailgun::create(env('MAILGUN_SECRET'), 'https://api.eu.mailgun.net');
            // Add $params to delete() method on \Mailgun\Api\Suppression\Unsubscribe;

            if ($this->_parent) {
                $website = '';
                if ($this->_parent->id == 1) {
                    $website = 'mespil';
                } elseif ($this->_parent->id == 2) {
                    $website = 'ph';
                } elseif ($this->_parent->id == 3) {
                    $website = 'pgv';
                }
            } else {
                $website = $request->input('website');
            }

            if ($data['is_subscribed']) {
                $unsubscribes = $mailgun->suppressions()->unsubscribes()->index(env('MAILGUN_DOMAIN'));
                foreach ($unsubscribes->getItems() as $unsubscribe) {
                    if ($unsubscribe->getAddress() == $this->email) {
                        $tags = $unsubscribe->getTags();

                        if (in_array('*', $tags)) {
                            $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email);
                        } elseif (in_array('newsletter-mespil', $tags) || in_array('newsletter-ph', $tags) || in_array('newsletter-mespil', $tags)) {
                            if (count($tags) == 1) {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email);
                            } else {
                                $mailgun->suppressions()->unsubscribes()->delete(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-' . $website]);
                            }
                        }
                    }
                }
            } else {
                $mailgun->suppressions()->unsubscribes()->create(env('MAILGUN_DOMAIN'), $this->email, ['tag' => 'newsletter-' . $website]);
            }
        }

        return $data;
    }

    public function datatable($api)
    {
        $data = Datatable::format($this, 'date', 'd.m.Y', 'email_verified_at', 'verified');
        $data = Datatable::render($data, 'verified', ['sort' => ['email_verified_at' => 'timestamp']]);
        $data = Datatable::onoff($data, 'is_subscribed', 'status');
        $data = Datatable::trans($data, 'website', 'text');
        $data = Datatable::trans($data, 'source', 'text');

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
                'id' => 'verified',
                'name' => trans('labels.verifiedAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
            ],
            [
                'id' => 'status',
                'name' => trans('labels.status'),
                'class' => 'text-center vertical-center status',
                'order' => false,
            ],
            [
                'id' => 'source',
                'name' => trans('labels.source'),
                'class' => 'vertical-center',
            ],
        ];

        if (!$api->meta->_parent) {
            array_push($columns, [
                'id' => 'website',
                'name' => trans('labels.website'),
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
                'visible' => Auth::user()->can('Create: Subscribers'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Subscribers'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Subscribers'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 1 : 2, 'desc'],
        ];
    }

    public function dData($api)
    {
        $website = '';
        if ($api->meta->_parent) {
            if ($api->model->_parent->id == 1) {
                $website = 'mespil';
            } elseif ($api->model->_parent->id == 2) {
                $website = 'ph';
            } elseif ($api->model->_parent->id == 3) {
                $website = 'pgv';
            }
        }

        $table = str_plural($api->meta->model);
        $subscribers = $this->select($table . '.id', $table . '.email', $table . '.email_verified_at', $table . '.is_subscribed', $table . '.website', $table . '.source')->when($api->meta->_parent, function ($query) use ($api, $table, $website) {
            return $query->where($table . '.website', $website);
        })->leftJoin('websites', 'websites.slug', '=', $table . '.website')->whereIn('websites.id', (Auth::user()->can('View All Websites') ? Website::pluck('id') : Auth::user()->websites->pluck('id')))->get();

        $data = Datatable::format($subscribers, 'date', 'd.m.Y', 'email_verified_at', 'verified');
        $data = Datatable::render($data, 'verified', ['sort' => ['email_verified_at' => 'timestamp']]);
        $data = Datatable::onoff($data, 'is_subscribed', 'status');
        $data = Datatable::trans($data, 'website', 'text');
        $data = Datatable::trans($data, 'source', 'text');

        return Datatable::data($data, array_column($this->dColumns($api), 'id'));
    }
}
