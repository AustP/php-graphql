<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use function GraphQL\Document\Parser\util\LexicalToken;

/*
FloatValue ::
- IntegerPart FractionalPart
- IntegerPart ExponentPart
- IntegerPart FractionalPart ExponentPart
 */
function FloatValue($string)
{
    return LexicalToken($string, function ($string) {
        [$integer, $substr] = IntegerPart($string);
        if ($integer === null) {
            return [null, $string];
        }

        [$fractional, $substr] = FractionalPart($substr);
        if ($fractional === null) {
            [$exponent, $substr] = ExponentPart($substr);
            if ($exponent === null) {
                return [null, $string];
            } else {
                $value = (float)($integer . $exponent);
                return [['type' => 'Float', 'value' => $value], $substr];
            }
        }

        [$exponent, $substr] = ExponentPart($substr);
        if ($exponent === null) {
            $value = (float)($integer . $fractional);
            return [['type' => 'Float', 'value' => $value], $substr];
        } else {
            $value = (float)($integer . $fractional . $exponent);
            return [['type' => 'Float', 'value' => $value], $substr];
        }
    });
}
