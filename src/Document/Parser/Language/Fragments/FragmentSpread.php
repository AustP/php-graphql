<?php

namespace GraphQL\Document\Parser\Language\Fragments;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Directives\Directives;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;

// FragmentSpread : ... FragmentName Directives?
function FragmentSpread($string)
{
    [$ellipsis, $substr] = Punctuator($string);
    if ($ellipsis !== '...') {
        return [null, $string];
    }

    [$name, $substr] = FragmentName($substr);
    if ($name === null) {
        Parser::throwSyntax('FragmentName', $substr);
    }

    $spread = [];

    [$directives, $substr] = Directives($substr, 'FRAGMENT_SPREAD');
    if ($directives !== null) {
        $spread['directives'] = $directives;
    }

    $spread['name'] = $name;
    $spread['type'] = 'spread';

    $spread['__parentScope'] = Parser::$scope;

    Parser::addFragment($spread, 'spread', $string);

    return [$spread, $substr];
}
