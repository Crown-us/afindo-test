<?php

namespace App\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Warna badge di UI (lihat komponen x-badge). */
    public function color(): string
    {
        return $this === self::Published ? 'green' : 'gray';
    }
}
