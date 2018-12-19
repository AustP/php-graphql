<?php

namespace GraphQL\Document\Parser\TypeSystem\Objects;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\util\getRepeating;

// ArgumentsDefinition : ( InputValueDefinition+ )
function ArgumentsDefinition($string)
{
    [$leftParen, $substr] = Punctuator($string);
    if ($leftParen !== '(') {
        return [null, $string];
    }

    [$definitions, $substr] = getRepeating(
        $substr,
        function ($definitions, $substr) {
            [$definition, $nextSubstr] = InputValueDefinition($substr);
            if ($definition === null) {
                return null;
            }

            if (strpos($definition['name'], '__') === 0) {
                Parser::throwInvalid(
                    "Argument `{$definition['name']}` cannot start with `__`.",
                    $substr
                );
            }

            if (isset($definitions[$definition['name']])) {
                Parser::throwInvalid(
                    "Argument `{$definition['name']}` is already defined.",
                    $substr
                );
            }

            $definitions[$definition['name']] = $definition;
            return [$definitions, $nextSubstr];
        },
        [],
        1
    );
    if ($definitions === null) {
        Parser::throwSyntax('InputValueDefinition', $substr);
    }

    [$rightParen, $nextSubstr] = Punctuator($substr);
    if ($rightParen !== ')') {
        Parser::throwSyntax(')', $substr);
    }

    return [$definitions, $nextSubstr];
}
