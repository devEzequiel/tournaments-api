<?php

namespace App\Http\Requests\Player;

use Illuminate\Foundation\Http\FormRequest;

class ChangePlayerTeamRequest extends FormRequest
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
            'player_id' => ['string', 'required', 'exists:players,id'],
            'team_id' => ['int', 'required', 'exists:teams,id'],
        ];
    }
}
