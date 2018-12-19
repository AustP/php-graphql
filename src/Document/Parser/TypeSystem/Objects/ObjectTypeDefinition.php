<?php

namespace GraphQL\Document\Parser\TypeSystem\Objects;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\TypeSystem\Descriptions\Description;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\util\Keyword;

// ObjectTypeDefinition : Description? type Name ImplementsInterfaces?
//   Directives[Const]? FieldsDefinition?
function ObjectTypeDefinition($string)
{
    [$description, $substr] = Description($string);
    [$type, $substr] = Keyword($substr, 'type');
    if ($type !== 'type') {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    [$implements, $substr] = ImplementsInterfaces($substr);
    [$directives, $substr] = DirectivesConst($substr, 'OBJECT');
    [$fields, $substr] = FieldsDefinition($substr);
    if ($fields === null) {
        Parser::throwInvalid(
            "`type $name` must define one or more fields.",
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

    $definition['fields'] = $fields;

    if ($implements !== null) {
        $definition['implements'] = $implements;
    }

    $definition['name'] = $name;
    $definition['type'] = 'type';

    if (isset($definition['implements'])) {
        Parser::addImplementer($definition);
    }

    return [$definition, $substr];
}
