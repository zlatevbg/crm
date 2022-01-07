<?php

namespace App\Models;

use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class Image extends Library
{
    public $library = false;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Upload Gallery') && !Auth::user()->can('Edit: Gallery Photo') && !Auth::user()->can('Delete: Gallery Photo'));
    }

    public function createRules($request, $api)
    {
        return [
            'name' => 'present|max:255',
            'link' => 'present|max:255',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['meta_id'] = $api->model->_parent->_parent ? $api->meta->_parent->id : $api->meta->id;
        $data['model_id'] = $api->model->_parent->id;

        return $data;
    }

    public function datatable($api)
    {
        $disk = 'public';
        if ($api->model->_parent->disks && $api->model->_parent->_parent) {
            $disk = $api->model->_parent->disks[$api->model->_parent->_parent->id];
        }

        $data = Datatable::thumbnail($this, 'file', $this->uploadDirectory($api), false, null, $disk);
        $data = Datatable::filesize($data, 'size');

        return Datatable::data($data, array_column($this->dColumns(), 'id'))->first();
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
                'id' => 'file',
                'name' => trans('labels.thumbnail'),
                'order' => false,
            ],
            [
                'id' => 'name',
                'name' => trans('labels.name'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'link',
                'name' => trans('labels.link'),
                'order' => false,
                'class' => 'vertical-center',
            ],
            [
                'id' => 'size',
                'name' => trans('labels.size'),
                'order' => false,
                'class' => 'vertical-center',
            ],
        ];
    }

    public function doptions($api)
    {
        return [
            'dom' => 'tr',
            'class' => 'photoswipe-wrapper',
        ];
    }

    public function dButtons($api)
    {
        return [
            'upload' => [
                'url' => Helper::route('api.upload', $api->path),
                'parameters' => 'data-upload' . (!in_array($api->meta->_parent->model, ['article', 'event', 'post']) ? ' data-file="1"' : '') . (!in_array($api->meta->_parent->model, ['article', 'event']) ? ' data-multiple="1"' : ''),
                'class' => 'btn-info' . (in_array($api->meta->_parent->model, ['article', 'event']) ? ' js-upload-single' : ''),
                'icon' => 'upload',
                'name' => trans('buttons.upload'),
                'visible' => Auth::user()->can('Upload Gallery'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Gallery Photo'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Gallery Photo'),
            ],
        ];
    }

    public function dData($api)
    {
        $images = $this->where('meta_id', $api->model->_parent->_parent ? $api->meta->_parent->id : $api->meta->id)->where('model_id', $api->model->_parent->id)->orderBy('file')->orderBy('name')->get();

        if ($api->model->_parent->_parent) {
            $images = $images->where('parent', $api->model->_parent->_parent->id);
        } else {
            $images = $images->whereNull('parent');
        }

        $disk = 'public';
        if ($api->model->_parent->disks && $api->model->_parent->_parent) {
            $disk = $api->model->_parent->disks[$api->model->_parent->_parent->id];
        }
        $data = Datatable::thumbnail($images, 'file', $this->uploadDirectory($api), false, null, $disk);
        $data = Datatable::filesize($data, 'size');

        return Datatable::data($data, array_column($this->dColumns(), 'id'));
    }

    public function uploadDirectory($api)
    {
        if ($api->model->_parent->paths && $api->model->_parent->_parent) {
            $dir = $api->model->_parent->paths[$api->model->_parent->_parent->id] . DIRECTORY_SEPARATOR . $api->model->_parent->id;
        } else {
            $dir = $api->meta->_parent->id . DIRECTORY_SEPARATOR . $api->model->_parent->id;
        }

        return $dir;
    }

    public function upload($uploader)
    {
        $uploader->isImage = true;
        $uploader->resize = true;
        $uploader->resizeWidth = config('upload.resizeWidth');
        $uploader->allowedExtensions = config('upload.imageExtensions');

        return $uploader;
    }

    public function datatableAdded($api, $request, $response = [])
    {
        $model = $api->model->create([
            'parent' => $api->model->_parent->_parent ? $api->model->_parent->_parent->id : null,
            'meta_id' => $api->model->_parent->_parent ? $api->meta->_parent->id : $api->meta->id,
            'model_id' => $api->model->_parent->id,
            'file' => $response['fileName'] ?? null,
            'size' => $response['fileSize'] ?? null,
            'width' => $response['fileWidth'] ?? null,
            'height' => $response['fileHeight'] ?? null,
            'uuid' => $response['uuid'] ?? null,
            'extension' => $response['fileExtension'] ?? null,
        ]);

        $disk = 'public';
        if ($api->model->_parent->disks && $api->model->_parent->_parent) {
            $disk = $api->model->_parent->disks[$api->model->_parent->_parent->id];
        }

        $data = [
            'id' => $model->id,
            'file' => Datatable::thumbnail($model, 'file', $this->uploadDirectory($api), false, null, $disk)->first()->file,
            'name' => $model->name,
            'link' => $model->link,
            'size' => Datatable::filesize($model, 'size')->first()->size,
        ];

        return $data;
    }

    public function datatableUpdated($api, $response = [])
    {
        $library = Library::firstOrNew(
            [
                'parent' => $api->model->_parent->_parent ? $api->model->_parent->_parent->id : null,
                'meta_id' => $api->model->_parent->_parent ? $api->meta->_parent->id : $api->meta->id,
                'model_id' => $api->model->_parent->id,
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

        $disk = 'public';
        if ($api->model->_parent->disks && $api->model->_parent->_parent) {
            $disk = $api->model->_parent->disks[$api->model->_parent->_parent->id];
        }

        if ($library->id) {
            Storage::disk($disk)->deleteDirectory($this->uploadDirectory($api) . DIRECTORY_SEPARATOR . $library->uuid);

            $library->file = $response['fileName'] ?? null;
            $library->size = $response['fileSize'] ?? null;
            $library->width = $response['fileWidth'] ?? null;
            $library->height = $response['fileHeight'] ?? null;
            $library->uuid = $response['uuid'] ?? null;
            $library->extension = $response['fileExtension'] ?? null;
        }

        $library->save();

        $data = [
            'id' => $library->id,
            'file' => Datatable::thumbnail($library, 'file', $this->uploadDirectory($api), false, null, $disk)->first()->file,
            'name' => $library->name,
            'link' => $library->link,
            'size' => Datatable::filesize($library, 'size')->first()->size,
        ];

        return $data;
    }
}
