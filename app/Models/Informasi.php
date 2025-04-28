<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class informasi extends Model
{
    use HasFactory;

    protected $table = 'informasi';
    protected $primaryKey = 'id_informasi'; // Sesuai dengan migration

    protected $fillable = [
        'judul',
        'deskripsi',
        'tanggal_upload',
    ];

    protected $casts = [
        'tanggal_upload' => 'datetime', // Menggantikan $dates yang sudah deprecated
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Scope untuk informasi terbaru
     */
    public function scopeTerbaru($query)
    {
        return $query->orderBy('tanggal_upload', 'desc');
    }

    /**
     * Scope untuk pencarian judul
     */
    public function scopeCariJudul($query, $judul)
    {
        return $query->where('judul', 'like', '%'.$judul.'%');
    }

    /**
     * Accessor untuk memformat tanggal upload
     */
    public function getTanggalUploadFormattedAttribute()
    {
        return $this->tanggal_upload->format('d F Y H:i');
    }

    /**
     * Mutator untuk judul (trim dan ucfirst)
     */
    public function setJudulAttribute($value)
    {
        $this->attributes['judul'] = ucfirst(trim($value));
    }
}