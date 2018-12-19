<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\util\getRepeating;

/*
ObjectValue[Const] :
- { }
- { ObjectField[?Const]+ }
 */
function ObjectValue($string)
{
    [$leftBracket, $substr] = Punctuator($string);
    if ($leftBracket !== '{') {
        return [null, $string];
    }

    [$rightBracket, $substr] = Punctuator($substr);
    if ($rightBracket === '}') {
        return [['type' => 'Object', 'value' => []], $substr];
    }

    [$values, $substr] = getRepeating($substr, function ($values, $substr) {
        [$field, $nextSubstr] = ObjectField($substr);
        if ($field === null) {
            return null;
        }

        if (isset($values[$field['name']])) {
            Parser::throwInvalid(
                "Field `{$field['name']}` is already defined.",
                $substr
            );
        }

        $values[$field['name']] = $field['value'];
        return [$values, $nextSubstr];
    }, [], 1);
    if ($values === null) {
        Parser::throwSyntax('ObjectField', $substr);
    }

    [$rightBracket, $nextSubstr] = Punctuator($substr);
    if ($rightBracket !== '}') {
        Parser::throwSyntax('}', $substr);
    }

    return [['type' => 'Object', 'value' => $values], $nextSubstr];
}

function ObjectValueConst($string)
{
    [$leftBracket, $substr] = Punctuator($string);
    if ($leftBracket !== '{') {
        return [null, $string];
    }

    [$rightBracket, $substr] = Punctuator($substr);
    if ($rightBracket === '}') {
        return [['type' => 'Object', 'value' => []], $substr];
    }

    [$values, $substr] = getRepeating($substr, function ($values, $substr) {
        [$field, $nextSubstr] = ObjectFieldConst($substr);
        if ($field === null) {
            return null;
        }

        if (isset($values[$field['name']])) {
            Parser::throwInvalid(
                "Field `{$field['name']}` is already defined.",
                $substr
            );
        }

        $values[$field['name']] = $field['value'];
        return [$values, $nextSubstr];
    }, [], 1);
    if ($values === null) {
        Parser::throwSyntax('ObjectFieldConst', $substr);
    }

    [$rightBracket, $nextSubstr] = Punctuator($substr);
    if ($rightBracket !== '}') {
        Parser::throwSyntax('}', $substr);
    }

    return [['type' => 'Object', 'value' => $values], $nextSubstr];
}
