<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'sometimes|required|integer',
            'name' => 'required|string|max:64',
            'value' => 'nullable|string',
        ];
    }
}
