<?php

namespace GraphQL\Document\Parser\TypeSystem\Unions;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\TypeSystem\Descriptions\Description;
use function GraphQL\Document\Parser\util\Keyword;

// UnionTypeDefinition : Description? union Name Directives[Const]?
//   UnionMemberTypes?
function UnionTypeDefinition($string)
{
    [$description, $substr] = Description($string);
    [$union, $substr] = Keyword($substr, 'union');
    if ($union !== 'union') {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    [$directives, $substr] = DirectivesConst($substr, 'UNION');
    [$types, $nextSubstr] = UnionMemberTypes($substr);
    if ($types === null) {
        Parser::throwInvalid(
            "`union $name` must include one or more member types.",
            $substr
        );
    }

    $definition = [];

    if ($description !== null) {
        $definition['description'] = $description;
    }

    if ($directives !== null) {
        $definition['directives'] = $directives;
    }

    $definition['name'] = $name;
    $definition['type'] = 'union';

    if ($types !== null) {
        $definition['types'] = $types;
        foreach ($types as $type) {
            Parser::addObjectType($type, $substr);
        }
    }

    return [$definition, $nextSubstr];
}
