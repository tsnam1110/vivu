<?php

declare(strict_types=1);

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class ChooseWhatToEatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');

        return $user !== null && $user->isActive();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'log_id' => ['required', 'integer', 'exists:meal_suggestion_logs,id'],
            'dish_id' => ['required', 'integer', 'exists:dishes,id'],
        ];
    }
}
