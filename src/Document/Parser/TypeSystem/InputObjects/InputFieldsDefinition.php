<?php

namespace GraphQL\Document\Parser\TypeSystem\InputObjects;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\TypeSystem\Objects\InputValueDefinition;
use function GraphQL\Document\Parser\util\getRepeating;

// InputFieldsDefinition : { InputValueDefinition+ }
function InputFieldsDefinition($string)
{
    [$leftBracket, $substr] = Punctuator($string);
    if ($leftBracket !== '{') {
        return [null, $string];
    }

    [$values, $substr] = getRepeating(
        $substr,
        function ($values, $substr) {
            [$value, $nextSubstr] = InputValueDefinition($substr);
            if ($value === null) {
                return null;
            }

            if (strpos($value['name'], '__') === 0) {
                Parser::throwInvalid(
                    "Field `{$value['name']}` cannot start with `__`.",
                    $substr
                );
            }

            if (isset($values[$value['name']])) {
                Parser::throwInvalid(
                    "Field `{$value['name']}` is already defined.",
                    $substr
                );
            }

            $values[$value['name']] = $value;
            return [$values, $nextSubstr];
        },
        [],
        1
    );
    if ($values === null) {
        Parser::throwSyntax('InputValueDefinition', $substr);
    }

    [$rightBracket, $nextSubstr] = Punctuator($substr);
    if ($rightBracket !== '}') {
        Parser::throwSyntax('}', $substr);
    }

    return [$values, $nextSubstr];
}
