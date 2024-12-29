<?php

namespace App\Http\Requests\Player;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlayerRequest extends FormRequest
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
            'name' => ['string', 'required', 'unique:players,name,' . $this->get('player_id'). ',id'],
            'team_id' => ['int', 'required', 'exists:teams,id'],
            'gk' => ['boolean', 'nullable', 'in:0,1']
        ];
    }
}
