<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'ms_layanan';

    protected $fillable = [
        'nama_layanan',
    ];

    public function penugasans(): HasMany
    {
        return $this->hasMany(Penugasan::class);
    }
}
