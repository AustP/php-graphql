<?php

namespace GraphQL\Document\Parser\Language\SourceText;

use function GraphQL\Document\Parser\util\getRepeating;

// Comment :: # CommentChar*
function Comment($string)
{
    $char = substr($string, 0, 1);
    if ($char !== '#') {
        return [null, $string];
    }

    [$chars, $substr] = getRepeating(
        substr($string, 1),
        function ($value, $substr) {
            [$char, $substr] = CommentChar($substr);
            if ($char === null) {
                return null;
            }

            return [$value . $char, $substr];
        },
        ''
    );

    return [$chars, $substr];
}
