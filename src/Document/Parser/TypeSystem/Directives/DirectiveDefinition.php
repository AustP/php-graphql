<?php

namespace GraphQL\Document\Parser\TypeSystem\Directives;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\TypeSystem\Descriptions\Description;
use function GraphQL\Document\Parser\TypeSystem\Objects\ArgumentsDefinition;
use function GraphQL\Document\Parser\util\Keyword;

// DirectiveDefinition : Description? directive @ Name ArgumentsDefinition?
//   on DirectiveLocations
function DirectiveDefinition($string)
{
    [$description, $substr] = Description($string);
    [$directive, $substr] = Keyword($substr, 'directive');
    if ($directive !== 'directive') {
        return [null, $string];
    }

    [$at, $nextSubstr] = Punctuator($substr);
    if ($at !== '@') {
        Parser::throwSyntax('@', $substr);
    }

    [$name, $nextSubstr] = Name($nextSubstr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    if (strpos($name, '__') === 0) {
        Parser::throwInvalid(
            "Directive `$name` cannot start with `__`.",
            $substr
        );
    }

    [$arguments, $substr] = ArgumentsDefinition($nextSubstr);
    [$on, $substr] = Keyword($substr, 'on');
    if ($on !== 'on') {
        Parser::throwSyntax('on', $substr);
    }

    [$locations, $substr] = DirectiveLocations($substr);
    if ($locations === null) {
        Parser::throwSyntax('DirectiveLocations', $substr);
    }

    $definition = [];

    if ($arguments !== null) {
        $definition['arguments'] = $arguments;
    }

    if ($description !== null) {
        $definition['description'] = $description;
    }

    $definition['locations'] = $locations;
    $definition['name'] = $name;
    $definition['type'] = 'directive';

    return [$definition, $substr];
}
