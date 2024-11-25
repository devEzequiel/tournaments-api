<?php

namespace App\Http\Requests\Player;

use Illuminate\Foundation\Http\FormRequest;

class CreatePlayerRateRequest extends FormRequest
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
            'fixture_id' => ['integer', 'required', 'exists:fixtures,id'],
            'player_id' => ['integer', 'required', 'exists:players,id'],
            'rate' => ['numeric', 'required', 'between:0,10'],
        ];
    }
}
