<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DosenPenguji extends Model
{
    use HasFactory;

    protected $table = 'dosen_penguji';
    protected $primaryKey = 'id_penguji';

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
        'kategori' => 'string',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    // Scope untuk pencarian berdasarkan kategori
    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    // Scope untuk pencarian berdasarkan NIP
    public function scopeByNip($query, $nip)
    {
        return $query->where('nip', $nip);
    }

    // Accessor untuk formatted no telp
    public function getFormattedNoTelpAttribute()
    {
        $no = $this->no_telp;
        if (strlen($no) === 12) {
            return substr($no, 0, 4).'-'.substr($no, 4, 4).'-'.substr($no, 8);
        }
        return $no;
    }

    // Mutator untuk memastikan NIP uppercase
    public function setNipAttribute($value)
    {
        $this->attributes['nip'] = strtoupper($value);
    }

    // Mutator untuk memastikan email lowercase
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }
}