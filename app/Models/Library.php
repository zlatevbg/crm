<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class Library extends Model
{
    protected $table = 'library';

    protected $fillable = [
        'parent',
        'meta_id',
        'model_id',
        'name',
        'link',
        'file',
        'width',
        'height',
        'size',
        'uuid',
        'extension',
    ];

    public $_parent;
    public $library = true;

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Upload Files') && !Auth::user()->can('Create: Library') && !Auth::user()->can('Edit: Library') && !Auth::user()->can('Delete: Library'));
    }

    public function createRules($request, $api)
    {
        return [
            'name' => 'required|max:255',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();
        $data['parent'] = $api->model->id;
        $data['meta_id'] = $api->meta->_parent->id;
        $data['model_id'] = $api->model->_parent->id;

        return $data;
    }

    public function postStore($api, $request)
    {
        if (!Storage::disk('public')->exists($this->meta_id . DIRECTORY_SEPARATOR . $this->model_id  . DIRECTORY_SEPARATOR . $this->id)) {
            Storage::disk('public')->makeDirectory($this->meta_id . DIRECTORY_SEPARATOR . $this->model_id  . DIRECTORY_SEPARATOR . $this->id);
        }
    }

    public function preDestroy($ids)
    {
        return $this->select('id', 'parent', 'meta_id', 'model_id', 'uuid')->find($ids);
    }

    public function postDestroy($api, $ids, $rows)
    {
        $disk = 'public';
        if ($api->model->_parent && $api->model->_parent->disks && $api->model->_parent->_parent) {
            $disk = $api->model->_parent->disks[$api->model->_parent->_parent->id];
        }

        foreach ($rows as $row) {
            $uuid = '';
            $id = $row->id;

            if ($row->uuid) {
                $uuid = DIRECTORY_SEPARATOR . $row->uuid;
                $id = $row->parent;
            }

            if ($api->model->_parent && $api->model->_parent->_parent) {
                Storage::disk($disk)->deleteDirectory((($api->model->_parent->paths && $api->model->_parent->paths[$api->model->_parent->_parent->id]) ? $api->model->_parent->paths[$api->model->_parent->_parent->id] : $row->meta_id . DIRECTORY_SEPARATOR . $row->parent) . DIRECTORY_SEPARATOR . $row->model_id . DIRECTORY_SEPARATOR . $uuid);
            } else {
                Storage::disk('public')->deleteDirectory($row->meta_id . DIRECTORY_SEPARATOR . $row->model_id . DIRECTORY_SEPARATOR . $id . $uuid);
            }
        }
    }

    public function datatable($api)
    {
        $path = $api->model->id ? str_replace_last('/' . $api->model->id, '', $api->path) : $api->path;
        $data = Datatable::link($this, 'name', 'name', $path, true, 'file');
        $data = Datatable::icon($this, 'name', $api->meta->slug);
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
                'id' => 'name',
                'name' => trans('labels.name'),
                'order' => false,
            ],
            [
                'id' => 'size',
                'name' => trans('labels.size'),
                'order' => false,
            ],
        ];
    }

    public function dButtons($api)
    {
        $buttons = [];

        if (!$api->model->_parent->disableUpload) {
            $buttons = [
                'upload' => [
                    'url' => Helper::route('api.upload', $api->path),
                    'parameters' => 'data-multiple="1" data-file="1" data-upload',
                    'class' => 'btn-info',
                    'icon' => 'upload',
                    'name' => trans('buttons.upload'),
                    'visible' => Auth::user()->can('Upload Files'),
                ],
            ];

            if ($api->meta->_parent->model != 'task') {
                $buttons = array_merge($buttons, [
                    'create' => [
                        'url' => Helper::route('api.create', $api->path),
                        'class' => 'btn-success',
                        'icon' => 'plus',
                        'method' => 'get',
                        'name' => trans('buttons.folder'),
                        'visible' => Auth::user()->can('Create: Library'),
                    ],
                ]);
            }
        }

        $buttons = array_merge($buttons, [
            'edit' => [
                'url' => Helper::route('api.edit', $api->meta->slug),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Library'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->meta->slug),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Library'),
            ],
        ]);

        return $buttons;
    }

    public function dOrder($meta)
    {
        return [];
    }

    public function dData($api)
    {
        $data = $this->select(array_merge(array_column($this->dColumns(), 'id'), ['file', 'extension']))->where('parent', $api->model->id)->where('meta_id', $api->meta->_parent->id)->where('model_id', $api->model->_parent->id)->orderBy('file')->orderBy('name')->get();

        $path = $api->model->id ? str_replace_last('/' . $api->model->id, '', $api->path) : $api->path;
        $data = Datatable::link($data, 'name', 'name', $path, true, 'file');
        $data = Datatable::icon($data, 'name', $api->meta->slug);
        $data = Datatable::filesize($data, 'size');

        return $data;
    }

    public function uploadDirectory($api)
    {
        return $api->meta->_parent->id . DIRECTORY_SEPARATOR . $api->model->_parent->id . ($api->model->id ? DIRECTORY_SEPARATOR . $api->model->id : '');
    }

    public function datatableAdded($api, $request, $response = [])
    {
        $model = $api->model->create([
            'parent' => $api->model->id,
            'meta_id' => $api->meta->_parent->id,
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
        $name = Datatable::link($model, 'name', 'name', $path, true, 'file');
        $name = Datatable::icon($model, 'name', $api->meta->slug)->first()->name;

        $data = [
            'id' => $model->id,
            'name' => $name,
            'size' => Datatable::filesize($model, 'size')->first()->size,
        ];

        return $data;
    }
}
