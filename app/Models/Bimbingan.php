<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bimbingan extends Model
{
    use HasFactory;

    protected $table = 'bimbingan';
    protected $primaryKey = 'id_bimbingan';

    protected $fillable = [
        'id_ta',
        'id_dosen',
        'tanggal',
        'catatan',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'id_ta' => 'integer',
        'id_dosen' => 'integer',
    ];

    // Status constants for better code readability
    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    /**
     * Get the status options for dropdown/select
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_MENUNGGU => 'Menunggu',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_DITOLAK => 'Ditolak',
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
     * Relationship with DosenPembimbing
     */
    public function dosenPembimbing()
    {
        return $this->belongsTo(Dospem::class, 'id_pembimbing');
    }

    /**
     * Scope for bimbingan by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for bimbingan by dosen
     */
    public function scopeByDosen($query, $dosenId)
    {
        return $query->where('id_pembimbing', $dosenId);
    }

    /**
     * Scope for bimbingan by tugas akhir
     */
    public function scopeByTugasAkhir($query, $taId)
    {
        return $query->where('id_ta', $taId);
    }

    /**
     * Check if bimbingan is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_DISETUJUI;
    }

    /**
     * Check if bimbingan is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_DITOLAK;
    }

    /**
     * Check if bimbingan is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_MENUNGGU;
    }
}