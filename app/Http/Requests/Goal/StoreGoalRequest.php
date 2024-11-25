<?php

namespace App\Http\Requests\Goal;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'fixture_id' => ['required', 'integer', 'exists:fixtures,id'],
            'scorer_id' => ['required',  'integer', 'exists:players,id'],
            'assist_id' => ['required',  'integer', 'exists:players,id'],
            'pk' => ['required', 'boolean'],
        ];
    }
}
