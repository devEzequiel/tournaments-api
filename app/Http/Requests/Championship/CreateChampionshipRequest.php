<?php

namespace App\Http\Requests\Championship;

use Illuminate\Foundation\Http\FormRequest;

class CreateChampionshipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'name' => ['string', 'required', 'max:255', 'unique:championships,name'],
            'description' => ['string', 'nullable'],
            'rounds' => ['required', 'integer', 'min:1', 'max:20'],
            'playoffs' => ['boolean', 'required'],
            'playoff_type' => ['nullable', 'required_if:playoffs,true', 'in:final,semifinal'],
            'teams' => ['array', 'required', 'min:2'],
            'teams.*' => ['required', 'integer', 'distinct', 'exists:teams,id'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                if (! $this->boolean('playoffs')) {
                    return;
                }

                $minimumTeams = $this->input('playoff_type') === 'semifinal' ? 4 : 2;

                if (count($this->input('teams', [])) < $minimumTeams) {
                    $validator->errors()->add(
                        'teams',
                        "Este formato de playoff exige pelo menos {$minimumTeams} times."
                    );
                }
            },
        ];
    }
}
