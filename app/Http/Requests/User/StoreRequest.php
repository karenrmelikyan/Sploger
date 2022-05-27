<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;

class StoreRequest extends FormRequest
{
    #[ArrayShape(['id' => 'string', 'name' => 'string', 'email' => "string", 'password' => "string"])]
    public function rules(): array
    {
        $rules = [
            'id' => 'sometimes|required|integer',
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:64',
            ],
            'password' => 'required|string|min:8',
        ];

        if ($this->has('id')) {
            $rules['email'][] = Rule::unique(User::class)->ignore($this->get('id'));
        } else {
            $rules['email'][] = Rule::unique(User::class);
            $rules['password_confirmation'] = 'required|string|min:8';
        }

        return $rules;
    }

    public function validator(Factory $factory): Validator
    {
        $validator = $this->createDefaultValidator($factory);
        $validator->sometimes('password', 'confirmed', function ($input) {
            return $input->id === null;
        });

        return $validator;
    }
}
