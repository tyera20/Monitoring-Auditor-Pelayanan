<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Penugasan extends Model
{
    use HasFactory;

    protected $table = 'tr_penugasan';

    protected $fillable = [
        'nomor_surat_tugas',
        'layanan_id',
        'task_detail',
        'tempat',
        'komoditi',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(
            Layanan::class,
            'layanan_id'
        );
    }

    public function petugas(): BelongsToMany
    {
        return $this->belongsToMany(
            Petugas::class,
            'penugasan_petugas',
            'penugasan_id',
            'petugas_id'
        )->withTimestamps();
    }
}