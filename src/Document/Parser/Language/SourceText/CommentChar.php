<?php

namespace GraphQL\Document\Parser\Language\SourceText;

// CommentChar :: SourceCharacter but not LineTerminator
function CommentChar($string)
{
    [$char, $substr] = SourceCharacter($string);
    if ($char === null) {
        return [null, $string];
    }

    [$terminator] = LineTerminator($string);
    if ($terminator !== null) {
        return [null, $string];
    }

    return [$char, $substr];
}
