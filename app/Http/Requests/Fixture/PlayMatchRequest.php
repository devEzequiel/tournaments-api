<?php

namespace App\Http\Requests\Fixture;

use Illuminate\Foundation\Http\FormRequest;

class PlayMatchRequest extends FormRequest
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
            'home_goals' => ['required', 'integer'],
            'away_goals' => ['required', 'integer'],
            'goals' => ['array', 'nullable'],
            'goals.scorer_id' => ['integer', 'nullable', 'exists:players,id'],
            'goals.assist_id' => ['integer', 'nullable', 'exists:players,id'],
            'rates' => ['array', 'nullable'],
            'rates.player_id' => ['integer', 'nullable', 'exists:players,id'],
            'rates.rate' => ['numeric', 'nullable'],
        ];
    }

    public function messages()
    {
        return [
            'goals.scorer_id.exists' => 'Marcador não existe.',
            'goals.assist_id.exists' => 'Assistente não existe.',
            'rates.player_id.exists' => 'Jogador não existe.'
        ];
    }
}
