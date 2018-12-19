<?php

namespace GraphQL\Document\Parser\TypeSystem\Objects;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\util\getRepeating;

// FieldsDefinition : { FieldDefinition+ }
function FieldsDefinition($string)
{
    [$leftBracket, $substr] = Punctuator($string);
    if ($leftBracket !== '{') {
        return [null, $string];
    }

    [$definitions, $substr] = getRepeating(
        $substr,
        function ($definitions, $substr) {
            [$definition, $nextSubstr] = FieldDefinition($substr);
            if ($definition === null) {
                return null;
            }

            if (isset($definitions[$definition['name']])) {
                Parser::throwInvalid(
                    "Field `{$definition['name']}` is already defined.",
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
        Parser::throwSyntax('FieldDefinition', $substr);
    }

    [$rightBracket, $nextSubstr] = Punctuator($substr);
    if ($rightBracket !== '}') {
        Parser::throwSyntax('}', $substr);
    }

    return [$definitions, $nextSubstr];
}
