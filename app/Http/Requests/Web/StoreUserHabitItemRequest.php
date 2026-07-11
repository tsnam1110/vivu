<?php

declare(strict_types=1);

namespace App\Http\Requests\Web;

use App\Models\UserHabitItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUserHabitItemRequest extends FormRequest
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
            'mode' => ['required', 'in:template,custom,starters'],
            'template_habit_item_id' => ['required_if:mode,template', 'nullable', 'integer', 'exists:habit_items,id'],
            'name' => ['required_if:mode,custom', 'nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:16', Rule::in(UserHabitItem::ICONS)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($this->input('mode') === 'custom' && trim((string) $this->input('name', '')) === '') {
                $v->errors()->add('name', 'Nhập tên đầu mục tuỳ chỉnh.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'template_habit_item_id' => 'mẫu',
            'name' => 'tên đầu mục',
        ];
    }
}
