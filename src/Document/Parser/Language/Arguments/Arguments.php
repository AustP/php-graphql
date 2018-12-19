<?php

namespace GraphQL\Document\Parser\Language\Arguments;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\util\getRepeating;

// Arguments[Const] : ( Argument[?Const]+ )
function Arguments($string, $scope)
{
    [$leftParen, $substr] = Punctuator($string);
    if ($leftParen !== '(') {
        return [null, $string];
    }

    [$arguments, $substr] = getRepeating(
        $substr,
        function ($arguments, $substr) use ($scope) {
            [$argument, $nextSubstr] = Argument($substr, $scope);
            if ($argument === null) {
                return null;
            }

            $name = $argument['name'];
            if (isset($arguments[$name])) {
                Parser::throwInvalid(
                    "Argument `$name` is already defined.",
                    $substr
                );
            }

            $arguments[$name] = $argument;
            return [$arguments, $nextSubstr];
        },
        [],
        1
    );
    if ($arguments === null) {
        Parser::throwSyntax('Argument', $substr);
    }

    [$rightParen, $nextSubstr] = Punctuator($substr);
    if ($rightParen !== ')') {
        Parser::throwSyntax(')', $substr);
    }

    return [$arguments, $nextSubstr];
}

function ArgumentsConst($string)
{
    [$leftParen, $substr] = Punctuator($string);
    if ($leftParen !== '(') {
        return [null, $string];
    }

    [$arguments, $substr] = getRepeating(
        $substr,
        function ($arguments, $substr) {
            [$argument, $nextSubstr] = ArgumentConst($substr);
            if ($argument === null) {
                return null;
            }

            if (isset($arguments[$argument['name']])) {
                Parser::throwInvalid(
                    "Argument `{$argument['name']} is already defined.`",
                    $substr
                );
            }

            $arguments[$argument['name']] = $argument;
            return [$arguments, $nextSubstr];
        },
        [],
        1
    );
    if ($arguments === null) {
        Parser::throwSyntax('ArgumentConst', $substr);
    }

    [$rightParen, $nextSubstr] = Punctuator($substr);
    if ($rightParen !== ')') {
        Parser::throwSyntax(')', $substr);
    }

    return [$arguments, $nextSubstr];
}
