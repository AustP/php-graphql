<?php

namespace GraphQL\Document\Parser\TypeSystem\Unions;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\util\Keyword;

/*
UnionTypeExtension :
- extend union Name Directives[Const]? UnionMemberTypes
- extend union Name Directives[Const]
 */
function UnionTypeExtension($string)
{
    [$extend, $substr] = Keyword($string, 'extend');
    if ($extend !== 'extend') {
        return [null, $string];
    }

    [$union, $substr] = Keyword($substr, 'union');
    if ($union !== 'union') {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    [$directives, $substr] = DirectivesConst($substr, 'UNION');
    [$types, $substr] = UnionMemberTypes($substr);
    if ($directives === null && $types === null) {
        Parser::throwSyntax('DirectivesConst or UnionMemberTypes', $substr);
    }

    $extension = [];

    if ($directives !== null) {
        $extension['directives'] = $directives;
    }

    $extension['name'] = $name;
    $extension['type'] = 'union';

    if ($types !== null) {
        $extension['types'] = $types;
    }

    return [$extension, $substr];
}
