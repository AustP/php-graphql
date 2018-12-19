<?php

namespace GraphQL\Document\Parser\TypeSystem\Interfaces;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\TypeSystem\Descriptions\Description;
use function GraphQL\Document\Parser\TypeSystem\Objects\FieldsDefinition;
use function GraphQL\Document\Parser\util\Keyword;

// InterfaceTypeDefinition : Description? interface Name Directives[Const]?
//   FieldsDefinition?
function InterfaceTypeDefinition($string)
{
    [$description, $substr] = Description($string);
    [$interface, $substr] = Keyword($substr, 'interface');
    if ($interface !== 'interface') {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    [$directives, $substr] = DirectivesConst($substr, 'INTERFACE');
    [$fields, $substr] = FieldsDefinition($substr);
    if ($fields === null) {
        Parser::throwInvalid(
            "`interface $name` must define one or more fields.",
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

    if ($fields !== null) {
        $definition['fields'] = $fields;
    }

    $definition['name'] = $name;
    $definition['type'] = 'interface';

    return [$definition, $substr];
}
