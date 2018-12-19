<?php

namespace GraphQL\Document\Parser\Language\SourceText;

use function GraphQL\Document\Parser\Language\InputValues\IntValue;
use function GraphQL\Document\Parser\Language\InputValues\FloatValue;
use function GraphQL\Document\Parser\Language\InputValues\StringValue;

/*
Token ::
- Punctuator
- Name
- IntValue
- FloatValue
- StringValue
 */
function Token($string)
{
    [$punctuator, $substr] = Punctuator($string);
    if ($punctuator !== null) {
        return [$punctuator, $substr];
    }

    [$name, $substr] = Name($string);
    if ($name !== null) {
        return [$name, $substr];
    }

    [$int, $substr] = IntValue($string);
    if ($int !== null) {
        return [$int, $substr];
    }

    [$float, $substr] = FloatValue($string);
    if ($float !== null) {
        return [$float, $substr];
    }

    [$value, $substr] = StringValue($string);
    if ($value !== null) {
        return [$value, $substr];
    }

    return [null, $string];
}
