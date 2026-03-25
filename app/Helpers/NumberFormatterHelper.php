<?php

namespace App\Helpers;

class NumberFormatterHelper
{
    public static function formatQty($amount,$currency): string
    {

        // if($amount && $amount==0 || $amount=='0'|| $amount==NULL){
        if ($amount === null || $amount === '' || $amount == 0 || $amount == '0') {
            return '0';
        }

        return match ($currency) {
            '₹' => self::trimDecimalZero(self::formatINR($amount,3)),
            'रु' => self::trimDecimalZero(self::formatNPR($amount,3)),
            'NPR' => self::trimDecimalZero(self::formatNPR($amount,3)),
            '$' => self::trimDecimalZero(self::formatUSD($amount,3)),
            // default =>  round($amount, 2),
            default => self::trimDecimalZero(sprintf("%.3f", (float)$amount)),
        };
    }
    public static function formatCurrencyPDF($amount,$currency): string
    {

        // if($amount && $amount==0 || $amount=='0'|| $amount==NULL){
        if ($amount === null || $amount === '' || $amount == 0 || $amount == '0') {
            return '0';
        }

        return match ($currency) {
            '₹' => self::trimDecimalZero(self::formatINR($amount,2)),
            'रु' => self::trimDecimalZero(self::formatNPR($amount,2)),
            'NPR' => self::trimDecimalZero(self::formatNPR($amount,2)),
            '$' => self::trimDecimalZero(self::formatUSD($amount,2)),
            // default =>  round($amount, 2),
            default => self::trimDecimalZero(sprintf("%.2f", (float)$amount)),
        };
    }
    public static function trimDecimalZero(string $formattedAmount): string
    {        
        if (str_ends_with($formattedAmount, '.000')) {
            return substr($formattedAmount, 0, -4);
        }

        return $formattedAmount;
    }
    public static function formatCurrency($amount,$currency): string
    {
        // $currency = session('user_currency')['symbol'] ?? '₹'; // Safely get currency symbol
        if($amount==0 || $amount=='0'){
            return $currency .' '.$amount;
        }
        return match ($currency) {
            '₹' => '₹ ' .self::formatINR($amount,2),
            'रु' => 'रु ' .self::formatNPR($amount,2),
            'NPR' => 'NPR ' .self::formatNPR($amount,2),
            '$' => '$ ' .self::formatUSD($amount,2),
            // default =>  round($amount, 2),
            default => $currency . ' ' . sprintf("%.2f", (float)$amount),
        };
    }
    

    public static function formatINR($amount,$decimalPlaces = 2): string
    {
        $amount = (float)$amount;
        $amountFormatted = number_format($amount, $decimalPlaces, '.', '');

        [$intPart, $decimalPart] = explode('.', $amountFormatted);

        $lastThree = substr($intPart, -3);
        $restUnits = substr($intPart, 0, -3);

        if ($restUnits != '') {
            $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
        }

        $formatted = ($restUnits != '') ? $restUnits . ',' . $lastThree : $lastThree;

        return $formatted . '.' . $decimalPart;
    }


    public static function formatNPR($amount,$decimalPlaces = 2): string
    {
        return self::formatINR($amount,$decimalPlaces);
    }


    public static function formatUSD($amount,$decimalPlaces = 2): string
    {
        return  number_format((float) $amount, $decimalPlaces, '.', ',');
    }
}
