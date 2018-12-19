<?php

namespace GraphQL\Document\Parser\TypeSystem\Unions;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\Language\TypeReferences\NamedType;
use function GraphQL\Document\Parser\util\getRepeating;

/*
UnionMemberTypes :
- = |? NamedType
- UnionMemberTypes | NamedType
 */
function UnionMemberTypes($string)
{
    [$equals, $substr] = Punctuator($string);
    if ($equals !== '=') {
        return [null, $string];
    }

    [$types, $substr] = getRepeating(
        $substr,
        function ($types, $substr) {
            [$pipe, $substr] = Punctuator($substr);
            if (count($types) >= 1 && $pipe !== '|') {
                return null;
            }

            [$type, $nextSubstr] = NamedType($substr);
            if ($type === null) {
                return null;
            }

            if (isset($types[$type['value']])) {
                Parser::throwInvalid(
                    "Member type `{$type['value']}` is already included.",
                    $substr
                );
            }

            $types[$type['value']] = ['name' => $type['value']];
            return [$types, $nextSubstr];
        },
        [],
        1
    );
    if ($types === null) {
        Parser::throwSyntax('NamedType', $substr);
    }

    return [$types, $substr];
}
