<?php

namespace GraphQL\Document\Parser\TypeSystem\Scalars;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\TypeSystem\Descriptions\Description;
use function GraphQL\Document\Parser\util\Keyword;

// ScalarTypeDefinition : Description? scalar Name Directives[Const]?
function ScalarTypeDefinition($string)
{
    [$description, $substr] = Description($string);
    [$scalar, $substr] = Keyword($substr, 'scalar');
    if ($scalar !== 'scalar') {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    [$directives, $substr] = DirectivesConst($substr, 'SCALAR');

    $definition = [];

    if ($description !== null) {
        $definition['description'] = $description;
    }

    if ($directives !== null) {
        $definition['directives'] = $directives;
    }

    $definition['name'] = $name;
    $definition['type'] = 'scalar';

    return [$definition, $substr];
}
