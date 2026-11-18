<?php

namespace App\Http\Requests\Admin;

use App\Enums\CourseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi tambah & ubah course (dipakai store dan update). */
class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => is_string($this->title) ? trim($this->title) : $this->title,
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'status' => ['required', Rule::enum(CourseStatus::class)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'title' => 'judul',
            'description' => 'deskripsi',
            'status' => 'status publikasi',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'min' => ':Attribute minimal :min karakter.',
            'max' => ':Attribute maksimal :max karakter.',
            'status.enum' => 'Status publikasi tidak valid.',
        ];
    }
}
