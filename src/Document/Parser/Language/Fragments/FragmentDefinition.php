<?php

namespace GraphQL\Document\Parser\Language\Fragments;

use GraphQL\Document\Parser;
use GraphQL\Document\Parser\Validator;
use function GraphQL\Document\Parser\Language\Directives\Directives;
use function GraphQL\Document\Parser\Language\SelectionSets\SelectionSet;
use function GraphQL\Document\Parser\util\Keyword;

// FragmentDefinition : fragment FragmentName TypeCondition Directives?
//   SelectionSet
function FragmentDefinition($string)
{
    [$fragment, $substr] = Keyword($string, 'fragment');
    if ($fragment !== 'fragment') {
        return [null, $string];
    }

    [$name, $substr] = FragmentName($substr);
    if ($name === null) {
        Parser::throwSyntax('FragmentName', $substr);
    }

    $typeSubstr = $substr;
    [$typeCondition, $substr] = TypeCondition($substr);
    if ($typeCondition === null) {
        Parser::throwSyntax('TypeCondition', $substr);
    }

    [$directives, $substr] = Directives($substr, 'FRAGMENT_DEFINITION');

    $previousScope = Parser::setScope($typeCondition['value'], $typeSubstr);
    [$selectionSet, $nextSubstr] = SelectionSet($substr);
    Parser::setScope($previousScope);

    if ($selectionSet === null) {
        Parser::throwSyntax('SelectionSet', $substr);
    }

    Parser::addSelectionSet($selectionSet, $substr);

    $definition = [];
    if ($directives !== null) {
        $definition['directives'] = $directives;
    }

    $definition['name'] = $name;
    $definition['selectionSet'] = $selectionSet;
    $definition['typeCondition'] = $typeCondition['value'];
    $definition['type'] = 'fragment';

    Parser::addFragment($definition, 'definition', $string);

    return [$definition, $nextSubstr];
}
