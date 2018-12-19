<?php

namespace GraphQL\Document\Parser;

use GraphQL\Document\Parser;

class Validator
{
    public static $mustUseFragment = true;

    protected static $document = [];
    protected static $schemaDocument = [];

    /**
     * Checks to see if the types are compatible.
     *
     * @param  array $variableType The variable type definition.
     * @param  array $locationType The location type definition.
     * @return bool
     */
    protected static function areTypesCompatible($variableType, $locationType)
    {
        if ($locationType['nullable'] === false) {
            if ($variableType['nullable'] === true) {
                return false;
            }

            $locationType['nullable'] = true;
            return static::areTypesCompatible(
                $variableType,
                $locationType
            );
        } elseif ($variableType['nullable'] === false) {
            $variableType['nullable'] = true;
            return static::areTypesCompatible(
                $variableType,
                $locationType
            );
        } elseif (is_array($locationType['value'])) {
            if (!is_array($variableType['value'])) {
                return false;
            }

            return static::areTypesCompatible(
                $variableType['value'],
                $locationType['value']
            );
        } elseif (is_array($variableType['value'])) {
            return false;
        }

        return $variableType === $locationType;
    }

    /**
     * Detects if the fragment definitions have cyclical dependencies.
     *
     * @param  array $definition The fragment definition.
     * @param  array $visited A set of visited fragments.
     * @return void
     */
    protected static function detectCycles($definition, $visited)
    {
        foreach ($definition['selectionSet']['set'] as $selection) {
            if ($selection['type'] === 'spread') {
                $name = $selection['name'];
                if (isset($visited[$name])) {
                    Parser::throwInvalid(
                        "Including `$name` in `{$definition['name']}` causes " .
                            "a cyclic dependency."
                    );
                }

                $visited[$name] = true;
                static::detectCycles(static::$document[$name], $visited);
            } elseif (isset($selection['selectionSet'])) {
                static::detectCycles($selection, $visited);
            }
        }
    }

    /**
     * Checks to see if the fields in the supplied set can merge.
     *
     * @param  array $set The set to check.
     * @return bool
     */
    protected static function fieldsInSetCanMerge($set)
    {
        $fieldsForName = static::getFieldsForName($set, true);
        foreach ($fieldsForName as $name => $fields) {
            if (count($fields) >= 2) {
                for ($i = 0, $l = count($fields) - 1; $i < $l; $i++) {
                    $fieldA = $fields[$i];
                    $fieldB = $fields[$i + 1];

                    if (!static::sameResponseShape($fieldA, $fieldB)) {
                        return false;
                    }

                    $objectA = $fieldA['scope'];
                    $objectB = $fieldB['scope'];

                    // If the parent types of {fieldA} and {fieldB} are equal or
                    // if either is not an Object Type:
                    if ((
                        $objectA['name'] === $objectB['name']
                    ) || (
                        $objectA['type'] !== 'type' ||
                        $objectB['type'] !== 'type'
                    )) {
                        if ($fieldA['name'] !== $fieldB['name']) {
                            return false;
                        }

                        $argsA = $fieldA['arguments'] ?? [];
                        $argsB = $fieldB['arguments'] ?? [];
                        if ($argsA !== $argsB) {
                            return false;
                        }

                        $setA = $fieldA['selectionSet'] ?? [];
                        $setB = $fieldB['selectionSet'] ?? [];

                        if (!static::fieldsInSetCanMerge(
                            array_merge($setA, $setB)
                        )) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Gets the set of selections with a given response name
     *
     * @param  array $info Contains the selection set and remaining string.
     * @param  bool  $validate Whether selection set validation should happen.
     * @return mixed
     */
    protected static function getFieldsForName($info, $validate = false)
    {
        $set = $info['selectionSet']['set'] ?? [];

        $fieldsForName = [];
        for ($i = 0, $l = count($set); $i < $l; $i++) {
            $selection = $set[$i];
            if ($selection['type'] === 'field') {
                $responseName = $selection['alias'] ?? $selection['name'];

                if (!isset($fieldsForName[$responseName])) {
                    $fieldsForName[$responseName] = [];
                }

                if (!isset($selection['scope'])) {
                    $selection['scope'] = $info['selectionSet']['scope'];
                }

                $fieldsForName[$responseName][] = $selection;

                if ($validate && isset($selection['selectionSet'])) {
                    static::validateSelectionSets([
                        [
                            'selectionSet' => $selection['selectionSet'],
                            'string' => $info['string']
                        ]
                    ]);
                }
            } elseif ($selection['type'] === 'fragment') {
                foreach ($selection['selectionSet']['set'] as $subSelection) {
                    if (isset($selection['typeCondition'])) {
                        $type = $selection['typeCondition'];
                        $scope = static::$schemaDocument[$type];
                    } else {
                        $scope = $selection['selectionSet']['scope'];
                    }

                    $subSelection['scope'] = $scope;
                    $set[] = $subSelection;
                    $l++;
                }
            } elseif ($selection['type'] === 'spread') {
                $fragment = static::$document[$selection['name']];
                $scope = $fragment['selectionSet']['scope'];
                foreach ($fragment['selectionSet']['set'] as $subSelection) {
                    $subSelection['scope'] = $scope;
                    $set[] = $subSelection;
                    $l++;
                }
            }
        }

        return $fieldsForName;
    }

    /**
     * Gets the possible types for the suppled schema type.
     *
     * @param  array $schemaType The schema type to check.
     * @return array
     */
    protected static function getPossibleTypes($schemaType)
    {
        $schemaObject = static::$schemaDocument[$schemaType];
        if ($schemaObject['type'] === 'interface') {
            $implementing = [];
            foreach (static::$schemaDocument as $type => $object) {
                if (isset($object['implements'][$schemaObject['name']])) {
                    $implementing[] = $type;
                }
            }

            return $implementing;
        } elseif ($schemaObject['type'] === 'type') {
            return [$schemaObject['name']];
        } elseif ($schemaObject['type'] === 'union') {
            return array_keys($schemaObject['types']);
        }

        return [];
    }

    /**
     * Checks to see if the supplied type is an input type.
     *
     * @param  array $type The type to check.
     * @return bool
     */
    public static function isInputType($type)
    {
        $value = $type['value'];
        if (is_array($value)) {
            return static::isInputType($value);
        }

        $definition = static::$document[$value] ??
            static::$schemaDocument[$value] ?? null;
        if ($definition !== null) {
            return $definition['type'] === 'enum' ||
                $definition['type'] === 'input' ||
                $definition['type'] === 'scalar';
        }

        return $value === 'Boolean' ||
            $value === 'Float' ||
            $value === 'ID' ||
            $value === 'Int' ||
            $value === 'String';
    }

    /**
     * Checks to see if the supplied type is an output type.
     *
     * @param  array $type The type to check.
     * @return bool
     */
    protected static function isOutputType($type)
    {
        $value = $type['value'];
        if (is_array($value)) {
            return static::isOutputType($value);
        }

        $definition = static::$document[$value] ??
            static::$schemaDocument[$value] ?? null;
        if ($definition !== null) {
            return $definition['type'] === 'enum' ||
                $definition['type'] === 'interface' ||
                $definition['type'] === 'scalar' ||
                $definition['type'] === 'type' ||
                $definition['type'] === 'union';
        }

        return $value === 'Boolean' ||
            $value === 'Float' ||
            $value === 'ID' ||
            $value === 'Int' ||
            $value === 'String';
    }

    /**
     * Checks to see if the implenter type is equal to or a sub-type of the
     * interface type.
     *
     * @param  array $implementerType The implementer type.
     * @param  array $interfaceType The interface type.
     * @return bool
     */
    protected function isTypeCovariant($implementerType, $interfaceType)
    {
        $implementerNullable = $implementerType['nullable'];
        $interfaceNullable = $interfaceType['nullable'];

        $implementerValue = $implementerType['value'];
        $interfaceValue = $interfaceType['value'];

        if (is_string($implementerValue) && is_string($interfaceValue)) {
            if ($implementerValue === $interfaceValue) {
                return $implementerNullable === $interfaceNullable ||
                    $implementerNullable === false;
            }

            $implementer = static::$document[$implementerValue] ?? null;
            $interface = static::$document[$interfaceValue] ?? null;
            if ($implementer === null || $interface === null) {
                return false;
            }

            if ($implementer['type'] !== 'type') {
                return false;
            }

            if ($interface['type'] === 'interface') {
                return isset($implementer['implements'][$interfaceValue]);
            }

            if ($interface['type'] === 'union') {
                return isset($interface['types'][$implementerValue]);
            }
        }

        if (is_array($implementerValue) && is_array($interfaceValue)) {
            $covariant = static::isTypeCovariant(
                $implementerValue,
                $interfaceValue
            );
            if ($covariant) {
                return $implementerNullable === $interfaceNullable ||
                    $implementerNullable === false;
            }
        }

        return false;
    }

    /**
     * Checks to see if the implenter type is equal to the interface type.
     *
     * @param  array $implementerType The implementer type.
     * @param  array $interfaceType The interface type.
     * @return bool
     */
    protected function isTypeInvariant($implementerType, $interfaceType)
    {
        $implementerNullable = $implementerType['nullable'];
        $interfaceNullable = $interfaceType['nullable'];

        $implementerValue = $implementerType['value'];
        $interfaceValue = $interfaceType['value'];

        if (is_string($implementerValue) && is_string($interfaceValue)) {
            return $implementerValue === $interfaceValue &&
                $implementerNullable === $interfaceNullable;
        }

        if (is_array($implementerValue) && is_array($interfaceValue)) {
            $invariant = static::isTypeInvariant(
                $implementerValue,
                $interfaceValue
            );

            return $invariant && $implementerNullable === $interfaceNullable;
        }

        return false;
    }

    /**
     * Checks to see if the variable usage is allowed.
     *
     * @param  array $variableDefinition The definition to check against.
     * @param  array $variableUsage The variable usage.
     * @return bool
     */
    protected static function isVariableUsageAllowed(
        $variableDefinition,
        $variableUsage
    ) {
        $variableType = $variableDefinition['type'];
        $locationType = $variableUsage['type'];

        if ($locationType['nullable'] === false &&
            $variableType['nullable'] === true
        ) {
            if ((
                ($variableDefinition['default']['value'] ?? null) === null
            ) && (
                !isset($variableUsage['default'])
            )) {
                return false;
            }

            $locationType['nullable'] = true;
            return static::areTypesCompatible($variableType, $locationType);
        }

        return static::areTypesCompatible($variableType, $locationType);
    }

    /**
     * Checks to see if the fields have the same response shape.
     *
     * @param  array $fieldA The first field.
     * @param  array $fieldB The second field.
     * @return bool
     */
    protected static function sameResponseShape($fieldA, $fieldB)
    {
        if ((
            $fieldA['name'] === '__typename' &&
            $fieldB['name'] === '__typename'
        )) {
            return true;
        }

        $typeA = $fieldA['scope']['fields'][$fieldA['name']]['type'];
        $typeB = $fieldB['scope']['fields'][$fieldB['name']]['type'];

        while (true) {
            if ($typeA['nullable'] === false || $typeB['nullable'] === false) {
                if ($typeA['nullable'] || $typeB['nullable']) {
                    return false;
                }
            }

            if (is_array($typeA['value']) || is_array($typeB['value'])) {
                if (!is_array($typeA['value']) || !is_array($typeB['value'])) {
                    return false;
                }

                $typeA = $typeA['value'];
                $typeB = $typeB['value'];

                continue;
            }

            break;
        }

        if ((
            $typeA['value'] === 'Boolean' ||
            $typeA['value'] === 'Float' ||
            $typeA['value'] === 'ID' ||
            $typeA['value'] === 'Int' ||
            $typeA['value'] === 'String'
        ) || (
            $typeB['value'] === 'Boolean' ||
            $typeB['value'] === 'Float' ||
            $typeB['value'] === 'ID' ||
            $typeB['value'] === 'Int' ||
            $typeB['value'] === 'String'
        )) {
            return $typeA['value'] === $typeB['value'];
        }

        $objectA = static::$schemaDocument[$typeA['value']];
        $objectB = static::$schemaDocument[$typeB['value']];

        if ((
            $objectA['type'] === 'enum' ||
            $objectA['type'] === 'scalar'
        ) || (
            $objectB['type'] === 'enum' ||
            $objectB['type'] === 'scalar'
        )) {
            return $objectA['type'] === $objectB['type'];
        }

        if ((
            $objectA['type'] !== 'interface' &&
            $objectA['type'] !== 'type'
        ) || (
            $objectB['type'] !== 'interface' &&
            $objectB['type'] !== 'type'
        )) {
            return false;
        }

        $fieldsForName = static::getFieldsForName(
            array_merge($fieldA['selectionSet'], $fieldB['selectionSet'])
        );
        foreach ($fieldsForName as $name => $fields) {
            if (count($fields) >= 2) {
                for ($i = 0, $l = count($fields) - 1; $i < $l; $i++) {
                    $subfieldA = $fields[$i];
                    $subfieldB = $fields[$i + 1];

                    if (!static::sameResponseShape($subfieldA, $subfieldB)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Sets the current document.
     *
     * @param  array $document The document.
     * @return void
     */
    public static function setDocument($document)
    {
        static::$document = $document;
    }

    /**
     * Sets the schema document.
     *
     * @param  array $document The schema document.
     * @return void
     */
    public static function setSchemaDocument($document)
    {
        static::$schemaDocument = $document;
    }

    /**
     * Validates the directives.
     *
     * @param  array $directives The directives to validate.
     * @return void
     */
    public static function validateDirectives($directives)
    {
        foreach ($directives as $details) {
            $directive = $details['directive'];
            $name = $directive['name'];
            $location = $directive['location'];

            $definition = static::$document[$name];
            if (!in_array($location, $definition['locations'])) {
                Parser::throwInvalid(
                    "Directive `@$name` cannot be used on `$location`.",
                    $string
                );
            }
        }
    }

    /**
     * Validates the fragments.
     *
     * @param  array $fragments The fragments to validate.
     * @return void
     */
    public static function validateFragments($fragments)
    {
        $visited = [];
        foreach ($fragments['definition'] as $name => $definition) {
            if ((
                !isset($fragments['spread'][$name]) &&
                static::$mustUseFragment
            )) {
                Parser::throwInvalid(
                    "Fragment `$name` must be used in the document.",
                    $definition['__string']
                );
            }

            static::detectCycles($definition, $visited);
        }

        foreach ($fragments['spread'] as $name => $spread) {
            if ((
                strpos($name, '__') === false &&
                !isset($fragments['definition'][$name])
            )) {
                Parser::throwInvalid(
                    "Fragment `$name` is not defined in the document.",
                    $spread['__string']
                );
            }

            if ($spread['type'] === 'spread') {
                $fragment = static::$document[$spread['name']];
                $fragmentType = $fragment['typeCondition'];
                $parentType = $spread['__parentScope']['name'];
            } elseif ($spread['type'] === 'fragment') {
                $parentType = $spread['__parentScope']['name'];
                $fragmentType = $spread['typeCondition'] ?? $parentType;
            }

            $applicableTypes = array_intersect(
                static::getPossibleTypes($fragmentType),
                static::getPossibleTypes($parentType)
            );

            if (count($applicableTypes) === 0) {
                $identifier = isset($spread['name']) ?
                    "Fragment `{$spread['name']}`" :
                    "Inline fragment";

                Parser::throwInvalid(
                    "$identifier can never spread.",
                    $spread['__string']
                );
            }
        }
    }

    /**
     * Validates the implementers.
     *
     * @param  array $implementers The implementers to validate.
     * @return void
     */
    public static function validateImplementers($implementers)
    {
        foreach ($implementers as $implementer) {
            $identifier = $implementer['name'];

            // An object type must be a super-set of all interfaces it
            // implements
            foreach ($implementer['implements'] as $name => $value) {
                $interface = static::$document[$name] ?? null;
                if ($interface === null) {
                    Parser::throwInvalid(
                        "`$identifier` implements `$name` which does not exist."
                    );
                }

                if ($interface['type'] !== 'interface') {
                    Parser::throwInvalid(
                        "`$identifier` implements `$name` which is not an " .
                        "interface."
                    );
                }

                $interfaceFields = $interface['fields'];
                $implementerFields = $implementer['fields'];

                // The object type must include a field of the same name for
                // every field defined in an interface.
                $found = array_diff_key($interfaceFields, $implementerFields);
                if (count($found) >= 1) {
                    Parser::throwInvalid(
                        "`$identifier` implements `$name` but hasn't " .
                        "implemented the fields `" .
                        implode('`, `', array_keys($found)) . "`."
                    );
                }

                foreach ($interfaceFields as $fieldName => $interfaceField) {
                    $implementerField = $implementerFields[$fieldName];

                    // The object field must be of a type which is equal to or
                    // a sub-type of the interface field (covariant).
                    if (!static::isTypeCovariant(
                        $implementerField['type'],
                        $interfaceField['type']
                    )) {
                        Parser::throwInvalid(
                            "`$identifier` implements `$name` but the type " .
                            "for the field `$fieldName` is not covariant."
                        );
                    }

                    $interfaceArguments = $interfaceField['arguments'] ?? [];
                    $implementerArguments =
                        $implementerField['arguments'] ?? [];

                    // The object field must include an argument of the same
                    // name for every argument defined in the interface field.
                    $found = array_diff_key(
                        $interfaceArguments,
                        $implementerArguments
                    );
                    if (count($found) >= 1) {
                        Parser::throwInvalid(
                            "`$identifier` implements `$name` but hasn't " .
                            "implemented the arguments `" .
                            implode('`, `', array_keys($found)) . "`."
                        );
                    }

                    foreach ($interfaceArguments as $interfaceArgument) {
                        $argumentName = $interfaceArgument['name'];
                        $implementerArgument =
                            $implementerArguments[$argumentName] ?? null;

                        // The object field argument must accept the same type
                        // (invariant) as the interface field argument.
                        if (!static::isTypeInvariant(
                            $implementerArgument['type'],
                            $interfaceArgument['type']
                        )) {
                            Parser::throwInvalid(
                                "`$identifier` implements `$name` but the " .
                                "type for the argument `$argumentName` is " .
                                "not invariant."
                            );
                        }
                    }

                    // The object field may include additional arguments not
                    // defined in the interface field, but any additional
                    // argument must not be required, e.g. must not be of a
                    // non-nullable type.
                    $found = array_diff_key(
                        $implementerArguments,
                        $interfaceArguments
                    );
                    foreach ($found as $argumentName => $argument) {
                        if ($argument['type']['nullable'] === false) {
                            Parser::throwInvalid(
                                "`$identifier` implements `$name` but the " .
                                "type for the extra argument `$argumentName` " .
                                "cannot be required."
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Validates the input types.
     *
     * @param  array $inputTypes The input types to validate.
     * @return void
     */
    public static function validateInputTypes($inputTypes)
    {
        foreach ($inputTypes as $inputType) {
            if (!static::isInputType($inputType['type']['type'])) {
                Parser::throwInvalid(
                    "The type for `{$inputType['type']['name']}` needs to be " .
                    "an input type.",
                    $inputType['string']
                );
            }
        }
    }

    /**
     * Validates the input value.
     *
     * @param  array  $definition The definition to compare against.
     * @param  array  $argument The argument to validate.
     * @param  string $string The remaining string to be parsed.
     * @return void
     */
    public static function validateInputValue($definition, $argument, $string)
    {
        Parser::coerceInput(
            static::$schemaDocument,
            $definition,
            $argument,
            $string
        );
    }

    /**
     * Validates the object types.
     *
     * @param  array $objectTypes The object types to validate.
     * @return void
     */
    public static function validateObjectTypes($objectTypes)
    {
        foreach ($objectTypes as $objectType) {
            $type = $objectType['type']['name'];

            $definition = static::$document[$type] ?? null;
            if ($definition === null || $definition['type'] !== 'type') {
                Parser::throwInvalid(
                    "The member type `$type` must be an Object type.",
                    $objectType['string']
                );
            }
        }
    }

    /**
     * Validates the operations.
     *
     * @param  array $operations The operations to validate.
     * @return void
     */
    public static function validateOperations($operations)
    {
        foreach ($operations as $details) {
            $operation = $details['operation'];
            $identifier = isset($operation['name']) ?
                "`{$operation['name']}`" :
                "the operation";

            $variableDefinitions =& $operation['__variableDefinitions'] ?? [];
            foreach ($operation['__fragmentSpreads'] as $fragmentName) {
                $set = static::$document[$fragmentName]['selectionSet']['set'];
                while (count($set) >= 1) {
                    $selection = array_shift($set);
                    if (isset($selection['selectionSet'])) {
                        foreach ($selection['selectionSet']['set'] as $s) {
                            $set[] = $s;
                        }
                    } elseif ($selection['type'] === 'spread') {
                        $fragment = static::$document[$selection['name']];
                        foreach ($fragment['selectionSet']['set'] as $s) {
                            $set[] = $s;
                        }
                    }

                    foreach ($selection['arguments'] ?? [] as $arg) {
                        $value = $arg['value'];

                        $expectedType = $value['__type'] ?? null;
                        $type = $value['type'];
                        $value = $value['value'];

                        while ($type === 'List') {
                            $expectedType = $value[0]['__type'] ?? null;
                            $type = $value[0]['type'];
                            $value = $value[0]['value'];
                        }

                        if ($type !== 'Variable') {
                            continue;
                        }

                        $argName = $value;
                        if (!isset($variableDefinitions[$argName])) {
                            Parser::throwInvalid(
                                "Variable `$$argName` is not defined in " .
                                "$identifier."
                            );
                        }

                        if (!is_array(
                            $variableDefinitions[$argName]['__used']
                        )) {
                            $variableDefinitions[$argName]['__used'] = [];
                        }

                        $variableDefinitions[$argName]['__used'][] =
                            $expectedType;
                    }
                }
            }

            foreach ($variableDefinitions ?? [] as $name => $definition) {
                if ($definition['__used'] === false) {
                    Parser::throwInvalid(
                        "Variable `$$name` is not used in $identifier."
                    );
                }

                foreach ($definition['__used'] as $variableUsage) {
                    if (!static::isVariableUsageAllowed(
                        $definition,
                        $variableUsage
                    )) {
                        Parser::throwInvalid(
                            "Variable `$$name` cannot be used as an argument " .
                            "to `{$variableUsage['name']}`."
                        );
                    }
                }
            }
        }
    }

    /**
     * Validates the output types.
     *
     * @param  array $outputTypes The output types to validate.
     * @return void
     */
    public static function validateOutputTypes($outputTypes)
    {
        foreach ($outputTypes as $outputType) {
            if (!static::isOutputType($outputType['type']['type'])) {
                Parser::throwInvalid(
                    "The type for `{$outputType['type']['name']}` needs to " .
                    "be an output type.",
                    $outputType['string']
                );
            }
        }
    }

    /**
     * Validates the selection sets.
     *
     * @param  array $sets The selection sets to validate.
     * @return void
     */
    public static function validateSelectionSets($sets)
    {
        foreach ($sets as $set) {
            if (!static::fieldsInSetCanMerge($set)) {
                Parser::throwInvalid(
                    "Fields in a selection set need to be able to merge.",
                    $set['string']
                );
            }
        }
    }

    /**
     * Validates the variable usage.
     *
     * @param  array  $variable The variable usage to validate.
     * @param  array  $type The expected variable type.
     * @param  string $string The remaining string to be parsed.
     * @return void
     */
    public static function validateVariableUsage($variable, $type, $string)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $inFragmentDefinition = false;
        foreach ($backtrace as $trace) {
            if (strpos($trace['function'], 'FragmentDefinition') !== false) {
                $inFragmentDefinition = true;
                break;
            }
        }

        if (!$inFragmentDefinition) {
            if (!isset(Parser::$variableDefinitions[$variable])) {
                Parser::throwInvalid(
                    "Variable `$$variable` is not defined in the operation.",
                    $string
                );
            }

            if (!is_array(Parser::$variableDefinitions[$variable]['__used'])) {
                Parser::$variableDefinitions[$variable]['__used'] = [];
            }

            Parser::$variableDefinitions[$variable]['__used'][] = $type;
        }
    }
}
