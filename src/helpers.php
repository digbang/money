<?php

if (! function_exists('prettyMoney')) {
    function prettyMoney(\Money\Money $money, int $decimals = 2): string
    {
        $currencySymbol = $money->getCurrency()->getCode();

        config('utils-money.symbols.' . $currencySymbol, '$');

        return $currencySymbol . ' ' . number_format($money->getAmount() / 100, $decimals, ',', '.');
    }
}

if (! function_exists('exportMoney')) {
    function exportMoney(\Money\Money $money, int $decimals = 2): float
    {
        return number_format($money->getAmount() / 100, $decimals, '.', '');
    }
}

