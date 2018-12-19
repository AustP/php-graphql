<?php

namespace GraphQL\Document\Parser\Language\Directives;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Arguments\Arguments;
use function GraphQL\Document\Parser\Language\Arguments\ArgumentsConst;
use function GraphQL\Document\Parser\Language\SourceText\Name;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;

// Directive[Const] : @ Name Arguments[?Const]?
function Directive($string, $location)
{
    [$at, $substr] = Punctuator($string);
    if ($at !== '@') {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    if (!isset(Parser::$schemaDocument[$name])) {
        Parser::throwInvalid(
            "Directive `@$name` is not defined in the schema.",
            $string
        );
    }

    $scope = Parser::$schemaDocument[$name];
    if (!in_array($location, $scope['locations'])) {
        Parser::throwInvalid(
            "Directive `@$name` cannot be used on `$location`.",
            $string
        );
    }

    $directive = [];

    [$arguments, $substr] = Arguments($substr, $scope);
    if ($arguments !== null) {
        $directive['arguments'] = $arguments;
    }

    $definitions = $scope['arguments'] ?? [];
    foreach ($definitions as $definition) {
        $default = $definition['default'] ?? null;
        $type = $definition['type'];

        if ($type['nullable'] === false && $default === null) {
            $argumentName = $definition['name'];
            $argument = $arguments[$argumentName] ?? null;
            if ($argument !== null && $argument['value']['value'] !== null) {
                continue;
            }

            Parser::throwInvalid(
                "Argument `$argumentName` is required on `@$name`.",
                $string
            );
        }
    }

    $directive['name'] = $name;

    return [$directive, $substr];
}

function DirectiveConst($string, $location)
{
    [$at, $substr] = Punctuator($string);
    if ($at !== '@') {
        return [null, $string];
    }

    [$name, $substr] = Name($substr);
    if ($name === null) {
        Parser::throwSyntax('Name', $substr);
    }

    $directive = [];
    [$arguments, $substr] = ArgumentsConst($substr);
    if ($arguments !== null) {
        $directive['arguments'] = $arguments;
    }

    $directive['location'] = $location;
    $directive['name'] = $name;

    Parser::addDirective($directive, $string);

    return [$directive, $substr];
}
