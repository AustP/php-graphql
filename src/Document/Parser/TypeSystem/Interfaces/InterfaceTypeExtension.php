<?php

namespace GraphQL\Document\Parser\TypeSystem\Interfaces;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\TypeSystem\Objects\FieldsDefinition;
use function GraphQL\Document\Parser\util\Keyword;

/*
InterfaceTypeExtension :
- extend interface Name Directives[Const]? FieldsDefinition
- extend interface Name Directives[Const]
 */
function InterfaceTypeExtension($string)
{
    [$extend, $substr] = Keyword($string, 'extend');
    if ($extend !== 'extend') {
        return [null, $string];
    }

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
    if ($directives === null && $fields === null) {
        Parser::throwSyntax('DirectivesConst or FieldsDefinition', $substr);
    }

    $extension = [];

    if ($directives !== null) {
        $extension['directives'] = $directives;
    }

    if ($fields !== null) {
        $extension['fields'] = $fields;
    }

    $extension['name'] = $name;
    $extension['type'] = 'interface';

    return [$extension, $substr];
}
