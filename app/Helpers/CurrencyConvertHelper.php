<?php

namespace App\Helpers;

class CurrencyConvertHelper
{
    public static function numberToWordsWithCurrency($amount, $symbol = '₹')
    {
        $number = floor($amount);
        $decimal = round(($amount - $number) * 100);

        // Decide number system based on currency
        $system = in_array($symbol, ['₹', 'रु']) ? 'indian' : 'international';

        $words = self::convertNumberToWords($number, $system);

        // Set currency and fractional unit names based on symbol
        [$currencyName, $fractionName] = match ($symbol) {
            '$' => ['Dollar', 'Cents'],
            '₹' => ['Rupees', 'Paise'],
            'रु' => ['Nepali Rupees', 'Paisa'],
            default => ['Amount', '']
        };

        $decimalWords = '';
        if ($decimal > 0 && $fractionName !== '') {
            $decimalWords = ' and ' . self::convertNumberToWords($decimal, $system) . ' ' . $fractionName;
        }

        return "$currencyName $words$decimalWords Only";
    }
    
    public static function convertNumberToWords($number, $system = 'international')
    {
        $words = [
            0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three',
            4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven',
            8 => 'eight', 9 => 'nine', 10 => 'ten', 11 => 'eleven',
            12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen',
            15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen',
            18 => 'eighteen', 19 => 'nineteen', 20 => 'twenty',
            30 => 'thirty', 40 => 'forty', 50 => 'fifty',
            60 => 'sixty', 70 => 'seventy', 80 => 'eighty',
            90 => 'ninety'
        ];

        $levels =  $system === 'indian'
            ? [
                10000000 => 'crore',
                100000   => 'lakh',
                1000     => 'thousand',
                100      => 'hundred'
            ]
            : [
            1000000000000 => 'trillion',
            1000000000 => 'billion',
            1000000 => 'million',
            1000 => 'thousand',
            100 => 'hundred'
        ];

        if (!is_numeric($number)) {
            return false;
        }

        if ($number < 0) {
            return ucfirst('minus ' . self::convertNumberToWords(abs($number), $system));
        }

        if ($number < 21) {
            return ucfirst($words[$number]);
        } elseif ($number < 100) {
            $tens = ((int) ($number / 10)) * 10;
            $units = $number % 10;
            return ucfirst($words[$tens] . ($units ? '-' . $words[$units] : ''));
        }

        foreach ($levels as $divisor => $levelName) {
            if ($number >= $divisor) {
                $major = floor($number / $divisor);
                $remainder = $number % $divisor;

                $result = self::convertNumberToWords($major, $system) . ' ' . $levelName;
                if ($remainder > 0) {
                    if ($remainder < 100) {
                        $result .= ' and ';
                    } else {
                        $result .= ' ';
                    }
                    $result .= self::convertNumberToWords($remainder, $system);
                }

                return ucfirst($result);
            }
        }

        return '';
    }


}
