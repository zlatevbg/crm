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

class Post extends Model
{
    public $disks = [
        1 => 'www-mespil-ie',
        2 => 'www-pinehillsvilamoura-com',
        3 => 'www-portugal-golden-visa-pt',
    ];

    public $paths = [
        1 => 'posts',
        2 => 'posts',
        3 => 'insights',
    ];

    protected $fillable = [
        'published_at',
        'title',
        'slug',
        'description',
        'content',
        'website_id',
    ];

    public $_parent;
    public $subslug = 'images';

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Posts') && !Auth::user()->can('Edit: Posts') && !Auth::user()->can('Delete: Posts'));
    }

    public function getPublishedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setPublishedAtAttribute($value)
    {
        $this->attributes['published_at'] = $value ? Carbon::parse($value) : null;
    }

    public function upload($uploader)
    {
        $uploader->uploadDisk = $this->disks[$this->_parent->id];
        $uploader->uploadPath = $uploader->getDiskPath($this->disks[$this->_parent->id]);
        $uploader->isImage = true;
        $uploader->resize = true;
        $uploader->resizeWidth = config('upload.resizeMaxWidth');
        $uploader->resizeHeight = config('upload.resizeMaxHeight');
        $uploader->thumbnailSmall = true;
        $uploader->cropThumbnail = true;
        $uploader->allowedExtensions = config('upload.imageExtensions');

        return $uploader;
    }

    public function createRules($request, $api)
    {
        return [
            'published_at' => 'required|date|date_format:"d.m.Y"',
            'title' => ['required', 'max:255', new Slug($api->id)],
            'description' => 'present',
            'content' => 'present',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();

        $data['website_id'] = $api->model->_parent->id;
        $data['slug'] = str_slug($data['title']);

        return $data;
    }

    public function updateData($request)
    {
        $data = $request->all();

        $data['slug'] = str_slug($data['title']);

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
        $this->slug = null;
        $path = $api->model->id ? str_replace_last('/' . $api->model->id, '', $api->path) : $api->path;

        $data = Datatable::link($this, 'title', 'title', $path, true, null, 'id', '/images');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'published_at', 'published');
        $data = Datatable::render($data, 'published', ['sort' => ['published_at' => 'timestamp']]);

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
                'id' => 'published',
                'name' => trans('labels.publishedAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
            ],
            [
                'id' => 'title',
                'name' => trans('labels.title'),
                'order' => false,
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
                'visible' => Auth::user()->can('Create: Posts'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Posts'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Posts'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 0 : 1, 'desc'],
        ];
    }

    public function dData($api)
    {
        $posts = $this->select('id', 'title', 'published_at')->where('website_id', $api->model->_parent->id)->get();

        $data = Datatable::link($posts, 'title', 'title', $api->path, true, null, 'id', '/images');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'published_at', 'published');
        $data = Datatable::render($data, 'published', ['sort' => ['published_at' => 'timestamp']]);

        return $data;
    }
}
