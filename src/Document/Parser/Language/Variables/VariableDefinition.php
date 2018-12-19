<?php

namespace GraphQL\Document\Parser\Language\Variables;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\DirectivesConst;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\Language\TypeReferences\Type;

// VariableDefinition : Variable : Type DefaultValue? Directives[Const]?
function VariableDefinition($string)
{
    [$name, $substr] = Variable($string);
    if ($name === null) {
        return [null, $string];
    }

    [$colon, $nextSubstr] = Punctuator($substr);
    if ($colon !== ':') {
        Parser::throwSyntax(':', $substr);
    }

    [$type, $substr] = Type($nextSubstr);
    if ($type === null) {
        Parser::throwSyntax('Type', $substr);
    }

    $definition = [];

    [$default, $substr] = DefaultValue($substr);
    if ($default !== null) {
        $definition['default'] = $default;
    }

    [$directives, $substr] = DirectivesConst($substr, 'VARIABLE_DEFINITION');
    if ($directives !== null) {
        $definition['directives'] = $directives;
    }

    $definition['name'] = $name['value'];
    $definition['type'] = $type;

    // used to indicate if this variable has been used in the scope
    $definition['__used'] = false;

    return [$definition, $substr];
}
