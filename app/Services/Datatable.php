<?php

namespace App\Services;

use Carbon\Carbon;
use App\Services\Helper;
use App\Facades\Domain;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Datatable
{
    public static function concat($data, $column, $columns, $glue = ' ', $default = '')
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        return $data->map(function ($item, $index) use ($column, $columns, $glue, $default) {
            $item->$column = trim(implode($glue, array_reduce($columns, function ($carry, $key) use ($item) {
                $carry[] = $item->$key;
                return $carry;
            })));

            $item->$column = $item->$column ?: $default;

            return $item;
        });
    }

    public static function default($data, $column, $default = '')
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        return $data->map(function ($item, $index) use ($column, $default) {
            if (is_callable($default)) {
                $item->$column = call_user_func($default, $item);
            } else {
                $item->$column = $item->$column ?: $default;
            }

            return $item;
        });
    }

    public static function link($data, $column1, $column2, $slug, $force = false, $skip = null, $id = 'id', $append = '')
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        return $data->map(function ($item, $index) use ($column1, $column2, $slug, $force, $skip, $id, $append) {
            if (($force || $item->is_menu) && (!$skip || !$item->$skip)) {
                $icon = 'fa-folder';

                if ($item->users && $item->users->count()) {
                    $user = $item->users->first();
                    if (!is_null($user->pivot->viewed_at)) {
                        $icon = 'fa-folder-open';
                    }
                }

                $item->$column1 = '<a class="fa-left" href="' . url($slug, $item->slug ?: $item->{$id}) . $append . '"><i class="far ' . $icon . ' fa-fw fa-lg"></i>' . ($item->$column2 ?: ($item->name ?? '')) . '</a>';
            }

            return $item;
        });
    }

    public static function url($data, $column, $link, $url = null, $folder = false)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        return $data->map(function ($item, $index) use ($column, $link, $folder, $url) {
            if ($folder && $url) {
                $item->$column = '<a class="fa-left" href="' . url($url) . '/' . $item->id . '"><i class="far fa-folder fa-fw fa-lg"></i>' . $item->$link . '</a>';
            } else {
                $item->$column = '<a target="_blank" href="' . ($item->$link ?: $url . $item->slug) . '">' . ($item->$link ?: $url . $item->slug) . '</a>';
            }

            return $item;
        });
    }

    public static function render($data, $column, $render)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $data = $data->map(function ($item, $index) use ($column, $render) {
            $arr = [
                'display' => $item->$column,
            ];

            foreach ($render as $key => $value) {
                $val = null;

                if (is_array($value)) {
                    $parameters = current($value);
                    if (is_array($parameters)) {
                        $function = $parameters[0];
                        $val = Datatable::{camel_case('render_' . $function)}($item->{key($value)}, array_slice($parameters, 1));
                    } else {
                        $val = Datatable::{camel_case('render_' . $parameters)}($item->{key($value)});
                    }
                } else {
                    $val = $item->$value;
                }

                $arr[$key] = $val;
            }

            $item->$column = $arr;

            return $item;
        });

        return $data;
    }

    public static function renderTimestamp($date)
    {
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        return $date ? $date->timestamp : null;
    }

    public static function renderPad($value, $parameters)
    {
        return str_pad($value, $parameters[0], '0', constant('STR_PAD_' . strtoupper($parameters[1])));
    }

    public static function thumbnail($data, $column, $uploadDirectory, $id = false, $render = null, $disk = 'public')
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $url = asset(Storage::disk($disk)->url(str_replace(DIRECTORY_SEPARATOR, '/', $uploadDirectory)));

        $data = $data->map(function ($item, $index) use ($column, $url, $id, $disk, $uploadDirectory) {
            if ($item->file) {
                $html = '';

                if ($item->width) {
                    $html .= '<a data-id="' . $item->id . '" data-size="' . $item->width . 'x' . $item->height . '" class="photoswipe" href="' . $url . ($id ? '/' . $item->id : '') . '/' . $item->uuid . '/' . (Storage::disk($disk)->exists($uploadDirectory . ($id ? '/' . $item->id : '') . '/' . $item->uuid . '/' . $item->file) ? '' : config('upload.cropDirectory') . '/') . $item->file . '">';
                }

                $html .= '<img src="' . $url . ($id ? '/' . $item->id : '') . '/' . $item->uuid . '/' . config('upload.thumbnailDirectory') . '/' . $item->file . '" alt="">';

                if ($item->width) {
                    $html .= '</a>';
                }

                $item->$column = $html;
            }
            return $item;
        });

        if ($render) {
            return Datatable::render($data, $column, $render);
        }

        return $data;
    }

    public static function icon($data, $column, $slug, $only = 'file')
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $faIcons = [
            'jpg' => 'far fa-file-image',
            'png' => 'far fa-file-image',
            'gif' => 'far fa-file-image',
            'jpeg' => 'far fa-file-image',
            'pdf' => 'far fa-file-pdf',
            'doc' => 'far fa-file-word',
            'docx' => 'far fa-file-word',
            'xls' => 'far fa-file-excel',
            'xlsx' => 'far fa-file-excel',
            'txt' => 'far fa-file-alt',
            'zip' => 'far fa-file-archive',
            'rar' => 'far fa-file-archive',
            'ppt' => 'far fa-file-powerpoint',
            'pptx' => 'far fa-file-powerpoint',
            'mp4' => 'far fa-file-video',
            'mp3' => 'far fa-file-audio',
        ];

        $data = $data->map(function ($item, $index) use ($column, $slug, $only, $faIcons) {
            if ($item->$only) {
                $icon = $faIcons[$item->extension] ?? 'far fa-file';
                $item->$column = '<a class="fa-left" href="' . Helper::route('api.download', $slug . '/' . $item->id) . '"><i class="fa-fw fa-lg ' . $icon . '"></i>' . $item->$column . '</a>';
            }

            return $item;
        });

        return $data;
    }

    public static function filesize($data, $column)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $data = $data->map(function ($item, $index) use ($column) {
            $size = Helper::formatBytes($item->$column);
            $item->$column = $size ?: '';
            return $item;
        });

        return $data;
    }

    public static function price($data, $columns, $prefix = '&euro;', $suffix = null)
    {
        $columns = array_wrap($columns);

        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $data = $data->map(function ($item, $index) use ($columns, $prefix, $suffix) {
            foreach ($columns as $column) {
                if (is_numeric($item->$column)) {
                    $item->$column = $prefix . number_format($item->$column, 2, '.', ' ') . $suffix;
                }
            }
            return $item;
        });

        return $data;
    }

    public static function gender($data, $column, $gender = 'gender')
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $data = $data->map(function ($item, $index) use ($column, $gender) {
            $item->$column = '<span class="fa-left"><i class="fa-fw fas fa-' . (in_array($item->$gender, ['male', 'female']) ? $item->$gender : 'user') . '"></i>' . $item->$column . '</span>';

            return $item;
        });

        return $data;
    }

    public static function prefix($data, $column, $prefix = null, $cast = null)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $data = $data->map(function ($item, $index) use ($column, $prefix, $cast) {
            if ($cast) {
                $value = $item->$column;
                settype($value, $cast);
                $item->$column = $value ? $prefix . $item->$column : '';
            } else {
                $item->$column = $prefix . $item->$column;
            }

            return $item;
        });

        return $data;
    }

    public static function suffix($data, $column, $suffix = null, $cast = null)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $data = $data->map(function ($item, $index) use ($column, $suffix, $cast) {
            if ($cast) {
                $value = $item->$column;
                settype($value, $cast);
                $item->$column = $value ? $item->$column . $suffix : '';
            } else {
                $item->$column .= $suffix;
            }

            return $item;
        });

        return $data;
    }

    public static function format($data, $type, $format, $column1, $column2 = null)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $data = $data->map(function ($item, $index) use ($type, $format, $column1, $column2) {
            $item->{$column2 ?: $column1} = Datatable::{camel_case('format_' . $type)}($item->$column1, $format);

            return $item;
        });

        return $data;
    }

    public static function formatDate($date, $format)
    {
        if ($date && !$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        return $date ? $date->format($format) : null;
    }

    public static function status($data, $columns, $path)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $columns = collect(array_wrap($columns));

        $data = $data->map(function ($item, $index) use ($columns, $path) {
            foreach ($columns as $column) {
                if ($item->$column) {
                    $item->$column = '<a class="js-status" href="' . Helper::route('api.status', $path) . '?status=' . $column . '&id=' . $item->id . '&value=0"><i class="far fa-check-square fa-fw fa-lg"></i></a>';
                } else {
                    $item->$column = '<a class="js-status" href="' . Helper::route('api.status', $path) . '?status=' . $column . '&id=' . $item->id . '&value=1"><i class="far fa-square fa-fw fa-lg"></i></a>';
                }
            }

            return $item;
        });

        return $data;
    }

    public static function onoff($data, $columns, $column, $inverse = false)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $columns = collect(array_wrap($columns));

        $data = $data->map(function ($item, $index) use ($columns, $column, $inverse) {
            foreach ($columns as $col) {
                $status = $inverse ? $item->$col : !$item->$col;

                if ($status) {
                    $item->$column = '<i class="fas fa-ban fa-fw fa-lg text-danger"></i>';
                } else {
                    $item->$column = '<i class="fas fa-check fa-fw fa-lg text-success"></i>';
                }
            }

            return $item;
        });

        return $data;
    }

    public static function nl2br($data, $column)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        return $data->map(function ($item, $index) use ($column) {
            $item->$column = nl2br($item->$column);

            return $item;
        });
    }

    public static function trans($data, $column, $file, $prefix = '')
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        return $data->map(function ($item, $index) use ($column, $file, $prefix) {
            if (is_array($item->$column)) {
                $translations = [];
                foreach ($item->$column as $key => $value) {
                    $translations[$key] = $value ? trans($file . '.' . camel_case($prefix . str_replace('-', '_', $value))) : null;
                }

                $item->$column = collect($translations)->implode(', ');
            } else {
                $item->$column = $item->$column ? trans($file . '.' . camel_case($prefix . str_replace('-', '_', $item->$column))) : null;
            }

            return $item;
        });
    }

    public static function data($data, $columns)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $columns = collect($columns);

        return $data->map(function ($item, $index) use ($columns) {
            $item = $columns->flip()->merge(collect($item)->only($columns));
            return $item;
        });
    }

    public static function actions($api, $data)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        return $data->map(function ($item, $index) use ($api) {
            $actions = '';
            foreach ($api->model->dActions($api) as $key => $value) {
                if ((!$value['hide'] || ($value['hide'] && !$item->{$value['hide']})) && (!isset($value['visible']) || $value['visible'] === true)) {
                    $actions .= '<button id="button-' . $key . '"' . ((isset($value['parameters']) && str_contains($value['parameters'], 'data-ajax')) ? '' : ' data-target=".modal" data-toggle="modal"') . ' data-action="' . $value['url'] . '/' . $item->id . '" data-table="datatable-' . $api->meta->model . '" class="btn fa-left ' . $value['class'] . '" ' . ($value['parameters'] ?? '') . (isset($value['method']) ? ' data-method="' . $value['method'] . '"' : '') . '>' . (isset($value['icon']) ? '<i class="fas fa-' . $value['icon'] . '"></i>' : '') . $value['name'] . '</button>';
                }
            }

            $item->actions = $actions;

            return $item;
        });
    }

    public static function popover($data, $column1, $column2 = 'description', $position = 'before', $html = false)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        return $data->map(function ($item, $index) use ($column1, $column2, $position, $html) {
            $relation = null;
            if (str_contains($column2, '.')) {
                $arr = explode('.', $column2);
                $relation = $arr[0];
                $column = $arr[1];
            } else {
                $column = $column2;
            }

            $value = '';
            if ($relation && $item->$relation && $item->$relation->$column) {
                $value = $item->$relation->$column;
            } else if ($item->$column) {
                $value = $item->$column;
            }

            if ($value) {
                if (!$html) {
                    $value = nl2br($value);
                }

                if ($position == 'before') {
                    $item->$column1 = '<a tabindex="0" role="button" data-toggle="popover" data-trigger="click hover" data-placement="auto" data-html="true" data-content="' . e($value) . '"><i class="fas fa-info-circle fa-fw fa-lg"></i></a>' . $item->$column1 . '&nbsp;';
                } elseif ($position == 'after') {
                    $item->$column1 .= '&nbsp;<a tabindex="0" role="button" data-toggle="popover" data-trigger="click hover" data-placement="auto" data-html="true" data-content="' . e($value) . '"><i class="fas fa-info-circle fa-fw fa-lg"></i></a>';
                }
            }

            return $item;
        });
    }

    public static function relationship($data, $column1, $relationship = null, $column2 = 'name', $order = false)
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        $relationship = $relationship ?: $column1;

        return $data->map(function ($item, $index) use ($column1, $relationship, $column2, $order) {
            if ($order) {
                $item->$column1 = optional($item->$relationship()->orderBy('pivot_created_at', 'desc')->first())->$column2;
            } else {
                $item->$column1 = optional($item->$relationship)->$column2;
            }

            return $item;
        });
    }

    public static function sum($data, $column, $format = false, $prefix = '&euro;')
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        return $data->map(function ($item, $index) use ($column, $format, $prefix) {
            $item->$column = $item->where('parent', $item->id)->sum($column);

            if ($format) {
                $item->$column = $prefix . number_format($item->$column, 2, '.', ' ');
            }

            return $item;
        });
    }

    public static function replace($data, $column, $search = ' ', $replace = '-')
    {
        if (!$data instanceof Collection) {
            $data = collect([$data]);
        }

        return $data->map(function ($item, $index) use ($column, $search, $replace) {
            $item->$column = str_replace($search, $replace, $item->$column);

            return $item;
        });
    }
}
