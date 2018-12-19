<?php

namespace GraphQL\Document\Parser\TypeSystem\Enums;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\util\Keyword;

/*
EnumTypeExtension :
- extend enum Name Directives[Const]? EnumValuesDefinition
- extend enum Name Directives[Const]
 */
function EnumTypeExtension($string)
{
    [$extend, $substr] = Keyword($string, 'extend');
    if ($extend !== 'extend') {
        return [null, $string];
    }

    [$enum, $substr] = Keyword($substr, 'enum');
    if ($enum !== 'enum') {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    [$directives, $substr] = DirectivesConst($substr, 'ENUM');
    [$values, $substr] = EnumValuesDefinition($substr);
    if ($directives === null && $values === null) {
        Parser::throwSyntax('DirectivesConst or EnumValuesDefinition', $substr);
    }

    $extension = [];

    if ($directives !== null) {
        $extension['directives'] = $directives;
    }

    $extension['name'] = $name;
    $extension['type'] = 'enum';

    if ($values !== null) {
        $extension['values'] = $values;
    }

    return [$extension, $substr];
}
