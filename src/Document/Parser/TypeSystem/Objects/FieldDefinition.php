<?php

namespace GraphQL\Document\Parser\TypeSystem\Objects;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\Language\TypeReferences\Type;
use function GraphQL\Document\Parser\TypeSystem\Descriptions\Description;

// FieldDefinition : Description? Name ArgumentsDefinition? : Type
//   Directives[Const]?
function FieldDefinition($string)
{
    [$description, $substr] = Description($string);
    [$name, $nextSubstr] = Name($substr);
    if ($name === null) {
        if ($description !== null) {
            Parser::throwSyntax('Name', $substr);
        }

        return [null, $string];
    }

    if (strpos($name, '__') === 0) {
        Parser::throwInvalid(
            "Field `{$name}` cannot start with __.",
            $substr
        );
    }

    [$arguments, $substr] = ArgumentsDefinition($nextSubstr);
    [$colon, $nextSubstr] = Punctuator($substr);
    if ($colon !== ':') {
        Parser::throwSyntax(':', $substr);
    }

    [$type, $substr] = Type($nextSubstr);
    if ($type === null) {
        Parser::throwSyntax('Type', $nextSubstr);
    }

    [$directives, $substr] = DirectivesConst($substr, 'FIELD_DEFINITION');

    $definition = [];

    if ($arguments !== null) {
        $definition['arguments'] = $arguments;
    }

    if ($description !== null) {
        $definition['description'] = $description;
    }

    if ($directives !== null) {
        $definition['directives'] = $directives;
    }

    $definition['name'] = $name;
    $definition['type'] = $type;

    Parser::addOutputType($definition, $nextSubstr);

    return [$definition, $substr];
}
