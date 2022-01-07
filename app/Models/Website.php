<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use App\Facades\Domain;
use App\Models\Library;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Website extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'website',
        'analytics',
    ];

    public $_parent;

    protected $dates = [
        'deleted_at',
    ];

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Websites') && !Auth::user()->can('Edit: Websites') && !Auth::user()->can('Delete: Websites'));
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function createRules($request, $api)
    {
        return [
            'name' => 'required|max:255|unique:' . str_plural($api->meta->model),
            'website' => 'required|max:255',
            'analytics' => 'present|max:255',
        ];
    }

    public function updateRules($request, $api)
    {
        $rules = $this->createRules($request, $api);
        $rules['name'] .= ',name,' . $api->id;

        return $rules;
    }

    public function datatable($api)
    {
        $data = Datatable::link($this, 'name', 'name', $api->meta->slug, true);

        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
    }

    public function actions($api)
    {
        if ($api->id) {
            $currentYear = date('Y');
            $currentMonth = date('n');
            $currentQuarter = ceil($currentMonth / 3);
            $quarter = null;
            $selected = 'all';

            if (request()->has('from') || request()->has('to')) {
                $selected = 'custom';
            } elseif (request()->has('y')) {
                $selected = 'year';
            } elseif (request()->has('q')) {
                $quarter = request()->input('q');
                $selected = 'quarter';
            }

            return [
                'all' => [
                    'url' => Helper::route('analytics', $api->id, false),
                    'class' => 'btn-primary js-link' . ($selected == 'all' ? ' active' : ''),
                    'icon' => '',
                    'method' => 'get',
                    'name' => trans('buttons.lastMonth'),
                ],
                $currentYear => [
                    'url' => Helper::route('analytics', $api->id, false) . '?y=' . $currentYear,
                    'class' => 'btn-primary js-link' . ($selected == 'year' ? ' active' : ''),
                    'icon' => '',
                    'method' => 'get',
                    'name' => $currentYear,
                ],
                'Q1' => [
                    'url' => Helper::route('analytics', $api->id, false) . '?q=1',
                    'class' => 'btn-primary js-link' . ($quarter == 1 ? ' active' : '') . (1 > $currentQuarter ? ' disabled' : ''),
                    'icon' => '',
                    'method' => 'get',
                    'name' => trans('buttons.Q1'),
                ],
                'Q2' => [
                    'url' => Helper::route('analytics', $api->id, false) . '?q=2',
                    'class' => 'btn-primary js-link' . ($quarter == 2 ? ' active' : '') . (2 > $currentQuarter ? ' disabled' : ''),
                    'icon' => '',
                    'method' => 'get',
                    'name' => trans('buttons.Q2'),
                ],
                'Q3' => [
                    'url' => Helper::route('analytics', $api->id, false) . '?q=3',
                    'class' => 'btn-primary js-link' . ($quarter == 3 ? ' active' : '') . (3 > $currentQuarter ? ' disabled' : ''),
                    'icon' => '',
                    'method' => 'get',
                    'name' => trans('buttons.Q3'),
                ],
                'Q4' => [
                    'url' => Helper::route('analytics', $api->id, false) . '?q=4',
                    'class' => 'btn-primary js-link' . ($quarter == 4 ? ' active' : '') . (4 > $currentQuarter ? ' disabled' : ''),
                    'icon' => '',
                    'method' => 'get',
                    'name' => trans('buttons.Q4'),
                ],
                [
                    'view' => 'button-dates-analytics',
                ],
            ];
        }
    }

    public function tabs()
    {
        $tabs = [
            'home' => [
                'slug' => '',
                'name' => $this->name,
            ],
        ];

        if (Auth::user()->can('View: Subscribers')) {
            $tabs = array_merge($tabs, [
                'subscribers' => [
                    'slug' => 'subscribers',
                    'name' => trans('buttons.subscribers'),
                ],
            ]);
        }

        if (Auth::user()->can('View: Articles')) {
            $tabs = array_merge($tabs, [
                'articles' => [
                    'slug' => 'articles',
                    'name' => trans('buttons.newsArticles'),
                ],
            ]);
        }

        if (Auth::user()->can('View: Questions')) {
            $tabs = array_merge($tabs, [
                'questions' => [
                    'slug' => 'questions',
                    'name' => trans('buttons.questions'),
                ],
            ]);
        }

        if ($this->id == 1) { // MESPIL
            if (Auth::user()->can('View: Posts')) {
                $tabs = array_merge($tabs, [
                    'posts' => [
                        'slug' => 'posts',
                        'name' => trans('buttons.blogPosts'),
                    ],
                ]);
            }
        }

        if ($this->id == 2) { // PH
            if (Auth::user()->can('View: Events')) {
                $tabs = array_merge($tabs, [
                    'events' => [
                        'slug' => 'events',
                        'name' => trans('buttons.events'),
                    ],
                ]);
            }

            if (Auth::user()->can('View: Galleries')) {
                $tabs = array_merge($tabs, [
                    'galleries' => [
                        'slug' => 'galleries',
                        'name' => trans('buttons.galleries'),
                    ],
                ]);
            }
        }

        return collect($tabs);
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
                'id' => 'name',
                'name' => trans('labels.name'),
            ],
            [
                'id' => 'website',
                'name' => trans('labels.website'),
            ],
        ];
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
                'visible' => Auth::user()->can('Create: Websites'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Websites'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Websites'),
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
        $data = $this->select('id', 'name', 'website')->whereIn('id', (Auth::user()->can('View All Websites') ? Website::pluck('id') : Auth::user()->websites->pluck('id')))->get();

        return Datatable::link($data, 'name', 'name', $api->path, true);
    }
}
