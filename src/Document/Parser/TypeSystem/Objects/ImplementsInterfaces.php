<?php

namespace GraphQL\Document\Parser\TypeSystem\Objects;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\TypeReferences\NamedType;
use function GraphQL\Document\Parser\util\getRepeating;
use function GraphQL\Document\Parser\util\Keyword;

/*
ImplementsInterfaces :
- implements &? NamedType
- ImplementsInterfaces & NamedType
 */
function ImplementsInterfaces($string)
{
    [$implements, $substr] = Keyword($string, 'implements');
    if ($implements === null) {
        return [null, $string];
    }

    [$interfaces, $substr] = getRepeating(
        $substr,
        function ($interfaces, $substr) {
            [$ampersand, $substr] = Keyword($substr, '&');
            if (count($interfaces) >= 1 && $ampersand !== '&') {
                return null;
            }

            [$type, $nextSubstr] = NamedType($substr);
            if ($type === null) {
                return null;
            }

            $name = $type['value'];
            if (isset($interfaces[$name])) {
                Parser::throwInvalid(
                    "Interface `$name` is already declared as implemented.",
                    $substr
                );
            }

            $interfaces[$name] = ['name' => $name];
            return [$interfaces, $nextSubstr];
        },
        [],
        1
    );
    if ($interfaces === null) {
        Parser::throwSyntax('NamedType', $substr);
    }

    return [$interfaces, $substr];
}
