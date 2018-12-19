<?php

namespace GraphQL\Document\Parser\Language\SourceText;

use function GraphQL\Document\Parser\util\LexicalToken;

// Punctuator :: one of ! $ ( ) ... : = @ [ ] { | }
function Punctuator($string)
{
    return LexicalToken($string, function ($string) {
        $char = substr($string, 0, 1);
        if ($char === '!' ||
            $char === '$' ||
            $char === '(' ||
            $char === ')' ||
            $char === ':' ||
            $char === '=' ||
            $char === '@' ||
            $char === '[' ||
            $char === ']' ||
            $char === '{' ||
            $char === '|' ||
            $char === '}'
        ) {
            return [$char, substr($string, 1)];
        }

        if ($char === '.') {
            $chars = substr($string, 1, 2);
            if ($chars === '..') {
                return ['...', substr($string, 3)];
            }
        }

        return [null, $string];
    });
}
