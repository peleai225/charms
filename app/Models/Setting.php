<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public $timestamps = false;

    /**
     * Récupérer une valeur de paramètre
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Définir une valeur de paramètre
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Récupérer plusieurs paramètres
     */
    public static function getMany(array $keys): array
    {
        return static::whereIn('key', $keys)->pluck('value', 'key')->toArray();
    }

    /**
     * Récupérer tous les paramètres sous forme de tableau
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('settings.all', 3600, function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }
}
