<?php

// Terjemahan pesan validasi yang dipakai di aplikasi ini.
// Aturan lain otomatis jatuh ke bahasa Inggris (fallback_locale).
return [
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'integer' => ':Attribute harus berupa angka.',
    'array' => ':Attribute tidak valid.',
    'distinct' => ':Attribute memiliki nilai duplikat.',
    'exists' => ':Attribute tidak ditemukan.',
    'enum' => ':Attribute tidak valid.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'unique' => ':Attribute sudah digunakan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'file' => ':Attribute harus berupa file.',
    'mimetypes' => ':Attribute harus berupa file bertipe: :values.',
    'min' => [
        'string' => ':Attribute minimal :min karakter.',
    ],
    'max' => [
        'string' => ':Attribute maksimal :max karakter.',
        'file' => ':Attribute maksimal :max KB.',
    ],
    'size' => [
        'array' => ':Attribute harus berisi :size item.',
    ],

    'attributes' => [
        'email' => 'email',
        'password' => 'password',
        'name' => 'nama',
    ],
];
