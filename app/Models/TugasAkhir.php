<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasAkhir extends Model
{
    use HasFactory;

    protected $table = 'tugas_akhir';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_mahasiswa',
        'judul',
        'status',
    ];

    protected $casts = [
        'id_mahasiswa' => 'integer',
    ];

    // Konstanta status untuk konsistensi
    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    /**
     * Get status options for dropdown/select
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    /**
     * Relasi ke Mahasiswa
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    /**
     * Relasi ke Bimbingan
     */
    public function bimbingans()
    {
        return $this->hasMany(Bimbingan::class, 'id_ta');
    }

    /**
     * Relasi ke Revisi
     */
    public function revisi()
    {
        return $this->hasMany(Revisi::class, 'id_ta');
    }

    /**
     * Scope untuk tugas akhir yang disetujui
     */
    public function scopeDisetujui($query)
    {
        return $query->where('status', self::STATUS_DISETUJUI);
    }

    /**
     * Cek apakah status diajukan
     */
    public function isDiajukan(): bool
    {
        return $this->status === self::STATUS_DIAJUKAN;
    }

    /**
     * Cek apakah status disetujui
     */
    public function isDisetujui(): bool
    {
        return $this->status === self::STATUS_DISETUJUI;
    }

    /**
     * Cek apakah status ditolak
     */
    public function isDitolak(): bool
    {
        return $this->status === self::STATUS_DITOLAK;
    }
}