<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\LineTerminator;
use function GraphQL\Document\Parser\Language\SourceText\SourceCharacter;

/*
StringCharacter ::
- SourceCharacter but not " or \ or LineTerminator
- \u EscapedUnicode
- \ EscapedCharacter
 */
function StringCharacter($string)
{
    [$char, $substr] = SourceCharacter($string);
    if ($char === '\\') {
        $char2 = substr($substr, 0, 1);
        if ($char2 === 'u') {
            [$unicode, $substr] = EscapedUnicode(substr($string, 2));
            if ($unicode === null) {
                Parser::throwSyntax('EscapedUnicode', $substr);
            }

            return [json_decode("\"\\u$unicode\""), $substr];
        }

        [$escaped, $substr] = EscapedCharacter(substr($string, 1));
        if ($escaped === null) {
            Parser::throwSyntax('EscapedCharacter', $substr);
        }

        $mapping = [
            '"' => "\u{0022}",
            '\\' => "\u{005C}",
            '/' => "\u{002F}",
            'b' => "\u{0008}",
            'f' => "\u{000C}",
            'n' => "\u{000A}",
            'r' => "\u{000D}",
            't' => "\u{0009}"
        ];

        return [$mapping[$escaped], $substr];
    }

    if ($char !== null) {
        if ($char === '"') {
            return [null, $string];
        }

        [$terminator] = LineTerminator($string);
        if ($terminator !== null) {
            return [null, $string];
        }

        return [$char, $substr];
    }

    return [null, $string];
}
