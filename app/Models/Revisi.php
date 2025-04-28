<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revisi extends Model
{
    use HasFactory;

    protected $table = 'revisi';
    protected $primaryKey = 'id_revisi';

    protected $fillable = [
        'id_ta',
        'id_penguji', 
        'catatan_revisi',
        'status',
    ];

    protected $casts = [
        'id_ta' => 'integer',
        'id_penguji' => 'integer',
    ];

    // Status constants
    public const STATUS_MENUNGGU = 'Menunggu Verifikasi';
    public const STATUS_DISETUJUI = 'Disetujui';

    /**
     * Get status options for dropdown
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_MENUNGGU => 'Menunggu Verifikasi',
            self::STATUS_DISETUJUI => 'Disetujui',
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
     * Relationship with DosenPenguji
     * Diubah dari dosen_pembimbing ke dosen_penguji sesuai migration
     */
    public function penguji()
    {
        return $this->belongsTo(DosenPenguji::class, 'id_penguji', 'id_penguji');
    }

    /**
     * Scope for revisi by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if revisi is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_DISETUJUI;
    }

    /**
     * Check if revisi is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_MENUNGGU;
    }
}