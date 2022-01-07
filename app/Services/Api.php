<?php

namespace App\Services;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use App\Models\Meta;

class Api
{
    public $path;
    public $subpath;
    public $slug;
    public $parentSlug;
    public $parentId;
    public $id = null;
    public $subid = null;
    public $meta = null;
    public $submeta = null;
    public $_meta = null;
    public $model = null;
    public $submodel = null;
    public $_model = null;
    public $breadcrumbs = [];
    public $tabs = [];
    public $tabsOverview = [];
    public $actions = [];
    public $datatables = [];

    public function __construct($path = null, $init = true)
    {
        $this->path = $path ?: Request::path();

        $parts = explode('/', $this->path);

        $this->parentSlug = $parts[0] ?? null;
        $this->parentId = $parts[1] ?? null;

        foreach (array_chunk($parts, 2) as $pair) {
            $this->slug = $pair[0];
            $this->id = $pair[1] ?? null;

            $this->meta = $this->meta();
            if ($this->_meta) {
                $this->meta->_parent = $this->_meta;
            }
            $this->_meta = $this->meta;

            $this->model = $this->model();
            if ($this->_model) {
                $this->model->_parent = $this->_model;
            }
            $this->_model = $this->model;

            if (!Request::expectsJson() && Route::currentRouteName() != 'sky.api.download') {
                if ($this->model->id && method_exists($this->model, 'tabs')) {
                    $this->tabs = $this->model->tabs($this);
                    $tabs = $this->tabs;
                }

                if ($this->model->_parent && $this->model->_parent->subslug == $this->slug) {
                    // skip
                } else {
                    array_push($this->breadcrumbs, $this->breadcrumb($this->slug, isset($tabs[$this->slug]) ? $tabs[$this->slug]['name'] : $this->meta->title));
                }

                if ($this->model->library && $this->model->parent) {
                    $library = 'App\Models\\' . studly_case(str_singular($this->meta->slug));
                    $folders = $library::select('id', 'parent', 'name')->where('meta_id', $this->meta->_parent->id)->where('model_id', $this->model->_parent->id)->where('id', '<=', $this->model->parent)->whereNull('file')->orderBy('id')->get()->keyBy('id');
                    foreach ($folders as $folder) {
                        array_push($this->breadcrumbs, $this->breadcrumb($folder->id, $folder->name, true));
                    }
                }

                if ($this->id) {
                    array_push($this->breadcrumbs, $this->breadcrumb($this->id, $this->name()));
                }
            }
        }

        if (!Request::expectsJson() && Route::currentRouteName() != 'sky.api.download') {
            if ($init) {
                if (method_exists($this->model, 'homeView') && $this->id) {
                    $this->tabsOverview = $this->tabsOverview();
                } else {
                    $this->datatables = $this->datatable();
                }
            }

            if (!$this->actions && method_exists($this->model, 'actions')) {
                $this->actions = $this->model->actions($this);
            }
        }

        $this->_meta = null;
        $this->_model = null;
    }

    public function meta($sub = false)
    {
        return Meta::where('slug', $this->slug)->firstOrFail();
    }

    public function model($sub = false)
    {
        if ($sub) {
            $model = $this->submeta->model;
            $id = $this->subid;
        } else {
            $model = $this->meta->model;
            $id = $this->id;
        }

        if ($model) {
            $class = 'App\Models\\' . studly_case($model != 'sms' ? str_singular($model) : $model);

            if (class_exists($class)) {
                $class = new $class;

                if ($id) {
                    if ($class->withTrashed) {
                        $class = $class->withTrashed();
                    }

                    if (is_numeric($id)) {
                        return $class->findOrFail($id);
                    } else {
                        return $class->where('slug', $id)->firstOrFail();
                    }
                }

                return $class;
            }
        }

        return optional();
    }

    public function breadcrumb($slug, $title, $library = false)
    {
        return [
            'slug' => $slug,
            'title' => $title,
        ] + ($library ? ['library' => $library] : []);
    }

    public function name()
    {
        if ($this->meta->model == 'apartment') {
            $name = $this->model->unit;
        } elseif ($this->meta->model == 'agent') {
            $name = $this->model->company;
        } elseif ($this->meta->model == 'lead') {
            $name = $this->model->email ?: $this->model->name;
        } elseif ($this->meta->model == 'gallery') {
            $name = $this->model->gallery;
        } elseif ($this->meta->model == 'sale') {
            $name = $this->model->id . ' (' . $this->model->project->name . ' ' . $this->model->project->location . ' / ' . $this->model->apartment->unit . ')';
        } elseif ($this->meta->model == 'newsletter') {
            $name = $this->model->subject;
        } elseif ($this->meta->model == 'text_section') {
            $name = $this->model->title ?: str_limit(strip_tags($this->model->content), 50);
        } elseif (in_array($this->meta->model, ['article', 'post', 'event'])) {
            $name = $this->model->title;
        } else {
            $name = $this->model->name;
        }

        return $name;
    }

    public function datatable($api = null)
    {
        $obj = $api ?: $this;

        return [
            'datatable-' . $obj->meta->model => [
                'options' => method_exists($obj->model, 'dOptions') ? $obj->model->dOptions($obj) : [],
                'order' => method_exists($obj->model, 'dOrder') ? $obj->model->dOrder($obj) : [],
                'buttons' => method_exists($obj->model, 'dButtons') ? $obj->model->dButtons($obj) : [],
                'columns' => method_exists($obj->model, 'dColumns') ? $obj->model->dColumns($obj) : [],
                'data' => method_exists($obj->model, 'dData') ? $obj->model->dData($obj) : [],
            ],
        ];
    }

    public function breadcrumbs($breadcrumb = [])
    {
        $breadcrumbs = '';
        $url = '';
        $_url = '';

        for ($i = 0, $count = count($this->breadcrumbs); $i < $count; $i++) {
            $url = $_url . $this->breadcrumbs[$i]['slug'] . '/';
            if (!isset($this->breadcrumbs[$i]['library'])) {
                $_url .= $this->breadcrumbs[$i]['slug'] . '/';
            }

            if (!$breadcrumb && $i == $count - 1) {
                $breadcrumbs .= '<li class="breadcrumb-item active" aria-current="page">' . $this->breadcrumbs[$i]['title'] . '</li>';
            } else {
                $breadcrumbs .= '<li class="breadcrumb-item"><a href="' . url(trim($url, '/')) . '">' . $this->breadcrumbs[$i]['title'] . '</a></li><li><i class="fas fa-angle-right fa-xs breadcrumb-divider"></i></li>';
            }
        }

        if ($breadcrumb) {
            $breadcrumbs .= '<li class="breadcrumb-item active" aria-current="page">' . $breadcrumb['title'] . '</li>';
        }

        return $breadcrumbs;
    }

    public function title()
    {
        $title = '';

        foreach ($this->breadcrumbs as $breadcrumb) {
            $title .= $breadcrumb['title'] . ' > ';
        }

        return trim($title, ' > ');
    }

    public function tabsOverview()
    {
        $class = get_class($this);

        $path = $this->path;
        if ($this->model->library) {
            $path = str_replace_last('/' . $this->meta->slug . '/' . $this->model->id, '', $this->path);
        }

        return $this->tabs->map(function ($item, $key) use ($class, $path) {
            if (isset($item['overview'])) {
                $api = new $class($path . '/' . $item['slug'], false);
                $table = 'datatable-' . $api->meta->model . '-overview';

                $item['datatables'] = $this->datatable($api);
                $item['datatables-overview'] = [$table => current($item['datatables'])];

                if (isset($item['overview']['options'])) {
                    $item['datatables-overview'][$table]['options'] = $item['overview']['options'];
                }

                if (isset($item['overview']['columns'])) {
                    $item['datatables-overview'][$table]['columns'] = $item['overview']['columns'];
                }

                if (isset($item['overview']['order'])) {
                    $item['datatables-overview'][$table]['order'] = $item['overview']['order'];
                }

                if (isset($item['overview']['buttons'])) {
                    $item['datatables-overview'][$table]['buttons'] = $item['overview']['buttons'];
                }

                unset($item['datatables']);
                unset($item['overview']);

                return $item;
            }

            return false;
        })->filter();
    }
}
