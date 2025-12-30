<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Types de comptes
     */
    const TYPES = [
        'asset' => 'Actif',
        'liability' => 'Passif',
        'equity' => 'Capitaux propres',
        'revenue' => 'Produits',
        'expense' => 'Charges',
    ];

    /**
     * Lignes d'écritures de ce compte
     */
    public function lines()
    {
        return $this->hasMany(AccountingEntryLine::class, 'account_id');
    }

    /**
     * Scope actif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtenir le libellé du type
     */
    public function getTypeLabelAttribute()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}

