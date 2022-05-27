<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'sometimes|required|integer',
            'name' => 'required|string|max:64',
            'keyword_set_id' => 'required|integer|exists:keyword_sets,id',
            'server_id' => 'required|integer',
            'sections_from' => ['required', 'numeric', 'min:1', 'max:' . $this->get('sections_to')],
            'sections_to' => ['required', 'numeric', 'min:' . $this->get('sections_from')],
            'words_from' => ['required', 'numeric', 'min:1', 'max:' . $this->get('words_to')],
            'words_to' => ['required', 'numeric', 'min:' . $this->get('words_from')],
            'keyword_density' => 'numeric|min:0|max:100|nullable',
            'schedule_interval' => 'numeric|min:0|nullable',
            'schedule_variance' => ['numeric', 'min:0', 'max:' . $this->get('schedule_interval'), 'nullable'],
            'splogs' => 'sometimes|array',
            'splogs.*.domain' => 'required|string|max:64',
            'splogs.*.server_id' => 'nullable|integer',
            'splogs.*.sections_from' => 'nullable|required_with:splogs.*.sections_to|numeric|min:1|lte:splogs.*.sections_to',
            'splogs.*.sections_to' => 'nullable|required_with:splogs.*.sections_from|numeric|gte:splogs.*.sections_from',
            'splogs.*.words_from' => 'nullable|required_with:splogs.*.words_to|numeric|min:1|lte:splogs.*.words_to',
            'splogs.*.words_to' => 'nullable|required_with:splogs.*.words_from|numeric|gte:splogs.*.words_from',
        ];
    }
}
