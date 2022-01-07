<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use App\Rules\Slug;
use App\Models\Library;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Gallery extends Model
{
    public $disks = [
        2 => 'www-pinehillsvilamoura-com',
    ];

    public $paths = [
        2 => 'galleries',
    ];

    protected $fillable = [
        'gallery',
        'website_id',
    ];

    public $_parent;
    public $subslug = 'images';

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Galleries') && !Auth::user()->can('Edit: Galleries') && !Auth::user()->can('Delete: Galleries'));
    }

    public function upload($uploader)
    {
        $uploader->uploadDisk = $this->disks[$this->_parent->id];
        $uploader->uploadPath = $uploader->getDiskPath($this->disks[$this->_parent->id]);
        $uploader->isImage = true;
        $uploader->resize = true;
        $uploader->resizeWidth = config('upload.resizeMaxWidth');
        $uploader->resizeHeight = config('upload.resizeMaxHeight');
        $uploader->thumbnailExtraSmall = true;
        $uploader->cropThumbnail = true;
        $uploader->allowedExtensions = config('upload.imageExtensions');

        return $uploader;
    }

    public function createRules($request, $api)
    {
        return [
            'gallery' => ['required', 'max:255', new Slug($api->id)],
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();

        $data['website_id'] = $api->model->_parent->id;

        return $data;
    }

    public function postDestroy($api, $ids, $rows)
    {
        Library::where('parent', $api->model->_parent->id)->where('meta_id', $api->meta->id)->whereIn('model_id', $ids)->delete();

        foreach ($ids as $id) {
            Storage::disk($this->disks[$api->model->_parent->id])->deleteDirectory($this->paths[$api->model->_parent->id] . DIRECTORY_SEPARATOR . $id);
        }
    }

    public function datatable($api)
    {
        $path = $api->model->id ? str_replace_last('/' . $api->model->id, '', $api->path) : $api->path;

        $data = Datatable::link($this, 'gallery', 'gallery', $path, true, null, 'id', '/images');

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
                'id' => 'gallery',
                'name' => trans('labels.gallery'),
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
                'visible' => Auth::user()->can('Create: Galleries'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Galleries'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Galleries'),
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
        $galleries = $this->where('website_id', $api->model->_parent->id)->get();

        $data = Datatable::link($galleries, 'gallery', 'gallery', $api->path, true, null, 'id', '/images');

        return $data;
    }
}
