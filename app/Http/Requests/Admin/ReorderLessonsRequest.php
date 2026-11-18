<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Urutan baru lesson dalam satu course. Daftar id wajib berisi
 * SEMUA lesson milik course tersebut (tidak kurang, tidak lebih, tanpa duplikat).
 */
class ReorderLessonsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'lessons' => ['required', 'array'],
            'lessons.*' => ['integer', 'distinct'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $expected = $this->route('course')->lessons()->pluck('id')->sort()->values()->all();
                $given = collect($this->input('lessons'))->map(fn ($id) => (int) $id)->sort()->values()->all();

                if ($expected !== $given) {
                    $validator->errors()->add('lessons', 'Daftar lesson tidak sesuai dengan isi course. Muat ulang halaman lalu coba lagi.');
                }
            },
        ];
    }
}
