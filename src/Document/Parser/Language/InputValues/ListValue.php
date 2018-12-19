<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use GraphQL\Document\Parser;
use GraphQL\Document\Parser\Validator;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\util\getRepeating;

/*
ListValue[Const] :
- [ ]
- [ Value[?Const]+ ]
 */
function ListValue($string)
{
    [$leftBracket, $substr] = Punctuator($string);
    if ($leftBracket !== '[') {
        return [null, $string];
    }

    [$rightBracket, $nextSubstr] = Punctuator($substr);
    if ($rightBracket === ']') {
        return [['type' => 'List', 'value' => []], $nextSubstr];
    }

    [$values, $substr] = getRepeating(
        $substr,
        function ($values, $substr) {
            [$value, $nextSubstr] = Value($substr);
            if ($value === null) {
                return null;
            }

            $values[] = $value;
            return [$values, $nextSubstr];
        },
        [],
        1
    );
    if ($values === null) {
        Parser::throwSyntax('Value', $substr);
    }

    [$rightBracket, $nextSubstr] = Punctuator($substr);
    if ($rightBracket !== ']') {
        Parser::throwSyntax(']', $substr);
    }

    return [['type' => 'List', 'value' => $values], $nextSubstr];
}

function ListValueConst($string)
{
    [$leftBracket, $substr] = Punctuator($string);
    if ($leftBracket !== '[') {
        return [null, $string];
    }

    [$rightBracket, $nextSubstr] = Punctuator($substr);
    if ($rightBracket === ']') {
        return [['type' => 'List', 'value' => []], $nextSubstr];
    }

    [$values, $substr] = getRepeating(
        $substr,
        function ($values, $substr) {
            [$value, $substr] = ValueConst($substr);
            if ($value === null) {
                return null;
            }

            $values[] = $value;
            return [$values, $substr];
        },
        [],
        1
    );
    if ($values === null) {
        Parser::throwSyntax('ValueConst', $substr);
    }

    [$rightBracket, $nextSubstr] = Punctuator($substr);
    if ($rightBracket !== ']') {
        Parser::throwSyntax(']', $substr);
    }

    return [['type' => 'List', 'value' => $values], $nextSubstr];
}
