<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One row of Indonesia's administrative hierarchy (provinsi, kota/kabupaten,
 * kecamatan, or kelurahan/desa) — see the `regions` migration for the level
 * numbering and `regions:import` for where the data comes from.
 */
class Region extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $primaryKey = 'code';

    protected $fillable = ['code', 'parent_code', 'level', 'name', 'postal_code'];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
        ];
    }
}
