<?php

namespace GraphQL\Document\Parser\TypeSystem\Objects;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\Language\TypeReferences\Type;
use function GraphQL\Document\Parser\Language\Variables\DefaultValue;
use function GraphQL\Document\Parser\TypeSystem\Descriptions\Description;

// InputValueDefinition : Description? Name : Type DefaultValue?
//   Directives[Const]?
function InputValueDefinition($string)
{
    [$description, $substr] = Description($string);
    [$name, $nextSubstr] = Name($substr);
    if ($name === null) {
        if ($description !== null) {
            Parser::throwSyntax('Name', $substr);
        }

        return [null, $string];
    }

    [$colon, $nextSubstr] = Punctuator($nextSubstr);
    if ($colon !== ':') {
        Parser::throwSyntax(':', $substr);
    }

    [$type, $substr] = Type($nextSubstr);
    if ($type === null) {
        Parser::throwSyntax('Type', $substr);
    }

    [$default, $substr] = DefaultValue($substr);
    [$directives, $substr] = DirectivesConst($substr, 'ARGUMENT_DEFINITION');

    $definition = [];

    if ($default !== null) {
        $definition['default'] = $default;
    }

    if ($description !== null) {
        $definition['description'] = $description;
    }

    if ($directives !== null) {
        $definition['directives'] = $directives;
    }

    $definition['name'] = $name;
    $definition['type'] = $type;

    Parser::addInputType($definition, $nextSubstr);

    return [$definition, $substr];
}
