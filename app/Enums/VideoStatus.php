<?php

namespace App\Enums;

/**
 * pending   -> lesson baru, belum ada video
 * uploading -> server sedang menerima/menyimpan file
 * ready     -> video tersimpan dan bisa diputar
 * failed    -> upload gagal, admin bisa unggah ulang
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

    /** Warna badge di UI (lihat komponen x-badge). */
    public function color(): string
    {
        return match ($this) {
            self::Ready => 'green',
            self::Uploading => 'blue',
            self::Failed => 'red',
            self::Pending => 'amber',
        };
    }
}
