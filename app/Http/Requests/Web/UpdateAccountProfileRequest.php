<?php

declare(strict_types=1);

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    public function rules(): array
    {
        $userId = $this->user('web')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'sample_avatar_id' => ['nullable', 'integer', Rule::exists('sample_avatars', 'id')->where('is_active', true)],
            'avatar_frame_id' => ['nullable', 'integer', Rule::exists('avatar_frames', 'id')->where('is_active', true)],
            'remove_avatar' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username chỉ gồm chữ, số và gạch dưới.',
            'avatar.max' => 'Ảnh đại diện tối đa 2MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('remove_avatar')) {
            $this->merge([
                'remove_avatar' => filter_var($this->input('remove_avatar'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->input('avatar_frame_id') === '' || $this->input('avatar_frame_id') === 'none') {
            $this->merge(['avatar_frame_id' => null]);
        }

        if ($this->input('sample_avatar_id') === '') {
            $this->merge(['sample_avatar_id' => null]);
        }
    }
}
