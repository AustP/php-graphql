<?php

namespace GraphQL\Document\Parser\Language\Variables;

use GraphQL\Document\Parser;
use GraphQL\Document\Parser\Validator;
use function GraphQL\Document\Parser\util\getRepeating;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;

// VariableDefinitions : ( VariableDefinition+ )
function VariableDefinitions($string)
{
    [$leftParen, $substr] = Punctuator($string);
    if ($leftParen !== '(') {
        return [null, $string];
    }

    [$definitions, $substr] = getRepeating(
        $substr,
        function ($definitions, $substr) {
            [$definition, $nextSubstr] = VariableDefinition($substr);
            if ($definition === null) {
                return null;
            }

            $name = $definition['name'];
            if (isset($definitions[$name])) {
                Parser::throwInvalid(
                    "Variable `$$name` is already defined.",
                    $substr
                );
            }

            if (!Validator::isInputType($definition['type'])) {
                Parser::throwInvalid(
                    "Variable `$$name` must be an input type.",
                    $substr
                );
            }

            $definitions[$name] = $definition;
            return [$definitions, $nextSubstr];
        },
        [],
        1
    );
    if ($definitions === null) {
        Parser::throwSyntax('VariableDefinition', $substr);
    }

    [$rightParen, $nextSubstr] = Punctuator($substr);
    if ($rightParen !== ')') {
        Parser::throwSyntax(')', $substr);
    }

    return [$definitions, $nextSubstr];
}
