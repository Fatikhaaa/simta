<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjadwalan extends Model
{
    use HasFactory;

    protected $table = 'penjadwalan';
    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'id_ta',
        'kategori',
        'tanggal',
        'waktu',
        'lokasi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu' => 'datetime:H:i:s',
    ];

    // Kategori constants
    public const KATEGORI_SIDANG = 'Sidang';
    public const KATEGORI_SEMINAR = 'Seminar';

    /**
     * Get kategori options for dropdown
     */
    public static function getKategoriOptions(): array
    {
        return [
            self::KATEGORI_SIDANG => 'Sidang',
            self::KATEGORI_SEMINAR => 'Seminar',
        ];
    }

    /**
     * Relationship with TugasAkhir
     */
    public function tugasAkhir()
    {
        return $this->belongsTo(TugasAkhir::class, 'id_ta');
    }

    /**
     * Scope for filtering by kategori
     */
    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Scope for upcoming schedules
     */
    public function scopeUpcoming($query)
    {
        return $query->where('tanggal', '>=', now()->toDateString())
                    ->orderBy('tanggal')
                    ->orderBy('waktu');
    }

    /**
     * Format tanggal dan waktu untuk display
     */
    public function getTanggalWaktuAttribute(): string
    {
        return $this->tanggal->format('d F Y') . ' ' . $this->waktu;
    }

    /**
     * Check if schedule is for Sidang
     */
    public function isSidang(): bool
    {
        return $this->kategori === self::KATEGORI_SIDANG;
    }

    /**
     * Check if schedule is for Seminar
     */
    public function isSeminar(): bool
    {
        return $this->kategori === self::KATEGORI_SEMINAR;
    }
}