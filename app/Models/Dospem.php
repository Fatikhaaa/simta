<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dospem extends Model
{
    use HasFactory;

    protected $table = 'dosen_pembimbing';
    protected $primaryKey = 'id_pembimbing';

    protected $fillable = [
        'id_pengguna',
        'nip',
        'nama',
        'kategori',
        'no_telp',
        'email',
    ];

    protected $casts = [
        'id_pengguna' => 'integer',
        'kategori' => 'string', // atau bisa dibuat custom enum casting
    ];

    /**
     * Relasi ke model User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    /**
     * Scope untuk mencari berdasarkan kategori
     */
    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Scope untuk mencari berdasarkan NIP
     */
    public function scopeByNip($query, $nip)
    {
        return $query->where('nip', $nip);
    }

    /**
     * Accessor untuk format nomor telepon
     */
    public function getFormattedNoTelpAttribute()
    {
        $no = preg_replace('/[^0-9]/', '', $this->no_telp);
        if (strlen($no) > 4) {
            return substr($no, 0, 4) . '-' . substr($no, 4);
        }
        return $no;
    }

    /**
     * Mutator untuk memastikan NIP selalu uppercase
     */
    public function setNipAttribute($value)
    {
        $this->attributes['nip'] = strtoupper($value);
    }

    /**
     * Mutator untuk memastikan email lowercase
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Check jika dosen adalah pembimbing 1
     */
    public function isPembimbing1(): bool
    {
        return $this->kategori === 'Pembimbing 1';
    }

    /**
     * Check jika dosen adalah pembimbing 2
     */
    public function isPembimbing2(): bool
    {
        return $this->kategori === 'Pembimbing 2';
    }
}