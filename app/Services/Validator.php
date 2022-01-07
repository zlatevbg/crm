<?php

namespace App\Services;

use App\Models\Meta;

class Validator
{
    public function meta($attribute, $value, $parameters, $validator)
    {
        $query = Meta::leftJoin('meta_translations', 'meta_translations.meta_id', '=', 'meta.id')->where('meta_translations.slug', $value)->where('meta.parent', $parameters[0] ? $parameters[1] : NULL)->where('meta.domain_id', \Local::domain()->id)->where('meta_translations.locale', $parameters[3])->where('meta.model', $parameters[2]);

        if ($parameters[4]) {
            $query = $query->where('meta.model_id', '!=', $parameters[4]);
        }

        return !$query->count();
    }
}
