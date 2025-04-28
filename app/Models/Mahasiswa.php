<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';
    protected $primaryKey = 'id_mahasiswa'; // Tambahkan ini

    protected $fillable = [
        'id_pengguna',
        'nim',
        'nama',
        'program_studi',
        'no_telp',
    ];

    // Casting tipe data untuk konsistensi
    protected $casts = [
        'id_pengguna' => 'integer',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    // Scope untuk pencarian praktis
    public function scopeByNim($query, $nim)
    {
        return $query->where('nim', $nim);
    }

    public function scopeByProgramStudi($query, $programStudi)
    {
        return $query->where('program_studi', 'like', "%$programStudi%");
    }

    // Accessor untuk format nomor telepon
    public function getFormattedNoTelpAttribute()
    {
        $no = $this->no_telp;
        return substr($no, 0, 4).'-'.substr($no, 4, 4).'-'.substr($no, 8);
    }

    // Mutator untuk memastikan nim selalu uppercase
    public function setNimAttribute($value)
    {
        $this->attributes['nim'] = strtoupper($value);
    }
}