<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Support\Facades\DB;
use App\Models\Library;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class TextSection extends Model
{
    protected $fillable = [
        'title',
        'content',
        'button_text',
        'button_link',
        'order',
        'newsletter_id',
    ];

    public $_parent;
    public $subslug = 'images';
    public $imagesMetaId = 28;

    public function images()
    {
        return $this->hasMany(Image::class, 'model_id')->where('meta_id', $this->imagesMetaId);
    }

    public function createRules($request, $api)
    {
        return [
            // 'title' => 'required_without:content|max:255',
            // 'content' => 'required_without:title',
            'title' => 'present',
            'content' => 'present',
            'button_text' => 'present',
            'button_link' => 'nullable|present|url',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();

        $data['newsletter_id'] = $api->model->_parent->id;

        $order = $data['order'] ?? 0;
        $maxOrder = $this->where('newsletter_id', $api->model->_parent->id)->max('order') + 1;

        if (!$order || $order > $maxOrder) {
            $order = $maxOrder;
        } else { // re-order all higher order rows
            $this->where('newsletter_id', $api->model->_parent->id)->where('order', '>=', $order)->increment('order');
        }

        $data['order'] = $order;

        return $data;
    }

    public function postDestroy($api, $ids, $rows)
    {
        Library::where('meta_id', $this->imagesMetaId)->whereIn('model_id', $ids)->delete();

        foreach ($ids as $id) {
            Storage::disk('public')->deleteDirectory($this->imagesMetaId . DIRECTORY_SEPARATOR . $id);
        }

        DB::statement('SET @pos := 0');
        DB::update('update ' . $this->getTable() . ' SET `order` = (SELECT @pos := @pos + 1) WHERE `newsletter_id` = ? ORDER BY `order`', [$api->model->_parent->id]);

        return true;
    }

    public function datatable($api)
    {
        $path = $api->model->id ? str_replace_last('/' . $api->model->id, '', $api->path) : $api->path;
        $data = Datatable::default($this, 'title', function ($item) {
            return $item->title ?: str_limit(strip_tags($item->content), 50);
        });
        $data = Datatable::link($data, 'title', 'title', $path, true, null, 'id', '/images');

        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
    }

    public function doptions($api)
    {
        return [
            'parameters' => 'data-route="' . Helper::route('api.order', $api->path) . '"',
            'dom' => 'tr',
        ];
    }

    public function dColumns()
    {
        return [
            [
                'id' => 'id',
                'checkbox' => true,
                'order' => false,
                'hidden' => !Auth::user()->can('Select Datatable Rows'),
            ],
            [
                'id' => 'title',
                'name' => trans('labels.title'),
                'order' => false,
            ],
            [
                'id' => 'order',
                'name' => trans('labels.order'),
                'class' => 'text-center' . (Auth::user()->can('Reorder') ? ' reorder' : ''),
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
                'visible' => true,
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => true,
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => true,
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [2, 'asc'],
        ];
    }

    public function dData($api)
    {
        $data = $this->select(array_merge(array_column($this->dColumns(), 'id'), ['content']))->where('newsletter_id', $api->model->_parent->id)->get();
        $data = Datatable::default($data, 'title', function ($item) {
            return $item->title ?: str_limit(strip_tags($item->content), 50);
        });
        $data = Datatable::link($data, 'title', 'title', $api->path, true, null, 'id', '/images');

        return $data;
    }
}
