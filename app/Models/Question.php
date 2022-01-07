<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use App\Rules\Slug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Question extends Model
{
    protected $fillable = [
        'parent',
        'name',
        'slug',
        'meta_title',
        'meta_description',
        'content',
        'order',
        'website_id',
    ];

    public $_parent;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Questions') && !Auth::user()->can('Edit: Questions') && !Auth::user()->can('Delete: Questions'));
    }

    public function createRules($request, $api)
    {
        $rules = [
            'name' => 'required|max:255',
            'slug' => ['nullable', 'present', 'max:255', new Slug($api->model->id)],
        ];

        if ($api->model->parent || ($api->id && strtolower($request->method()) != 'patch')) {
            $rules['content'] = 'required';
        } else {
            $rules['meta_title'] = 'required|max:70';
            $rules['meta_description'] = 'required|max:160';
        }

        return $rules;
    }

    public function storeData($api, $request)
    {
        $data = $request->all();

        $data['website_id'] = $api->model->_parent->id;
        $data['slug'] = str_slug($data['slug'] ?: $data['name']);

        if ($api->id) {
            $data['parent'] = $api->model->id;
        }

        $order = $data['order'] ?? 0;
        $maxOrder = $this->where('parent', $api->model->id)->max('order') + 1;

        if (!$order || $order > $maxOrder) {
            $order = $maxOrder;
        } else { // re-order all higher order rows
            $this->where('parent', $api->model->id)->where('order', '>=', $order)->increment('order');
        }

        $data['order'] = $order;

        return $data;
    }

    public function updateData($request)
    {
        $data = $request->all();

        $data['slug'] = str_slug($data['slug'] ?: $data['name']);

        return $data;
    }

    public function postDestroy($api, $ids, $rows)
    {
        DB::statement('SET @pos := 0');
        DB::update('update ' . $this->getTable() . ' SET `order` = (SELECT @pos := @pos + 1) WHERE `parent` = ? ORDER BY `order`', [$api->model->id]);
    }

    public function datatable($api)
    {
        $path = $api->model->id ? str_replace_last('/' . $api->model->id, '', $api->path) : $api->path;

        $data = Datatable::status($this, 'featured', $api->path);

        if ($api->model->parent || ($api->id && strtolower(request()->method()) != 'patch')) {

        } else {
            $data = Datatable::link($data, 'name', 'name', $path, true);
        }

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
                'id' => 'name',
                'name' => trans('labels.' . (($api->model->parent || ($api->id && strtolower(request()->method()) != 'patch')) ? 'question' : 'category')),
            ],
            [
                'id' => 'featured',
                'name' => trans('labels.featured'),
                'order' => false,
                'class' => 'text-center',
            ],
        ];

        if ($api->model->parent || ($api->id && strtolower(request()->method()) != 'patch')) {

        } else {
            array_push($columns, [
                'id' => 'order',
                'name' => trans('labels.order'),
                'class' => 'vertical-center text-center' . (Auth::user()->can('Reorder') ? ' reorder' : ''),
            ]);
        }

        return $columns;
    }

    public function doptions($api)
    {
        return [
            'parameters' => 'data-route="' . Helper::route('api.order', $api->path) . '"',
        ];
    }

    public function dButtons($api)
    {
        $buttons = [
            'create' => [
                'url' => Helper::route('api.create', $api->path),
                'class' => 'btn-success',
                'icon' => 'plus',
                'method' => 'get',
                'name' => trans('buttons.create'),
                'visible' => Auth::user()->can('Create: Questions'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path . ($api->id ? '/' . $api->slug : '')),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Questions'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Questions'),
            ],
        ];

        if (Auth::user()->can('Move')) {
            if ($api->id) {
                $buttons = array_merge([
                    'move' => [
                        'url' => Helper::route('api.move', $api->path),
                        'parameters' => 'disabled data-disabled',
                        'class' => 'btn-info',
                        'icon' => 'exchange-alt',
                        'method' => 'get',
                        'name' => trans('buttons.move'),
                        'visible' => Auth::user()->can('Move'),
                    ],
                ], $buttons);
            }
        }

        return $buttons;
    }

    public function dOrder($api)
    {
        return [
            $api->id ? [$this->isNotInteractable() ? 0 : 1, 'asc'] : [$this->isNotInteractable() ? 2 : 3, 'asc'],
        ];
    }

    public function dData($api)
    {
        $questions = $this->select('id', 'parent', 'name', 'slug', 'featured', 'order')->where('website_id', $this->_parent->id)->where('parent', $api->model->id ?: null)->orderBy('name')->get();

        $data = Datatable::status($questions, 'featured', $api->path);

        if ($api->id) {

        } else {
            $data = Datatable::link($data, 'name', 'name', $api->path, true);
        }

        return $data;
    }
}
