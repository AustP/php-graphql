<?php

namespace GraphQL\Document\Parser\TypeSystem\Objects;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\util\Keyword;

/*
ObjectTypeExtension :
- extend type Name ImplementsInterfaces? Directives[Const]? FieldsDefinition
- extend type Name ImplementsInterfaces? Directives[Const]
- extend type Name ImplementsInterfaces
 */
function ObjectTypeExtension($string)
{
    [$extend, $substr] = Keyword($string, 'extend');
    if ($extend !== 'extend') {
        return [null, $string];
    }

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
    if ($implements === null && $directives === null && $fields === null) {
        Parser::throwSyntax(
            'ImplementsInterfaces, DirectivesConst, or FieldsDefinition',
            $substr
        );
    }

    $extension = [];

    if ($directives !== null) {
        $extension['directives'] = $directives;
    }

    if ($fields !== null) {
        $extension['fields'] = $fields;
    }

    if ($implements !== null) {
        $extension['implements'] = $implements;
    }

    $extension['name'] = $name;
    $extension['type'] = 'type';

    return [$extension, $substr];
}
