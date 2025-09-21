<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_sekolah',
        'status_sekolah',
        'alamat_sekolah',
        'npsn',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'kepala_nama',
        'kepala_nip',
        'kepala_sk',
        'bendahara_nama',
        'bendahara_nip',
        'bendahara_sk',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
