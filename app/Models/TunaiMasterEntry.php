<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TunaiMasterEntry extends Model
{
    use HasFactory;

    protected $table = 'tunai_master_entries';

    protected $fillable = [
        'user_id',
        'tanggal',
        'kode_kegiatan',
        'kode_rekening',
        'no_bukti',
        'uraian',
        'penerimaan',
        'pengeluaran',
        'saldo',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'penerimaan' => 'decimal:3', // Keep 3 decimal precision for accuracy
        'pengeluaran' => 'decimal:3',
        'saldo' => 'decimal:3',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
