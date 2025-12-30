<?php

use Illuminate\Support\Number;

if (!function_exists('format_price')) {
    /**
     * Formate un prix en FCFA
     *
     * @param float|int|null $amount
     * @param bool $showCurrency
     * @return string
     */
    function format_price($amount, bool $showCurrency = true): string
    {
        if ($amount === null) {
            return $showCurrency ? '0 F CFA' : '0';
        }
        
        $formatted = number_format($amount, 0, ',', ' ');
        
        return $showCurrency ? $formatted . ' F CFA' : $formatted;
    }
}

if (!function_exists('format_number')) {
    /**
     * Formate un nombre avec séparateur de milliers
     *
     * @param float|int|null $number
     * @param int $decimals
     * @return string
     */
    function format_number($number, int $decimals = 0): string
    {
        if ($number === null) {
            return '0';
        }
        
        return number_format($number, $decimals, ',', ' ');
    }
}

if (!function_exists('currency')) {
    /**
     * Retourne le symbole de la devise
     *
     * @return string
     */
    function currency(): string
    {
        return config('app.currency', 'F CFA');
    }
}

