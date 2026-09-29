<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    protected $table = 'ms_petugas';

    protected $fillable = [
        'name',
        'nip',
        'position',
    ];

    public function penugasans(): BelongsToMany
    {
        return $this->belongsToMany(Penugasan::class, 'penugasan_petugas', 'petugas_id', 'penugasan_id');
    }
}
