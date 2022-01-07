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

class Article extends Model
{
    public $disks = [
        1 => 'www-mespil-ie',
        2 => 'www-pinehillsvilamoura-com',
        3 => 'www-portugal-golden-visa-pt',
    ];

    public $paths = [
        1 => 'articles',
        2 => 'articles',
        3 => 'news',
    ];

    public $urls = [
        1 => 'https://www.mespil.ie/news/',
        2 => 'https://www.pinehillsvilamoura.com/news/',
        3 => 'https://www.portugal-golden-visa.pt/news/',
    ];

    protected $fillable = [
        'published_at',
        'title',
        'slug',
        'meta_title',
        'meta_description',
        'content',
        'link',
        'website_id',
    ];

    public $_parent;
    public $subslug = 'images';

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Articles') && !Auth::user()->can('Edit: Articles') && !Auth::user()->can('Delete: Articles'));
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
        $uploader->crop = true;
        $uploader->cropUpsize = true;
        $uploader->cropWidth = config('upload.newsCropWidth');
        $uploader->cropHeight = config('upload.newsCropHeight');
        $uploader->allowedExtensions = config('upload.imageExtensions');

        if ($this->_parent->id == 3) {
            $uploader->thumbnailSmall = true;
            $uploader->cropThumbnail = true;
        }

        return $uploader;
    }

    public function createRules($request, $api)
    {
        return [
            'published_at' => 'required|date|date_format:"d.m.Y"',
            'title' => 'required|max:255',
            'slug' => ['nullable', 'present', 'max:255', new Slug($api->model->id)],
            'meta_title' => 'required|max:70',
            'meta_description' => 'required|max:160',
            'link' => 'nullable|present|url',
            'content' => 'nullable|present',
        ];
    }

    public function storeData($api, $request)
    {
        $data = $request->all();

        $data['website_id'] = $api->model->_parent->id;
        $data['slug'] = str_slug($data['slug'] ?: $data['title']);

        return $data;
    }

    public function updateData($request)
    {
        $data = $request->all();

        $data['slug'] = str_slug($data['slug'] ?: $data['title']);

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

        $data = Datatable::status($this, 'is_hidden', $api->path);
        $data = Datatable::link($data, 'title', 'title', $path, true, null, 'id', '/images');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'published_at', 'published');
        $data = Datatable::render($data, 'published', ['sort' => ['published_at' => 'timestamp']]);
        $data = Datatable::url($data, 'link', 'link', $this->urls[$api->model->_parent->id]);

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
            [
                'id' => 'link',
                'name' => trans('labels.link'),
                'order' => false,
            ],
            [
                'id' => 'is_hidden',
                'name' => trans('labels.isHidden'),
                'order' => false,
                'class' => 'text-center',
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
                'visible' => Auth::user()->can('Create: Articles'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Articles'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Articles'),
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
        $articles = $this->select('id', 'is_hidden', 'title', 'published_at', 'link', 'slug')->where('website_id', $this->_parent->id)->get();

        $data = Datatable::status($articles, 'is_hidden', $api->path);
        $data = Datatable::link($data, 'title', 'title', $api->path, true, null, 'id', '/images');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'published_at', 'published');
        $data = Datatable::render($data, 'published', ['sort' => ['published_at' => 'timestamp']]);
        $data = Datatable::url($data, 'link', 'link', $this->urls[$this->_parent->id]);

        return $data;
    }
}
