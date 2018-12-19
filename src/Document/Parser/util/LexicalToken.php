<?php

namespace GraphQL\Document\Parser\util;

use function GraphQL\Document\Parser\Language\SourceText\Ignored;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;

/*
In order for us to parse, we need to be able to tell if something is a token
or not. The below specification is not standard and completely made up.

LexicalToken :
- Ignored* TokenNotPunctuator TokenEnd
- Ignored* Punctuator TokenEnd?
TokenNotPunctuator : Token but not Punctuator
TokenEnd :
- Ignored+
- Punctuator
- EOF
 */
function LexicalToken($string, $callback)
{
    [$ignored, $substr] = getRepeating(
        $string,
        function ($value, $substr) {
            [$char, $substr] = Ignored($substr);
            if ($char === null) {
                return null;
            }

            return [$value . $char, $substr];
        },
        ''
    );

    [$value, $substr] = $callback($substr);

    [$ignored, $substr] = getRepeating(
        $substr,
        function ($value, $substr) {
            [$char, $substr] = Ignored($substr);
            if ($char === null) {
                return null;
            }

            return [$value . $char, $substr];
        },
        '',
        1
    );

    return [$value, $substr];
}
