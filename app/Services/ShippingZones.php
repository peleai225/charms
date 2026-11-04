<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Zones de livraison (Paramètres → Livraison) : prix client et frais livreur par quartier.
 * La zone d'une commande se retrouve par sa ville de livraison (le choix de zone au paiement y écrit le nom de la zone).
 */
class ShippingZones
{
    /**
     * @return array<int, array{name: string, cities: string, price: mixed, courier_fee?: mixed}>
     */
    public function all(): array
    {
        return array_values(json_decode(Setting::get('shipping_zones', '[]'), true) ?: []);
    }

    public function findForCity(?string $city): ?array
    {
        $city = mb_strtolower(trim((string) $city));
        if ($city === '') {
            return null;
        }

        foreach ($this->all() as $zone) {
            $cities = array_map(fn ($c) => mb_strtolower(trim($c)), explode(',', (string) ($zone['cities'] ?? '')));
            if (mb_strtolower(trim((string) ($zone['name'] ?? ''))) === $city || in_array($city, $cities, true)) {
                return $zone;
            }
        }

        return null;
    }

    public function courierFeeForCity(?string $city): ?float
    {
        $fee = $this->findForCity($city)['courier_fee'] ?? null;

        return $fee === null || $fee === '' ? null : (float) $fee;
    }
}
