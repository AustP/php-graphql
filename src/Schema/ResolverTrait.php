<?php

namespace GraphQL\Schema;

use GraphQL\Document\Exceptions\IncorrectResolverType;
use GraphQL\Document\Parser;
use GraphQL\Server;

trait ResolverTrait
{
    public $isResolver = true;

    protected $resolverErrors = [];

    /**
     * Adds an error that will be returned.
     *
     * @param  string $error The error message to be added.
     * @return void
     */
    protected function addResolverError($error)
    {
        $this->resolverErrors[] = $error;
    }

    /**
     * Executes the resolver.
     *
     * This is our implementation of ExecuteSelectionSet.
     *
     * @param  array $schema The schema document.
     * @param  array $document The query document.
     * @param  array $variables The variables to use when resolving.
     * @param  array $selectionSet The selection set to resolve.
     * @return array
     */
    public function executeResolver(
        $schema,
        $document,
        $variables,
        $selectionSet
    ) {
        $set = $selectionSet['set'];
        $scope = $selectionSet['scope'];

        $resultMap = [];
        $groupedSet = $this->resolverCollectFields(
            $document,
            $set,
            $scope,
            $variables
        );
        foreach ($groupedSet as $responseKey => $fields) {
            $fieldName = $fields[0]['name'];
            if ($fieldName === '__typename') {
                $resultMap[$responseKey] = Server::mapTypename(
                    $this,
                    $scope['name']
                );
                continue;
            }

            try {
                $resultMap[$responseKey] = $this->resolveFields(
                    $schema,
                    $document,
                    $scope,
                    $fields,
                    $variables
                );
            } catch (IncorrectResolverType $e) {
                // this exception keeps the field from being executed
                // unless the type is the correct type
            }
        }

        return $resultMap;
    }

    /**
     * The resolve method.
     *
     * This must be implemented by the user so the server knows how to resolve
     * individual fields. The server will validate the response after, so there
     * is no need to do it during resolution.
     *
     * @param  string $fieldName The field being resolved.
     * @param  array  $args The arguments supplied to the field.
     * @return mixed
     */
    abstract protected function resolve($fieldName, $args);

    /**
     * Begins the process of resolving the fields.
     *
     * This is our implementation of ExecuteField.
     *
     * @param  array $schema The schema document.
     * @param  array $document The query document.
     * @param  array $scope The current scope.
     * @param  array $fields The fields to resolve.
     * @param  array $variables The variables to use when resolving.
     * @return mixed
     */
    protected function resolveFields(
        $schema,
        $document,
        $scope,
        $fields,
        $variables
    ) {
        $field = $fields[0];

        // try to get a field that has the correct scope
        if ($scope['type'] === 'interface' || $scope['type'] === 'union') {
            $type = Server::mapTypename($this, $scope['name']);
            foreach ($fields as $fieldItem) {
                if ($fieldItem['__scope']['name'] === $type) {
                    $field = $fieldItem;
                    break;
                }
            }
        }

        $arguments = [];
        foreach ($field['arguments'] ?? [] as $argument) {
            $argumentName = $argument['name'];

            if ($argument['value']['type'] === 'Variable') {
                $default = $argument['value']['__type']['default'] ?? null;
                $coerced = $this->resolverCoerceValues(
                    $schema,
                    [[
                        'default' => $default,
                        'name' => $argument['name'],
                        'type' => $argument['value']['__type']['type'],
                        '__variable' => $argument['value']['value']
                    ]],
                    $variables
                );
                if ($coerced === []) {
                    continue;
                }

                $value = $coerced[$argumentName];
            } else {
                $value = $this->resolverGetValue(
                    $argument['value'],
                    $variables
                );
            }

            $arguments[$argumentName] = $value;
        }

        $fieldName = $field['name'];

        $args = $field['__scope']['fields'][$fieldName]['arguments'] ?? [];
        foreach ($args as $arg) {
            if (isset($arg['default']) && !isset($arguments[$arg['name']])) {
                $arguments[$arg['name']] = $this->resolverGetValue(
                    $arg['default'],
                    $variables
                );
            }
        }

        // if the scopes don't match, it means we are dealing with
        // unions or interfaces. throwing an IncorrectResolverType
        // exception indicates not to include the value in the result map
        if ($field['__scope']['name'] !== $scope['name']) {
            if ($field['__scope']['name'] !== $type) {
                throw new IncorrectResolverType();
            }
        }

        $resolvedValue = $this->resolve($fieldName, $arguments);

        $fieldType = $field['__scope']['fields'][$field['name']]['type'];

        return $this->resolverCompleteValue(
            $schema,
            $document,
            $fieldType,
            $fields,
            $resolvedValue,
            $variables
        );
    }

    /**
     * Coerces the input value.
     *
     * @param  array  $schema The schema document.
     * @param  array  $type The type definition.
     * @param  string $name The name of the input.
     * @param  mixed  $value The value of the input.
     * @return mixed
     */
    protected function resolverCoerceInput($schema, $type, $name, $value)
    {
        $coerced = Parser::coerceInput(
            $schema,
            $type,
            [
                'name' => $name,
                'value' => ['value' => $value]
            ]
        );

        return $coerced['value'] ?? null;
    }

    /**
     * Coerces the result.
     *
     * Result coercion is turned off by default because we blindly coerce the
     * value which could result in data loss. If the user understands this, they
     * can set Parser::$useResultCoerction to be true to turn it on.
     *
     * If the result type is an enum or a scalar, the result can be a resolver.
     * The resolver method will be called on the result and the response will
     * be returned.
     *
     * @param  string $fieldName The field being coerced.
     * @param  array  $type The type definition.
     * @param  array  $object The object associated with the result.
     * @param  mixed  $value The result.
     * @return mixed
     */
    protected function resolverCoerceResult($fieldName, $type, $object, $value)
    {
        if ($object['type'] === 'enum') {
            if (is_object($value) && $value->isResolver) {
                $value = $value->resolve($object['values'], $object);
            }
        }

        if ($object['type'] === 'scalar') {
            if (is_object($value) && $value->isResolver) {
                $value = $value->resolve($object);
            }
        }

        if (Server::$useResultCoercion === false) {
            $valueType = $object['name'] ?? Parser::getInputType($value, null);
            if ($type !== $valueType &&
                !($type === 'ID' && $valueType === 'String')
            ) {
                Server::throwError(
                    "Expected `$fieldName` to return `$type`, but it " .
                    "returned `$valueType` instead."
                );
            }

            return $value;
        }

        if ($type === 'Boolean') {
            return (bool)$value;
        }

        if ($type === 'Float') {
            return (float)$value;
        }

        if ($type === 'ID') {
            return (string)$value;
        }

        if ($type === 'Int') {
            return (int)$value;
        }

        if ($type === 'String') {
            return (string)$value;
        }

        if ($object['type'] === 'enum') {
            if (isset($object['values'][$value])) {
                return (string)$value;
            }
        }

        if ($object['type'] === 'scalar') {
            // The user defines any scalars, and return values for them.
            // No need to try to coerce it.
            return $value;
        }

        Server::throwError("Could not coerce `$fieldName` to `$type`.");
    }

    /**
     * Coerces the argument or variable values.
     *
     * This is our implementation of CoerceArgumentValues and
     * CoerceVariableValues.
     *
     * @param  array $schema The schema document.
     * @param  array $definitions The definitions to compare against.
     * @param  array $variables The variables or arguments to coerce.
     * @return array
     */
    protected function resolverCoerceValues($schema, $definitions, $variables)
    {
        $coercedValues = [];
        foreach ($definitions as $definition) {
            $resultKey = $definition['name'];
            $variableName = $definition['__variable'] ?? $definition['name'];
            $variableType = $definition['type'];

            $hasValue = array_key_exists($variableName, $variables);
            $value = $variables[$variableName] ?? null;

            if ($hasValue === false && isset($definition['default'])) {
                $coercedValues[$resultKey] = $this->resolverGetValue(
                    $definition['default'],
                    $variables
                );
            } elseif ((
                $variableType['nullable'] === false
            ) && (
                $hasValue === false ||
                $value === null
            )) {
                Server::throwQuery("Variable `$resultKey` cannot be null.");
            } elseif ($hasValue === true) {
                if ($value === null) {
                    $coercedValues[$resultKey] = null;
                }

                $coercedValue = $this->resolverCoerceInput(
                    $schema,
                    $variableType,
                    '$' . $resultKey,
                    $value
                );
                if ($coercedValue === null) {
                    Server::throwQuery(
                        "Variable `$resultKey` could not be coerced."
                    );
                }

                $coercedValues[$resultKey] = $coercedValue;
            }
        }

        return $coercedValues;
    }

    /**
     * Collects the fields into a grouped set.
     *
     * @param  array $document The query document.
     * @param  array $set The selections to collect.
     * @param  array $scope The scope for the selections.
     * @param  array $variables The variables for the directives.
     * @param  array $fragments The fragments that have been included.
     * @return mixed
     */
    public function resolverCollectFields(
        $document,
        $set,
        $scope,
        $variables,
        &$fragments = []
    ) {
        $groupedFields = [];
        foreach ($set as $selection) {
            $skipDirective = $selection['directives']['skip'] ?? null;
            if ($skipDirective) {
                $skip = $this->resolverGetValue(
                    $skipDirective['arguments']['if']['value'],
                    $variables
                );
                if ($skip) {
                    continue;
                }
            }

            $includeDirective = $selection['directives']['include'] ?? null;
            if ($includeDirective) {
                $include = $this->resolverGetValue(
                    $includeDirective['arguments']['if']['value'],
                    $variables
                );
                if ($include === false) {
                    continue;
                }
            }

            if ($selection['type'] === 'field') {
                $responseKey = $selection['alias'] ?? $selection['name'];
                if (!isset($groupedFields[$responseKey])) {
                    $groupedFields[$responseKey] = [];
                }

                $selection['__scope'] = $scope;
                $groupedFields[$responseKey][] = $selection;
            } elseif ($selection['type'] === 'spread') {
                $fragmentName = $selection['name'];
                if (isset($fragments[$fragmentName])) {
                    continue;
                }

                $fragments[$fragmentName] = true;
                $fragment = $document[$fragmentName];

                $fragmentGroupedFields = $this->resolverCollectFields(
                    $document,
                    $fragment['selectionSet']['set'],
                    $fragment['selectionSet']['scope'],
                    $variables,
                    $fragments
                );
                foreach ($fragmentGroupedFields as $key => $fragmentGroup) {
                    if (!isset($groupedFields[$key])) {
                        $groupedFields[$key] = [];
                    }

                    foreach ($fragmentGroup as $subSelection) {
                        $groupedFields[$key][] = $subSelection;
                    }
                }
            } elseif ($selection['type'] === 'fragment') {
                $fragmentGroupedFields = $this->resolverCollectFields(
                    $document,
                    $selection['selectionSet']['set'],
                    $selection['selectionSet']['scope'],
                    $variables,
                    $fragments
                );
                foreach ($fragmentGroupedFields as $key => $fragmentGroup) {
                    if (!isset($groupedFields[$key])) {
                        $groupedFields[$key] = [];
                    }

                    foreach ($fragmentGroup as $subSelection) {
                        $groupedFields[$key][] = $subSelection;
                    }
                }
            }
        }

        return $groupedFields;
    }

    /**
     * Make sure the result type is correct and if the result is a resolver,
     * continue the resolution process recursively.
     *
     * @param  array $schema The schema document.
     * @param  array $document The query document.
     * @param  array $fieldType The field's type definition.
     * @param  array $fields The fields to complete.
     * @param  mixed $result The result of the resolution.
     * @param  array $variables The available variables.
     * @return mixed
     */
    protected function resolverCompleteValue(
        $schema,
        $document,
        $fieldType,
        $fields,
        $result,
        $variables
    ) {
        $fieldName = $fields[0]['name'];

        if ($fieldType['nullable'] === false) {
            $fieldType['nullable'] = true;
            $completedResult = $this->resolverCompleteValue(
                $schema,
                $document,
                $fieldType,
                $fields,
                $result,
                $variables
            );
            if ($completedResult === null) {
                $this->addResolverError("`$fieldName` cannot be null.");
            }

            return $completedResult;
        }

        if ($result === null) {
            return null;
        }

        if (is_array($fieldType['value'])) {
            if (!is_iterable($result)) {
                $this->addResolverError("`$fieldName` must be a list.");
                return $result;
            }

            $innerType = $fieldType['value'];

            $completedResult = [];
            foreach ($result as $resultItem) {
                $completedResult[] = $this->resolverCompleteValue(
                    $schema,
                    $document,
                    $innerType,
                    $fields,
                    $resultItem,
                    $variables
                );
            }

            return $completedResult;
        }

        $object = $schema[$fieldType['value']] ?? null;

        if ((
            $fieldType['value'] === 'Boolean' ||
            $fieldType['value'] === 'Float' ||
            $fieldType['value'] === 'ID' ||
            $fieldType['value'] === 'Int' ||
            $fieldType['value'] === 'String'
        ) || (
            $object['type'] === 'enum' ||
            $object['type'] === 'scalar'
        )) {
            return $this->resolverCoerceResult(
                $fieldName,
                $fieldType['value'],
                $object,
                $result
            );
        }

        if ((
            $object['type'] === 'interface' ||
            $object['type'] === 'type' ||
            $object['type'] === 'union'
        )) {
            $isResolver = $result->isResolver ?? false;
            if (!$isResolver) {
                Server::throwError("`$fieldName` must be a resolver.");
            }

            $subSelectionSet = $this->resolverMergeSelectionSets($fields);
            return $result->executeResolver(
                $schema,
                $document,
                $variables,
                $subSelectionSet
            );
        }

        $this->addResolverError("`$fieldName` returned an invalid response.");
        return $result;
    }

    /**
     * Gets the errors that have occurred during the resolution process.
     *
     * @return array
     */
    protected function resolverGetErrors()
    {
        return array_map(function ($error) {
            return ['message' => $error];
        }, $this->resolverErrors);
    }

    /**
     * Gets the value for the value definition.
     *
     * @param  array $value The value definition.
     * @param  array $variables The available variables.
     * @return mixed
     */
    protected function resolverGetValue($value, $variables)
    {
        if ($value['type'] === 'Variable') {
            return $variables[$value['value']];
        }

        if ($value['type'] === 'Object') {
            $object = [];
            foreach ($value['value'] as $name => $type) {
                $object[$name] = $this->resolverGetValue($type, $variables);
            }

            return $object;
        }

        return $value['value'];
    }

    /**
     * Merges the selection sets of the fields.
     *
     * @param  array $fields The fields to use.
     * @return array
     */
    protected function resolverMergeSelectionSets($fields)
    {
        $selectionSet = [];
        foreach ($fields as $field) {
            if (isset($field['selectionSet'])) {
                if (!isset($selectionSet['scope'])) {
                    $selectionSet['scope'] = $field['selectionSet']['scope'];
                    $selectionSet['set'] = [];
                }

                foreach ($field['selectionSet']['set'] as $selection) {
                    $selectionSet['set'][] = $selection;
                }
            }
        }

        return $selectionSet;
    }
}
