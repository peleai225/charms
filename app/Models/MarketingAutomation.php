<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingAutomation extends Model
{
    protected $fillable = [
        'name', 'description', 'trigger', 'channel', 'message_template',
        'delay_hours', 'conditions', 'is_active', 'sent_count', 'converted_count',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTrigger($query, string $trigger)
    {
        return $query->where('trigger', $trigger)->where('is_active', true);
    }

    public function getConversionRateAttribute(): float
    {
        return $this->sent_count > 0 ? round(($this->converted_count / $this->sent_count) * 100, 1) : 0;
    }

    public function buildMessage(array $variables = []): string
    {
        $msg = $this->message_template;
        foreach ($variables as $key => $value) {
            $msg = str_replace("{{$key}}", $value, $msg);
        }
        return $msg;
    }
}
