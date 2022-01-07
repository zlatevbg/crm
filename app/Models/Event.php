<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;
use App\Models\Library;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Event extends Model
{
    public $disks = [
        2 => 'www-pinehillsvilamoura-com',
    ];

    public $paths = [
        2 => 'events',
    ];

    protected $fillable = [
        'start_at',
        'end_at',
        'month',
        'company',
        'time',
        'address',
        'type',
        'title',
        'description',
        'link',
        'website_id',
    ];

    public $_parent;
    public $subslug = 'images';

    public function isNotInteractable()
    {
        return !Auth::user()->can('Select Datatable Rows') || (!Auth::user()->can('Create: Events') && !Auth::user()->can('Edit: Events') && !Auth::user()->can('Delete: Events'));
    }

    public function getStartAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setStartAtAttribute($value)
    {
        $this->attributes['start_at'] = $value ? Carbon::parse($value) : null;
    }

    public function getEndAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setEndAtAttribute($value)
    {
        $this->attributes['end_at'] = $value ? Carbon::parse($value) : null;
    }

    public function selectMonths()
    {
        $months = [];
        foreach (trans('labels.months') as $key => $value) {
            $months[$key] = $value;
        }


        return $months;
    }

    public function upload($uploader)
    {
        $uploader->uploadDisk = $this->disks[$this->_parent->id];
        $uploader->uploadPath = $uploader->getDiskPath($this->disks[$this->_parent->id]);
        $uploader->isImage = true;
        $uploader->crop = true;
        $uploader->cropUpsize = true;
        $uploader->cropWidth = config('upload.thumbnailMediumWidth');
        $uploader->cropHeight = config('upload.thumbnailMediumHeight');
        $uploader->allowedExtensions = config('upload.imageExtensions');

        return $uploader;
    }

    public function createRules($request, $api)
    {
        return [
            'title' => 'required|max:255',
            'month' => 'required|numeric|min:1|max:12',
            'start_at' => 'present|nullable|date|date_format:"d.m.Y"',
            'end_at' => 'present|nullable|date|date_format:"d.m.Y"',
            'company' => 'present|nullable|max:255',
            'time' => 'present|nullable|max:255',
            'address' => 'present|nullable|max:255',
            'type' => 'present|nullable|max:255',
            'description' => 'present',
            'link' => 'required|url',
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

        $data = Datatable::link($this, 'title', 'title', $path, true, null, 'id', '/images');
        $data = Datatable::trans($data, 'month', 'labels.months');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'start_at', 'start');
        $data = Datatable::render($data, 'start', ['sort' => ['start_at' => 'timestamp']]);
        $data = Datatable::format($data, 'date', 'd.m.Y', 'end_at', 'end');
        $data = Datatable::render($data, 'end', ['sort' => ['end_at' => 'timestamp']]);

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
                'id' => 'title',
                'name' => trans('labels.title'),
                'order' => false,
            ],
            [
                'id' => 'month',
                'name' => trans('labels.month'),
            ],
            [
                'id' => 'start',
                'name' => trans('labels.startAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
            ],
            [
                'id' => 'end',
                'name' => trans('labels.endAt'),
                'render' =>  ['sort'],
                'class' => 'vertical-center',
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
                'visible' => Auth::user()->can('Create: Events'),
            ],
            'edit' => [
                'url' => Helper::route('api.edit', $api->path),
                'parameters' => 'disabled data-disabled="1" data-append-id',
                'class' => 'btn-warning',
                'icon' => 'edit',
                'method' => 'get',
                'name' => trans('buttons.edit'),
                'visible' => Auth::user()->can('Edit: Events'),
            ],
            'delete' => [
                'url' => Helper::route('api.delete', $api->path),
                'parameters' => 'disabled data-disabled',
                'class' => 'btn-danger',
                'icon' => 'trash',
                'method' => 'get',
                'name' => trans('buttons.delete'),
                'visible' => Auth::user()->can('Delete: Events'),
            ],
        ];
    }

    public function dOrder($api)
    {
        return [
            [$this->isNotInteractable() ? 1 : 2, 'asc'],
            [$this->isNotInteractable() ? 2 : 3, 'asc'],
        ];
    }

    public function dData($api)
    {
        $events = $this->select('id', 'title', 'month', 'start_at', 'end_at')->where('website_id', $api->model->_parent->id)->get();

        $data = Datatable::link($events, 'title', 'title', $api->path, true, null, 'id', '/images');
        $data = Datatable::trans($data, 'month', 'labels.months');
        $data = Datatable::format($data, 'date', 'd.m.Y', 'start_at', 'start');
        $data = Datatable::render($data, 'start', ['sort' => ['start_at' => 'timestamp']]);
        $data = Datatable::format($data, 'date', 'd.m.Y', 'end_at', 'end');
        $data = Datatable::render($data, 'end', ['sort' => ['end_at' => 'timestamp']]);

        return $data;
    }
}
