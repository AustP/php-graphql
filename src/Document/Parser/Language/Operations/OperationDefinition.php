<?php

namespace GraphQL\Document\Parser\Language\Operations;

use GraphQL\Document\Parser;
use GraphQL\Document\Parser\Validator;
use function GraphQL\Document\Parser\Language\Directives\Directives;
use function GraphQL\Document\Parser\Language\SelectionSets\SelectionSet;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\Language\Variables\VariableDefinitions;

/*
OperationDefinition :
- OperationType Name? VariableDefinitions? Directives? SelectionSet
- SelectionSet
 */
function OperationDefinition($string)
{
    Parser::setScope('__query', $string);
    [$selectionSet, $substr] = SelectionSet($string);

    if ($selectionSet !== null) {
        Parser::addSelectionSet($selectionSet, $string);

        return [['selectionSet' => $selectionSet, 'type' => 'query'], $substr];
    }

    [$type, $substr] = OperationType($string);
    if ($type === null) {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    $variableSubstr = $substr;
    [$variables, $substr] = VariableDefinitions($substr);
    [$directives, $substr] = Directives($substr, strtoupper($type));

    Parser::setVariableDefinitions($variables ?? []);

    Parser::setScope('__' . $type);
    [$selectionSet, $nextSubstr] = SelectionSet($substr);

    if ($selectionSet === null) {
        Parser::throwSyntax('SelectionSet', $substr);
    }

    Parser::addSelectionSet($selectionSet, $substr);

    $definition = [];
    if ($directives !== null) {
        $definition['directives'] = $directives;
    }

    if ($name !== null) {
        $definition['name'] = $name;
    }

    $definition['selectionSet'] = $selectionSet;
    $definition['type'] = $type;

    if ($variables !== null) {
        $definition['variables'] = $variables;
        $definition['__variableDefinitions'] = Parser::$variableDefinitions;
    }

    $definition['__fragmentSpreads'] = Parser::getFragmentSpreads();

    Parser::addOperation($definition, $variableSubstr);

    return [$definition, $nextSubstr];
}
