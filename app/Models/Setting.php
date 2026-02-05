<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public $timestamps = false;

    /**
     * Récupérer une valeur de paramètre (temps réel - cache court)
     */
    public static function get(string $key, $default = null)
    {
        // Cache très court (60 secondes) pour un temps quasi-réel
        // Le cache est vidé immédiatement lors de la modification
        return Cache::remember("setting.{$key}", 60, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Définir une valeur de paramètre (temps réel - cache vidé immédiatement)
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        // Vider le cache pour ce paramètre spécifique
        Cache::forget("setting.{$key}");
        
        // Vider aussi le cache global des paramètres
        Cache::forget('settings.all');
    }

    /**
     * Récupérer plusieurs paramètres
     */
    public static function getMany(array $keys): array
    {
        return static::whereIn('key', $keys)->pluck('value', 'key')->toArray();
    }

    /**
     * Récupérer tous les paramètres sous forme de tableau (temps réel)
     */
    public static function getAllSettings(): array
    {
        // Cache très court pour un temps quasi-réel
        return Cache::remember('settings.all', 60, function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Vider tous les caches de paramètres (pour forcer le rechargement)
     */
    public static function clearCache(): void
    {
        Cache::forget('settings.all');
        // Vider tous les caches de paramètres individuels
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
    }
}
