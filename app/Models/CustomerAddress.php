<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'is_default',
        'first_name',
        'last_name',
        'company',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'postal_code',
        'state',
        'country',
        'delivery_instructions',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // ========== RELATIONS ==========

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // ========== ACCESSORS ==========

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = [];

        if ($this->company) {
            $parts[] = $this->company;
        }
        $parts[] = $this->full_name;
        $parts[] = $this->address_line1;
        if ($this->address_line2) {
            $parts[] = $this->address_line2;
        }
        $parts[] = $this->postal_code . ' ' . $this->city;
        if ($this->state) {
            $parts[] = $this->state;
        }
        $parts[] = $this->getCountryName();

        return implode("\n", $parts);
    }

    public function getFormattedAddressHtmlAttribute(): string
    {
        return nl2br($this->formatted_address);
    }

    // ========== METHODS ==========

    public function setAsDefault(): void
    {
        // Retirer le statut default des autres adresses du même type
        static::where('customer_id', $this->customer_id)
            ->where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    public function getCountryName(): string
    {
        $countries = [
            'FR' => 'France',
            'BE' => 'Belgique',
            'CH' => 'Suisse',
            'LU' => 'Luxembourg',
            'DE' => 'Allemagne',
            'ES' => 'Espagne',
            'IT' => 'Italie',
            'GB' => 'Royaume-Uni',
        ];

        return $countries[$this->country] ?? $this->country;
    }
}

