<?php

namespace App\Enums;

/**
 * Siklus hidup video sebuah lesson.
 *
 * pending   -> lesson dibuat, belum ada video
 * uploading -> request upload sedang diproses server
 * ready     -> file tersimpan & tervalidasi, siap diputar
 * failed    -> upload/validasi gagal, admin bisa upload ulang
 */
enum VideoStatus: string
{
    case Pending = 'pending';
    case Uploading = 'uploading';
    case Ready = 'ready';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Belum ada video',
            self::Uploading => 'Mengunggah',
            self::Ready => 'Siap',
            self::Failed => 'Gagal',
        };
    }

    public function isPlayable(): bool
    {
        return $this === self::Ready;
    }
}
