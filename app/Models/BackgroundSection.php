<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Support\Facades\DB;
use App\Models\Library;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BackgroundSection extends Model
{
    protected $fillable = [
        'position',
        'title',
        'description',
        'button_text',
        'button_link',
        'order',
        'newsletter_id',
    ];

    public $_parent;

    public $backgroundsMetaId = 30;

    public function image()
    {
        return $this->hasOne(Image::class, 'model_id')->where('meta_id', $this->backgroundsMetaId);
    }

    public function selectPosition()
    {
        return [
            'header' => trans('labels.header'),
            'footer' => trans('labels.footer'),
        ];
    }

    public function createRules($request, $api)
    {
        return [
            'title' => 'required_without:description|max:255',
            'description' => 'required_without:title',
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
        Library::where('meta_id', $this->backgroundsMetaId)->whereIn('model_id', $ids)->delete();

        foreach ($ids as $id) {
            Storage::disk('public')->deleteDirectory($this->backgroundsMetaId . DIRECTORY_SEPARATOR . $id);
        }

        DB::statement('SET @pos := 0');
        DB::update('update ' . $this->getTable() . ' SET `order` = (SELECT @pos := @pos + 1) WHERE `newsletter_id` = ? ORDER BY `order`', [$api->model->_parent->id]);
    }

    public function datatable($api)
    {
        if ($this->image) {
            $this->file = $this->image->file;
            $this->uuid = $this->image->uuid;
            $this->width = $this->image->width;
            $this->height = $this->image->height;
        } else {
            $this->file = null;
        }

        $data = Datatable::thumbnail($this, 'file', $this->uploadDirectory($api));
        $data = Datatable::trans($data, 'position', 'labels');

        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
    }

    public function doptions($api)
    {
        return [
            'parameters' => 'data-route="' . Helper::route('api.order', $api->path) . '"',
            'dom' => 'tr',
            'class' => 'photoswipe-wrapper',
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
                'id' => 'file',
                'name' => trans('labels.thumbnail'),
                'order' => false,
                'class' => 'js-value',
            ],
            [
                'id' => 'title',
                'name' => trans('labels.title'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'position',
                'name' => trans('labels.position'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'order',
                'name' => trans('labels.order'),
                'class' => 'vertical-center text-center' . (Auth::user()->can('Reorder') ? ' reorder' : ''),
            ],
        ];
    }

    public function dButtons($api)
    {
        return [
            'delete-image' => [
                'url' => Helper::route('api.delete-image', $api->path),
                'parameters' => 'hidden data-hidden="1" data-ajax data-append-id data-if=".js-value" data-value="delete-image"',
                'class' => 'btn-danger js-action',
                'icon' => 'times',
                'method' => 'post',
                'name' => trans('buttons.removeImage'),
                'visible' => Auth::user()->can('Delete Photo'),
            ],
            'upload' => [
                'url' => Helper::route('api.upload', $api->path),
                'parameters' => 'disabled data-disabled="1" data-upload',
                'class' => 'btn-info js-upload-single',
                'icon' => 'upload',
                'name' => trans('buttons.upload'),
                'visible' => Auth::user()->can('Upload'),
            ],
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
                'url' => Helper::route('api.delete', $api->meta->slug),
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
            [4, 'asc'],
        ];
    }

    public function dData($api)
    {
        $table = str_plural($api->meta->model);
        $data = $this->select($table . '.id', $table . '.position', $table . '.title', $table . '.order', 'library.file', 'library.uuid', 'library.width', 'library.height')->leftJoin('library', function ($join) use ($table) {
            $join->on('library.model_id', '=', $table . '.id')->where('library.meta_id', $this->backgroundsMetaId);
        })->where($table . '.newsletter_id', $api->model->_parent->id)->get();

        $data = Datatable::thumbnail($data, 'file', $this->uploadDirectory($api), true);
        $data = Datatable::trans($data, 'position', 'labels');

        return $data;
    }

    public function upload($uploader)
    {
        $uploader->isImage = true;
        $uploader->resize = true;
        $uploader->resizeWidth = config('upload.resizeWidth');
        $uploader->resizeHeight = config('upload.resizeHeight');
        $uploader->allowedExtensions = config('upload.imageExtensions');

        return $uploader;
    }

    public function uploadDirectory($api)
    {
        return $api->meta->id . ($api->model->id ? DIRECTORY_SEPARATOR . $api->model->id : '');
    }

    public function datatableUpdated($api, $response = [])
    {
        $library = Library::firstOrNew(
            [
                'meta_id' => $api->meta->id,
                'model_id' => $api->model->id,
            ],
            [
                'file' => $response['fileName'] ?? null,
                'size' => $response['fileSize'] ?? null,
                'width' => $response['fileWidth'] ?? null,
                'height' => $response['fileHeight'] ?? null,
                'uuid' => $response['uuid'] ?? null,
                'extension' => $response['fileExtension'] ?? null,
            ]
        );

        if ($library->id) {
            Storage::disk('public')->deleteDirectory($this->uploadDirectory($api) . DIRECTORY_SEPARATOR . $library->uuid);

            $library->meta_id = $api->meta->id;
            $library->model_id = $api->model->id;
            $library->file = $response['fileName'] ?? null;
            $library->size = $response['fileSize'] ?? null;
            $library->width = $response['fileWidth'] ?? null;
            $library->height = $response['fileHeight'] ?? null;
            $library->uuid = $response['uuid'] ?? null;
            $library->extension = $response['fileExtension'] ?? null;
        }

        if ($response) {
            $library->save();
        } else {
            $library->delete();
        }

        $this->file = $library->file ?? null;
        $this->uuid = $library->uuid ?? null;
        $this->width = $library->width ?? null;
        $this->height = $library->height ?? null;

        $data = [
            'id' => $this->id,
            'file' => Datatable::thumbnail($this, 'file', $this->uploadDirectory($api))->first()->file,
            'title' => $this->title,
            'position' => Datatable::trans($this, 'position', 'labels')->first()->position,
            'order' => $this->order,
        ];

        return $data;
    }
}
