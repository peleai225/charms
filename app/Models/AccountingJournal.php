<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * Écritures de ce journal
     */
    public function entries()
    {
        return $this->hasMany(AccountingEntry::class, 'journal_id');
    }
}

