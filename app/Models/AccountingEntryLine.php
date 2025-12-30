<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingEntryLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_id',
        'account_id',
        'label',
        'debit',
        'credit',
        'cost_center',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    /**
     * Écriture parente
     */
    public function entry()
    {
        return $this->belongsTo(AccountingEntry::class, 'entry_id');
    }

    /**
     * Compte comptable
     */
    public function account()
    {
        return $this->belongsTo(AccountingAccount::class, 'account_id');
    }
}

