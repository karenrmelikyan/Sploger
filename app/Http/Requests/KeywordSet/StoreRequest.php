<?php

declare(strict_types=1);

namespace App\Http\Requests\KeywordSet;

use App\Models\KeywordSet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;

class StoreRequest extends FormRequest
{
    #[ArrayShape(['id' => 'string', 'name' => 'string', 'keywords' => 'string'])]
    public function rules(): array
    {
        $rules = [
            'id' => 'sometimes|required|integer',
            'name' => [
                'required',
                'string',
                'max:64',
            ],
            'language_code' => 'required|string:|size:2',
            'keywords' => 'required|string',
        ];

        if ($this->has('id')) {
            $rules['name'][] = Rule::unique(KeywordSet::class)->ignore($this->get('id'));
        } else {
            $rules['name'][] = Rule::unique(KeywordSet::class);
        }

        return $rules;
    }
}
