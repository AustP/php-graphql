<?php

namespace GraphQL\Document\Parser\Language\Arguments;

use GraphQL\Document\Parser;
use GraphQL\Document\Parser\Validator;
use function GraphQL\Document\Parser\Language\InputValues\Value;
use function GraphQL\Document\Parser\Language\InputValues\ValueConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;

// Argument[Const] : Name : Value[?Const]
function Argument($string, $scope)
{
    [$name, $substr] = Name($string);
    if ($name === null) {
        return [null, $string];
    }

    $definitions = $scope['arguments'] ?? [];

    if (!isset($definitions[$name])) {
        $field = ($scope['type'] === 'directive' ? '@' : '') . $scope['name'];
        Parser::throwInvalid(
            "Argument `$name` is not defined on `$field`.",
            $substr
        );
    }

    [$colon, $substr] = Punctuator($substr);
    if ($colon !== ':') {
        Parser::throwSyntax(':', $substr);
    }

    [$value, $nextSubstr] = Value($substr);
    if ($value === null) {
        Parser::throwSyntax('Value', $substr);
    }

    Validator::validateInputValue(
        $definitions[$name]['type'],
        ['name' => $name, 'value' => $value],
        $substr
    );

    // getExpectedType will mutate $value and add $value['__type'] to it
    getExpectedType($value, $definitions[$name], $substr);

    return [['name' => $name, 'value' => $value], $nextSubstr];
}

function ArgumentConst($string)
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

function getExpectedType(&$value, $scope, $string)
{
    if ($value['type'] === 'Object') {
        foreach ($value['value'] as $key => &$v) {
            $type = $scope['type']['value'];
            getExpectedType(
                $v,
                Parser::$schemaDocument[$type]['fields'][$key],
                $string
            );
        }
    } elseif ($value['type'] === 'List') {
        getExpectedType(
            $value['value'][0],
            ['name' => $scope['name'], 'type' => $scope['type']['value']],
            $string
        );
    }

    $expectedType = [];

    if (isset($scope['default'])) {
        $expectedType['default'] = $scope['default'];
    }

    $expectedType['name'] = $scope['name'];
    $expectedType['type'] = $scope['type'];

    $value['__type'] = $expectedType;

    if ($value['type'] === 'Variable') {
        Validator::validateVariableUsage(
            $value['value'],
            $value['__type'],
            $string
        );
    }
}
