<?php

namespace GraphQL\Document\Parser\TypeSystem\Enums;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\util\getRepeating;

// EnumValuesDefinition : { EnumValueDefinition+ }
function EnumValuesDefinition($string)
{
    [$leftBracket, $substr] = Punctuator($string);
    if ($leftBracket !== '{') {
        return [null, $string];
    }

    [$definitions, $substr] = getRepeating(
        $substr,
        function ($definitions, $substr) {
            [$definition, $nextSubstr] = EnumValueDefinition($substr);
            if ($definition === null) {
                return null;
            }

            if (isset($definitions[$definition['name']])) {
                Parser::throwInvalid(
                    "Enum value `{$definition['name']}` is already defined.",
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
        Parser::throwSyntax('EnumValueDefinition', $substr);
    }

    [$rightBracket, $nextSubstr] = Punctuator($substr);
    if ($rightBracket !== '}') {
        Parser::throwSyntax('}', $substr);
    }

    return [$definitions, $nextSubstr];
}
