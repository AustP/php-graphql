<?php

namespace GraphQL\Document\Parser\Language\SourceText;

/*
Token ::
- UnicodeBOM
- WhiteSpace
- LineTerminator
- Comment
- Comma
 */
function Ignored($string)
{
    [$bom, $substr] = UnicodeBOM($string);
    if ($bom !== null) {
        return [$bom, $substr];
    }

    [$whitespace, $substr] = WhiteSpace($string);
    if ($whitespace !== null) {
        return [$whitespace, $substr];
    }

    [$terminator, $substr] = LineTerminator($string);
    if ($terminator !== null) {
        return [$terminator, $substr];
    }

    [$comment, $substr] = Comment($string);
    if ($comment !== null) {
        return [$comment, $substr];
    }

    [$comma, $substr] = Comma($string);
    if ($comma !== null) {
        return [$comma, $substr];
    }

    return [null, $string];
}
