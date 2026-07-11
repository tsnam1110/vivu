<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ContributionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContributionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in([
                ContributionStatus::Approved->value,
                ContributionStatus::Rejected->value,
                ContributionStatus::Pending->value,
            ])],
            'set_canonical' => ['nullable', 'boolean'],
            'review_note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
