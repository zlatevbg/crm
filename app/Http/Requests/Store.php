<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use App\Services\Api;

class Store extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(Request $request)
    {
        $api = new Api($request->route('path'));

        if (in_array($this->method(), ['PUT', 'PATCH']) && method_exists($api->model, 'updateRules')) {
            return $api->model->updateRules($this, $api);
        } else {
            return $api->model->createRules($this, $api);
        }
    }

    public function messages()
    {
        $api = new Api(Request::route('path'));

        if (method_exists($api->model, 'validationMessages')) {
            return $api->model->validationMessages($this, $api);
        }

        return [];
    }
}
