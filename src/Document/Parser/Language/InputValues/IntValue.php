<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use function GraphQL\Document\Parser\util\LexicalToken;

// IntValue :: IntegerPart
function IntValue($string)
{
    return LexicalToken($string, function ($string) {
        [$int, $substr] = IntegerPart($string);
        if ($int === null) {
            return [null, $string];
        }

        return [['type' => 'Int', 'value' => $int], $substr];
    });
}
