<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'label', 'recipient_name', 'phone', 'address_line',
        'kelurahan', 'kecamatan', 'kota', 'provinsi', 'postal_code', 'note',
        'latitude', 'longitude', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line, $this->kelurahan, $this->kecamatan,
            $this->kota, $this->provinsi, $this->postal_code,
        ])->filter()->implode(', ');
    }
}
