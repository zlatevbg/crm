<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Helper;
use App\Services\Datatable;

class ModalLibrary extends Model
{
    protected $table = 'library';

    protected $libraries = [
        3 => 'first_name, last_name',
        4 => 'name, city, country',
        5 => 'company, first_name, last_name',
    ];

    public function dColumns($api)
    {
        if ($api->subid) {
            $columns = [
                [
                    'id' => 'id',
                    'checkbox' => true,
                    'order' => false,
                ],
                [
                    'id' => 'name',
                    'name' => trans('labels.name'),
                    'order' => false,
                ], [
                    'id' => 'size',
                    'name' => trans('labels.size'),
                    'order' => false,
                ],
            ];
        } else {
            $columns = [
                [
                    'id' => 'fullname',
                    'name' => trans('labels.name'),
                ],
            ];
        }

        return $columns;
    }

    public function dData($api)
    {
        if ($api->subid) {
            $data = $this->select(array_merge(array_column($this->dColumns($api), 'id'), ['file', 'extension']))->where('parent', $api->model->id)->where('meta_id', $api->submeta->id)->where('model_id', $api->submodel->id)->orderBy('file')->orderBy('name')->get();

            $path = $api->model->id ? str_replace_last('/' . $api->model->id, '', $api->path) : $api->path;
            // $data = Datatable::link($data, 'name', 'name', $path . '?library=' . $api->submeta->slug . '/' . $api->submodel->id, true, 'file');
            $data = Datatable::icon($data, 'name', $api->meta->slug);
            $data = Datatable::filesize($data, 'size');
        } elseif ($api->submeta) {
            $data = $api->submodel->selectRaw('id, ' . $this->libraries[$api->submeta->id])->get();
            $data = Datatable::url($data, 'fullname', 'fullname', 'modal-library?library=' . $api->submeta->slug, true);
        } else {
            $data = [];
            $libraries = Meta::whereIn('id', [3, 4, 5])->orderBy('title')->get();
            foreach ($libraries as $library) {
                array_push($data, [
                    'id' => $library->id,
                    'fullname' => '<a class="fa-left" href="' . secure_url('modal-library') . '?library=' . $library->slug . '"><i class="far fa-folder fa-fw fa-lg"></i>' . $library->title . '</a>',
                ]);
            }
        }

        return $data;
    }
}
