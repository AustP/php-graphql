<?php

namespace GraphQL\Document\Parser\Language\InputValues;

use GraphQL\Document\Parser;
use GraphQL\Document\Parser\Validator;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;

// ObjectField[Const] : Name : Value[?Const]
function ObjectField($string)
{
    [$name, $substr] = Name($string);
    if ($name === null) {
        return [null, $string];
    }

    [$colon, $nextSubstr] = Punctuator($substr);
    if ($colon !== ':') {
        Parser::throwSyntax(':', $substr);
    }

    [$value, $substr] = Value($nextSubstr);
    if ($value === null) {
        Parser::throwSyntax('Value', $substr);
    }

    return [['name' => $name, 'value' => $value], $substr];
}

function ObjectFieldConst($string)
{
    [$name, $substr] = Name($string);
    if ($name === null) {
        return [null, $string];
    }

    [$colon, $nextSubstr] = Punctuator($substr);
    if ($colon !== ':') {
        Parser::throwSyntax(':', $substr);
    }

    [$value, $substr] = ValueConst($nextSubstr);
    if ($value === null) {
        Parser::throwSyntax('ValueConst', $substr);
    }

    return [['name' => $name, 'value' => $value], $substr];
}
