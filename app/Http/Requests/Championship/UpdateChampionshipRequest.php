<?php

namespace App\Http\Requests\Championship;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChampionshipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'required'],
            'description' => ['string', 'nullable'],
            'rounds' => ['numeric', 'required'],
            'playoffs' => ['boolean', 'required']
        ];
    }
}
