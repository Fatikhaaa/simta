<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';
    protected $primaryKey = 'id_penilaian';

    protected $fillable = [
        'id_ta',
        'id_penguji',  
        'nilai',
        'catatan',
        'status_kelulusan',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'id_ta' => 'integer',
        'id_penguji' => 'integer',
    ];

    // Status kelulusan constants
    public const STATUS_LULUS = 'Lulus';
    public const STATUS_TIDAK_LULUS = 'Tidak Lulus';

    /**
     * Relationship with TugasAkhir
     */
    public function tugasAkhir()
    {
        return $this->belongsTo(TugasAkhir::class, 'id_ta');
    }

    /**
     * Relationship with DosenPenguji
     * Relasi ke model User dengan scope dosen penguji
     */
    public function penguji()
    {
        return $this->belongsTo(User::class, 'id_dosen_penguji')
                   ->where('role', 'dosen penguji');
    }

    /**
     * Scope for penilaian by penguji tertentu
     */
    public function scopeByPenguji($query, $pengujiId)
    {
        return $query->where('id_penguji', $pengujiId);
    }

    /**
     * Scope for penilaian lulus
     */
    public function scopeLulus($query)
    {
        return $query->where('status_kelulusan', self::STATUS_LULUS);
    }

    /**
     * Scope for penilaian tidak lulus
     */
    public function scopeTidakLulus($query)
    {
        return $query->where('status_kelulusan', self::STATUS_TIDAK_LULUS);
    }

    /**
     * Check if lulus
     */
    public function isLulus(): bool
    {
        return $this->status_kelulusan === self::STATUS_LULUS;
    }

    /**
     * Get formatted nilai
     */
    public function getNilaiFormattedAttribute(): string
    {
        return number_format($this->nilai, 2);
    }

    /**
     * Validasi bahwa penguji adalah dosen penguji
     */
    public function isValidPenguji(): bool
    {
        return $this->penguji !== null;
    }
}