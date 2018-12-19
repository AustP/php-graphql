<?php

namespace GraphQL\Document\Parser\TypeSystem\InputObjects;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\util\Keyword;

/*
InputObjectTypeExtension :
- extend input Name Directives[Const]? InputFieldsDefinition
- extend input Name Directives[Const]
 */
function InputObjectTypeExtension($string)
{
    [$extend, $substr] = Keyword($string, 'extend');
    if ($extend !== 'extend') {
        return [null, $string];
    }

    [$input, $substr] = Keyword($substr, 'input');
    if ($input !== 'input') {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    [$directives, $substr] = DirectivesConst($substr, 'INPUT_OBJECT');
    [$fields, $substr] = InputFieldsDefinition($substr);
    if ($directives === null && $fields === null) {
        Parser::throwSyntax(
            'DirectivesConst or InputFieldsDefinition',
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

    $extension['name'] = $name;
    $extension['type'] = 'input';
    return [$extension, $substr];
}
