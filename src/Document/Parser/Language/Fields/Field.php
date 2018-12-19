<?php

namespace GraphQL\Document\Parser\Language\Fields;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\Arguments\Arguments;
use function GraphQL\Document\Parser\Language\Directives\Directives;
use function GraphQL\Document\Parser\Language\SelectionSets\SelectionSet;
use function GraphQL\Document\Parser\Language\SourceText\Name;

// Field : Alias? Name Arguments? Directives? SelectionSet?
function Field($string)
{
    [$alias, $substr] = Alias($string);
    [$name, $substr] = Name($substr);
    if ($name === null) {
        if ($alias !== null) {
            Parser::throwSyntax('Name', $substr);
        }

        return [null, $string];
    }

    Parser::addPath($alias ?? $name);

    if (!isset(Parser::$scope['fields'][$name])) {
        $currentScope = isset(Parser::$scope['name']) ?
            ('`' . Parser::$scope['name'] . '`') :
            'the current scope';

        if ($name !== '__typename') {
            $scopeType = Parser::$scope['type'] ?? null;
            if ($scopeType === 'union') {
                $types = '';
                $typeNames = array_keys(Parser::$scope['types']);
                foreach ($typeNames as $i => $type) {
                    if ($types) {
                        if (count($typeNames) !== 2) {
                            $types .= ',';
                        }

                        if ($i + 1 === count($typeNames)) {
                            $types .= ' or ';
                        } else {
                            $types .= ' ';
                        }
                    }

                    $types .= '`' . $type . '`';
                }

                Parser::throwInvalid(
                    "The field `$name` is not defined in $currentScope. Did " .
                    "you mean to use an inline fragment on $types?",
                    $string
                );
            }

            Parser::throwInvalid(
                "The field `$name` is not defined in $currentScope.",
                $string
            );
        }
    }

    $argumentScope = Parser::$scope['fields'][$name] ?? [];
    $argumentSubstr = $substr;
    [$arguments, $substr] = Arguments($substr, $argumentScope);

    $definitions = $argumentScope['arguments'] ?? [];
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
                "Argument `$argumentName` is required on `$name`.",
                $argumentSubstr
            );
        }
    }

    [$directives, $substr] = Directives($substr, 'FIELD');

    $previousScope = Parser::setScopeForField($name, $string);
    [$selectionSet, $substr] = SelectionSet($substr);
    $selectionScope = Parser::setScope($previousScope);

    $field = [];
    if ($alias !== null) {
        $field['alias'] = $alias;
    }

    if ($arguments !== null) {
        $field['arguments'] = $arguments;
        foreach ($arguments as $argument) {
            if ($argument['value']['type'] === 'Object') {
                CheckObject($argument['value'], $argumentSubstr);
            }
        }
    }

    if ($directives !== null) {
        $field['directives'] = $directives;
    }

    $field['name'] = $name;

    if ($selectionSet !== null) {
        $field['selectionSet'] = $selectionSet;

        // NOTE: no need to call Parser::addSelectionSet because this selection
        // set will be included dynamically where it is invoked
    } else {
        $type = $selectionScope['type'] ?? '';
        if ((
            $type === 'interface' ||
            $type === 'type' ||
            $type === 'union'
        ) && (
            $name !== '__typename'
        )) {
            Parser::throwInvalid(
                "Selections are required on `$name`.",
                $string
            );
        }
    }

    $field['type'] = 'field';

    Parser::removePath();

    return [$field, $substr];
}

function CheckObject($type, $substr)
{
    $definition = $type['__type'];

    $fields = $type['value'];
    $name = $definition['name'];
    $object = Parser::$schemaDocument[$definition['type']['value']];

    foreach ($object['fields'] as $fieldDefinition) {
        $default = $fieldDefinition['default'] ?? null;
        $fieldName = $fieldDefinition['name'];
        $expectedType = $fieldDefinition['type'];
        if ($expectedType['nullable'] === false && $default === null) {
            $field = $fields[$fieldName] ?? null;
            $value = $field['value'] ?? null;
            if ($value === null) {
                Parser::throwInvalid(
                    "Field `$fieldName` is required on `$name`.",
                    $substr
                );
            }

            if ($field['type'] === 'Object') {
                CheckObject($field, $substr);
            }
        }
    }
}
