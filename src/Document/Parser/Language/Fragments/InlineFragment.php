<?php

namespace GraphQL\Document\Parser\Language\Fragments;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\Directives;
use function GraphQL\Document\Parser\Language\SelectionSets\SelectionSet;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;

// InlineFragment : ... TypeCondition? Directives? SelectionSet
function InlineFragment($string)
{
    [$ellipsis, $substr] = Punctuator($string);
    if ($ellipsis !== '...') {
        return [null, $string];
    }

    $typeSubstr = $substr;
    [$typeCondition, $substr] = TypeCondition($substr);
    [$directives, $substr] = Directives($substr, 'INLINE_FRAGMENT');

    $parentScope = Parser::$scope;
    $previousScope = null;
    if ($typeCondition !== null) {
        $previousScope = Parser::setScope($typeCondition['value'], $typeSubstr);
    }
    [$selectionSet, $substr] = SelectionSet($substr);
    if ($previousScope !== null) {
        Parser::setScope($previousScope);
    }

    if ($selectionSet === null) {
        Parser::throwSyntax('SelectionSet', $substr);
    }

    // NOTE: no need to call Parser::addSelectionSet because this selection
    // set will be included dynamically where it is invoked

    $fragment = [];
    if ($directives !== null) {
        $fragment['directives'] = $directives;
    }

    $fragment['selectionSet'] = $selectionSet;
    $fragment['type'] = 'fragment';

    if ($typeCondition !== null) {
        $fragment['typeCondition'] = $typeCondition['value'];
    }

    $fragment['__parentScope'] = $parentScope;

    Parser::addFragment($fragment, 'spread', $string);

    return [$fragment, $substr];
}
