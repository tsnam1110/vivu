<?php

declare(strict_types=1);

namespace App\Http\Requests\Web;

use App\Models\UserHabitItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserHabitItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var UserHabitItem|null $item */
        $item = $this->route('userHabitItem');
        $user = $this->user('web');

        return $user?->isActive() === true
            && $item instanceof UserHabitItem
            && $item->user_id === $user->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            // Allow current icon even if not in preset (e.g. adopted from admin template).
            'icon' => ['nullable', 'string', 'max:16'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
