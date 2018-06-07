<?php

if (! function_exists('prettyMoney')) {
    function prettyMoney(\Money\Money $money, $displaySymbol = true): string
    {
        /** @var \Money\Formatter\IntlMoneyFormatter $formatter */
        $formatter = app(\Money\Formatter\IntlMoneyFormatter::class);

        if ($displaySymbol) {
            $currencySymbol = config("money.symbols.{$money->getCurrency()->getCode()}", '$');

            return $currencySymbol . ' ' . $formatter->format($money);
        }

        return $formatter->format($money);
    }
}

if (! function_exists('exportMoney')) {
    function exportMoney(\Money\Money $money): float
    {
        /** @var \Money\Formatter\DecimalMoneyFormatter $formatter */
        $formatter = app(\Money\Formatter\DecimalMoneyFormatter::class);

        return $formatter->format($money);
    }
}
