<?php

declare(strict_types=1);

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class CycleHabitCellRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web')?->isActive() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_habit_item_id' => ['required', 'integer', 'exists:user_habit_items,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_habit_item_id' => 'đầu mục',
            'date' => 'ngày',
        ];
    }
}
