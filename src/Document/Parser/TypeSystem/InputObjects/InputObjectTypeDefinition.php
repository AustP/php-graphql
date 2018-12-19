<?php

namespace GraphQL\Document\Parser\TypeSystem\InputObjects;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\TypeSystem\Descriptions\Description;
use function GraphQL\Document\Parser\util\Keyword;

// InputObjectTypeDefinition : Description? input Name Directives[Const]?
//   InputFieldsDefinition?
function InputObjectTypeDefinition($string)
{
    [$description, $substr] = Description($string);
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
    if ($fields === null) {
        Parser::throwInvalid(
            "`input $name` must define one or more input fields.",
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
    $definition['type'] = 'input';
    return [$definition, $substr];
}
