<?php

namespace App\Models;

use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Support\Facades\Auth;

class Attachment extends Library
{
    public $library = false;

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['meta_id'] = $api->meta->id;
        $data['model_id'] = $api->model->_parent->id;

        return $data;
    }

    public function doptions($api)
    {
        return [
            'dom' => 'tr',
        ];
    }

    public function dButtons($api)
    {
        return [
            /*'library' => [
                'url' => secure_url('modal-library'),
                'class' => 'btn-info',
                'icon' => 'paperclip',
                'method' => 'get',
                'name' => trans('buttons.library'),
                'visible' => Auth::user()->can('View: Library'),
            ],*/
            'upload' => [
                'url' => Helper::route('api.upload', $api->path),
                'parameters' => 'data-multiple="1" data-file="1" data-upload',
                'class' => 'btn-info',
                'icon' => 'upload',
                'name' => trans('buttons.upload'),
                'visible' => Auth::user()->can('Upload'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
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

    public function uploadDirectory($api)
    {
        return $api->meta->id . DIRECTORY_SEPARATOR . $api->model->_parent->id;
    }

    public function dData($api)
    {
        $data = $this->select(array_merge(array_column($this->dColumns(), 'id'), ['file', 'extension']))->whereNull('parent')->where('meta_id', $api->meta->id)->where('model_id', $api->model->_parent->id)->orderBy('file')->orderBy('name')->get();

        $path = $api->model->id ? str_replace_last('/' . $api->model->id, '', $api->path) : $api->path;
        $data = Datatable::icon($data, 'name', $api->meta->slug);
        $data = Datatable::filesize($data, 'size');

        return $data;
    }

    public function datatableAdded($api, $request, $response = [])
    {
        $model = $api->model->create([
            'meta_id' => $api->meta->id,
            'model_id' => $api->model->_parent->id,
            'name' => $response['fileName'] ?? null,
            'file' => $response['fileName'] ?? null,
            'size' => $response['fileSize'] ?? null,
            'width' => $response['fileWidth'] ?? null,
            'height' => $response['fileHeight'] ?? null,
            'uuid' => $response['uuid'] ?? null,
            'extension' => $response['fileExtension'] ?? null,
        ]);

        $path = $api->model->id ? str_replace_last('/' . $api->model->id, '', $api->path) : $api->path;
        $name = Datatable::icon($model, 'name', $api->meta->slug)->first()->name;

        $data = [
            'id' => $model->id,
            'name' => $name,
            'size' => Datatable::filesize($model, 'size')->first()->size,
        ];

        return $data;
    }
}
