<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_id',
        'entry_number',
        'entry_date',
        'label',
        'description',
        'reference_type',
        'reference_id',
        'document_number',
        'status',
        'fiscal_year',
        'fiscal_period',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'validated_at' => 'datetime',
    ];

    /**
     * Journal de cette écriture
     */
    public function journal()
    {
        return $this->belongsTo(AccountingJournal::class, 'journal_id');
    }

    /**
     * Lignes de cette écriture
     */
    public function lines()
    {
        return $this->hasMany(AccountingEntryLine::class, 'entry_id');
    }

    /**
     * Utilisateur créateur
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculer le total débit
     */
    public function getTotalDebitAttribute()
    {
        return $this->lines()->sum('debit');
    }

    /**
     * Calculer le total crédit
     */
    public function getTotalCreditAttribute()
    {
        return $this->lines()->sum('credit');
    }

    /**
     * Vérifier si l'écriture est équilibrée
     */
    public function isBalanced(): bool
    {
        return bccomp($this->total_debit, $this->total_credit, 2) === 0;
    }
}

