<?php

namespace GraphQL\Document;

use GraphQL\Document\Parser\Validator;
use GraphQL\Document\Parser\Language\SourceText;
use GraphQL\Server;
use GraphQL\Schema\Resolver;

class Parser
{
    public static $path = [];
    public static $schemaDocument = [];
    public static $scope = [];
    public static $skipValidation = false;
    public static $variableDefinitions = [];

    protected static $directives = [];
    protected static $document;
    protected static $documents = [];
    protected static $filesIncluded = false;
    protected static $fragments = ['definition' => [], 'spread' => []];
    protected static $fragmentSpreads = [];
    protected static $implementers = [];
    protected static $inputTypes = [];
    protected static $loadedMd5s = [];
    protected static $objectTypes = [];
    protected static $operations = [];
    protected static $outputTypes = [];
    protected static $selectionSets = [];
    protected static $string;

    private static $version = '0.1.0';

    /**
     * Adds a directive for later validation.
     *
     * @param  array  $directive The directive.
     * @param  string $string The remaining string to be parsed.
     * @return void
     */
    public static function addDirective($directive, $string)
    {
        static::$directives[] = [
            'directive' => $directive,
            'string' => $string
        ];
    }

    /**
     * Adds a fragment for later validation.
     *
     * @param  array  $fragment The fragment.
     * @param  string $string The remaining string to be parsed.
     * @return void
     */
    public static function addFragment($fragment, $type, $string)
    {
        if ($fragment['type'] === 'spread') {
            static::$fragmentSpreads[] = $fragment['name'];
        }

        $fragment['__string'] = $string;
        $name = $fragment['name'] ?? '__' . count(static::$fragments[$type]);
        static::$fragments[$type][$name] = $fragment;
    }

    /**
     * Adds an implementer for later validation.
     *
     * @param  array  $implementer The implementer.
     * @return void
     */
    public static function addImplementer($implementer)
    {
        static::$implementers[] = $implementer;
    }

    /**
     * Adds an input type for later validation.
     *
     * @param  array  $type The input type.
     * @param  string $string The remaining string to be parsed.
     * @return void
     */
    public static function addInputType($type, $string)
    {
        static::$inputTypes[] = [
            'string' => $string,
            'type' => $type
        ];
    }

    /**
     * Adds an object type for later validation.
     *
     * @param  array  $type The object type.
     * @param  string $string The remaining string to be parsed.
     * @return void
     */
    public static function addObjectType($type, $string)
    {
        static::$objectTypes[] = [
            'string' => $string,
            'type' => $type
        ];
    }

    /**
     * Adds an operation for later validation.
     *
     * @param  array  $operation The operation.
     * @param  string $string The remaining string to be parsed.
     * @return void
     */
    public static function addOperation($operation, $string)
    {
        static::$operations[] = [
            'operation' => $operation,
            'string' => $string
        ];
    }

    /**
     * Adds an output type for later validation.
     *
     * @param  array  $type The output type.
     * @param  string $string The remaining string to be parsed.
     * @return void
     */
    public static function addOutputType($type, $string)
    {
        static::$outputTypes[] = [
            'string' => $string,
            'type' => $type
        ];
    }

    /**
     * Adds an entry to the current path.
     *
     * @param  string $path The entry to add.
     * @return void
     */
    public static function addPath($path)
    {
        static::$path[] = $path;
    }

    /**
     * Adds a selection set for later validation.
     *
     * @param  array  $selectionSet The selection set.
     * @param  string $string The remaining string to be parsed.
     * @return void
     */
    public static function addSelectionSet($selectionSet, $string)
    {
        static::$selectionSets[] = [
            'selectionSet' => $selectionSet,
            'string' => $string
        ];
    }

    /**
     * Attempts to coerce the input to a valid type.
     *
     * @param  array  $schemaDocument The schema document.
     * @param  array  $definition The definition to check against.
     * @param  array  $input The input to coerce.
     * @param  string $string The remaining string to be parsed.
     * @param  bool   $singleItem Used when coercing single items as lists.
     * @return mixed
     */
    public static function coerceInput(
        $schemaDocument,
        $definition,
        $input,
        $string = null,
        $singleItem = false
    ) {
        $nullable = $definition['nullable'];
        $definitionType = $definition['value'];
        $object = is_string($definitionType) ?
            ($schemaDocument[$definitionType] ?? null) :
            null ;

        $name = $input['name'];
        $value = $input['value']['value'];
        $valueType = $input['value']['type'] ?? static::getInputType(
            $value,
            $object
        );

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $recursive = strpos(
            $backtrace[1]['function'],
            'coerceInput'
        ) !== false;

        $be = $recursive ? 'contain' : 'be';

        if ($valueType === 'Variable') {
            return null;
        }

        if ($value === null) {
            if ($nullable === false) {
                Parser::throwInvalid(
                    "Value for `$name` cannot be null.",
                    $string
                );
            } else {
                return ['type' => 'Null', 'value' => null];
            }
        }

        if (is_array($definitionType)) {
            if ($valueType !== 'List') {
                if ($recursive === false || $singleItem === true) {
                    $singleItem = true;
                    $value = [['type' => $valueType, 'value' => $value]];
                } else {
                    Parser::throwInvalid(
                        "Expected `$name` to $be a List, found `$valueType`.",
                        $string
                    );
                }
            }

            $items = [];
            foreach ($value as $item) {
                $coerced = static::coerceInput(
                    $schemaDocument,
                    $definitionType,
                    ['name' => $name, 'value' => ['value' => $item]],
                    $string,
                    $singleItem
                );

                if ($coerced !== null) {
                    $items[] = $coerced['value'];
                }
            }

            return ['type' => 'List', 'value' => $items];
        }

        if ($definitionType === 'Int') {
            if ($valueType !== 'Int') {
                Parser::throwInvalid(
                    "Expected `$name` to $be an Int, found `$valueType`.",
                    $string
                );
            }

            return ['type' => 'Int', 'value' => (int)$value];
        }

        if ($definitionType === 'Float') {
            if ($valueType !== 'Float' && $valueType !== 'Int') {
                Parser::throwInvalid(
                    "Expected `$name` to $be an Int or Float, found " .
                    "`$valueType`.",
                    $string
                );
            }

            return ['type' => 'Float', 'value' => (float)$value];
        }

        if ($definitionType === 'String') {
            if ($valueType !== 'String') {
                Parser::throwInvalid(
                    "Expected `$name` to $be a String, found `$valueType`.",
                    $string
                );
            }

            return ['type' => 'String', 'value' => (string)$value];
        }

        if ($definitionType === 'Boolean') {
            if ($valueType !== 'Boolean') {
                Parser::throwInvalid(
                    "Expected `$name` to $be a Boolean, found `$valueType`.",
                    $string
                );
            }

            return ['type' => 'Boolean', 'value' => (bool)$value];
        }

        if ($definitionType === 'ID') {
            if ($valueType !== 'Int' && $valueType !== 'String') {
                Parser::throwInvalid(
                    "Expected `$name` to $be an Int or String, found `$valueType`.",
                    $string
                );
            }

            return ['type' => 'ID', 'value' => (string)$value];
        }

        if ($object === null) {
            return null;
        }

        if ($object['type'] === 'enum') {
            if ($valueType !== 'Enum') {
                Parser::throwInvalid(
                    "Expected `$name` to $be an Enum, found `$valueType`.",
                    $string
                );
            }

            return ['type' => 'Enum', 'value' => (string)$value];
        }

        if ($object['type'] === 'input') {
            if ($valueType !== 'Object') {
                Parser::throwInvalid(
                    "Expected `$name` to $be an Object, found `$valueType`.",
                    $string
                );
            }

            $return = [];
            foreach ($value as $subName => $subValue) {
                if (!isset($object['fields'][$subName])) {
                    Parser::throwInvalid(
                        "`$subName` is not defined in `$definitionType`.",
                        $string
                    );
                }

                if (!isset($subValue['value'])) {
                    $subValue = [
                        'type' => static::getInputType($subValue, $object),
                        'value' => $subValue
                    ];
                }

                $item = static::coerceInput(
                    $schemaDocument,
                    $object['fields'][$subName]['type'],
                    [
                        'name' => $subName,
                        'value' => $subValue
                    ],
                    $string
                );

                if ($item !== null) {
                    $return[$subName] = $item['value'];
                }
            }

            return ['type' => 'Object', 'value' => $return];
        }
    }

    /**
     * Gets the current fragment spreads.
     *
     * This is used by operations so they know which fragment spreads are used
     * within their bodies.
     *
     * @return array
     */
    public static function getFragmentSpreads()
    {
        $spreads = static::$fragmentSpreads;
        static::$fragmentSpreads = [];

        return $spreads;
    }

    /**
     * Gets the input type for the supplied value.
     *
     * @param  mixed $value The value to get the type for.
     * @param  array $object The object associated with the value.
     * @return mixed
     */
    public static function getInputType($value, $object)
    {
        if (is_string($value)) {
            if ((
                $object !== null &&
                $object['type'] === 'enum' &&
                isset($object['values'][$value])
            )) {
                return 'Enum';
            }

            return 'String';
        }

        if (is_float($value)) {
            return 'Float';
        }

        if (is_int($value)) {
            return 'Int';
        }

        if (is_bool($value)) {
            return 'Boolean';
        }

        if (is_array($value)) {
            $list = array_reduce(array_keys($value), function ($isList, $key) {
                if (!$isList) {
                    return false;
                }

                return is_int($key);
            }, true);

            return $list ? 'List' : 'Object';
        }
    }

    /**
     * Calculates the current line and column based on how far the parser got.
     *
     * @param  string $string The remaining string to be parsed.
     * @return array
     */
    protected static function getLineAndColumn($string)
    {
        $lines = [];

        $line = '';
        $substr = substr(static::$string, 0, -strlen($string));
        while (true) {
            if (strlen($substr) === 0) {
                break;
            }

            [$char, $charSubstr] = SourceText\SourceCharacter($substr);
            [$terminator, $terminatorSubstr] =
                SourceText\LineTerminator($substr);
            if ($terminator !== null) {
                $lines[] = $line;
                $line = '';

                $substr = $terminatorSubstr;
            } else {
                $line .= $char;
                $substr = $charSubstr;
            }
        }

        $lines[] = $line;

        $line = count($lines) ?? 0;
        $column = $line ? strlen($lines[$line - 1]) + 1 : 0;

        return [$line, $column];
    }

    /**
     * Figures out what was found instead of what was expected.
     *
     * @param  string $string The remaining string to be parsed.
     * @return string
     */
    protected static function getFound($string)
    {
        [$found] = Parser\util\LexicalToken($string, function ($string) {
            return SourceText\Token($string);
        });

        return $found ?? $string[0] ?? '';
    }

    /**
     * Gets the current document.
     *
     * It is possible to parse a schema document as many files. This method
     * combines all previously parsed documents into one document.
     *
     * @return array
     */
    protected static function getCurrentDocument()
    {
        $documents = static::$documents;
        $documents[] = static::$document;

        $currentDocument = [];
        foreach ($documents as $document) {
            foreach ($document as $name => $definition) {
                if (isset($currentDocument[$name])) {
                    static::throwInvalid(
                        "Definition `{$name}` cannot be overwritten."
                    );
                }

                $currentDocument[$name] = $definition;
            }
        }

        return $currentDocument;
    }

    /**
     * Parses and validates the document string.
     *
     * By default, parsed and validated documents will be cached to save time
     * upon future requests. This can be disabled by setting Server::$useCache
     * to false before attempting to parse document.
     *
     * @param  string $string The document string to be parsed.
     * @return array
     */
    public static function parse($string)
    {
        static::reset(false);

        static::$string = $string;
        $md5 = md5($string);

        $usingCache = false;
        if (Server::$useCache) {
            $cacheName = Server::$cacheDir . $md5 . '.cache.json';

            if (file_exists($cacheName)) {
                $cache = json_decode(
                    file_get_contents($cacheName),
                    true
                );

                if ($cache['version'] === static::$version) {
                    $dependentsLoaded = true;
                    foreach ($cache['md5s'] as $dependent) {
                        if (!in_array($dependent, static::$loadedMd5s)) {
                            $dependentsLoaded = false;
                            break;
                        }
                    }

                    if ($dependentsLoaded) {
                        static::$document = $cache['document'];
                        $usingCache = true;
                    }
                }
            }
        }

        if (!$usingCache) {
            if (!static::$filesIncluded) {
                static::$filesIncluded = true;

                $dir = new \RecursiveDirectoryIterator(__DIR__ . '/Parser');
                $iterator = new \RecursiveIteratorIterator($dir);
                foreach ($iterator as $file) {
                    if (strpos($file->getFilename(), '.php') !== false) {
                        require_once($file->getPathname());
                    }
                }
            }

            static::$document = Parser\Language\Document\Document($string);
            static::validateDocument();

            if (Server::$useCache) {
                file_put_contents($cacheName, json_encode([
                    'document' => static::$document,
                    'md5s' => static::$loadedMd5s,
                    'version' => static::$version
                ]));
            }
        }

        $document = static::getCurrentDocument();
        static::$documents[] = static::$document;
        static::$loadedMd5s[] = $md5;

        return $document;
    }

    /**
     * Parses and validates the file.
     *
     * @param  string $path The path to the document.
     * @return array
     */
    public static function parseFile($path)
    {
        return static::parse(file_get_contents($path));
    }

    /**
     * Parses the introspection schema.
     *
     * Validation is skipped while parsing this file so the underscore names
     * will be allowed.
     *
     * @param  string $path The path to the introspection schema document.
     * @return array
     */
    public static function parseIntrospection($path)
    {
        static::$skipValidation = true;
        $document = static::parseFile($path);
        static::$skipValidation = false;

        return $document;
    }

    /**
     * Parses and validates the query document.
     *
     * @param  string $query The query document.
     * @return array
     */
    public static function parseQuery($query)
    {
        static::resetQuery();

        $queryDocument = static::parse($query);

        static::$documents = [];

        foreach ($queryDocument as $definition) {
            if ($definition['__Definition'] !== 'ExecutableDefinition') {
                static::throwInvalid(
                    "Only ExecutableDefinitions are allowed in the query " .
                    "document."
                );
            }

            if ($definition['type'] === 'subscription') {
                // let's use a resolver to access collectFields
                $resolver = new Resolver(null);
                $groupedFieldSet = $resolver->resolverCollectFields(
                    $queryDocument,
                    $definition['selectionSet']['set'],
                    $definition['selectionSet']['scope'],
                    []
                );
                if (count($groupedFieldSet) !== 1) {
                    static::throwInvalid(
                        "Subscription operations must have exactly one root " .
                        "field."
                    );
                }
            }
        }

        return $queryDocument;
    }

    /**
     * Parses and validates the schema document.
     *
     * @param  string ...$items File paths or schema strings
     * @return array
     */
    public static function parseSchema(...$items)
    {
        if (count($items) === 0) {
            throw new \GraphQL\Document\Exceptions\ServerError(
                "At least one file path or schema string must be included."
            );
        }

        $document = '';
        foreach ($items as $item) {
            if (file_exists($item)) {
                $document .= "\n\n" . file_get_contents($item);
            } else {
                $document .= "\n\n" . $item;
            }
        }

        $schema = static::parse($document);

        static::$documents = [];

        foreach ($schema as $definition) {
            if ($definition['__Definition'] === 'ExecutableDefinition') {
                static::throwInvalid(
                    "ExecutableDefinitions are not allowed in the schema " .
                    "document."
                );
            }
        }

        if (!isset($schema['__schema'])) {
            if (!isset($schema['Query'])) {
                static::throwInvalid(
                    "`schema` is not defined and `Query` does not exist."
                );
            }

            $schema['__schema'] = [
                '__Definition' => 'TypeSystemDefinition',
                'operations' => [
                    'query' => ['name' => 'Query']
                ],
                'type' => 'schema'
            ];

            if (isset($schema['Mutation'])) {
                $schema['__schema']['operations']['mutation'] = [
                    'name' => 'Mutation'
                ];
            }

            if (isset($schema['Subscription'])) {
                $schema['__schema']['operations']['subscription'] = [
                    'name' => 'Subscription'
                ];
            }
        }

        if (Server::$useIntrospection) {
            // inject __schema and __type for introspection
            $queryName = $schema['__schema']['operations']['query']['name'];

            $schema[$queryName]['fields']['__schema'] = [
                'name' => '__schema',
                'type' => [
                    'nullable' => false,
                    'value' => '__Schema'
                ]
            ];

            $schema[$queryName]['fields']['__type'] = [
                'arguments' => [
                    'name' => [
                        'name' => 'name',
                        'type' => [
                            'nullable' => false,
                            'value' => 'String'
                        ]
                    ]
                ],
                'name' => '__type',
                'type' => [
                    'nullable' => true,
                    'value' => '__Type'
                ]
            ];
        }

        static::$schemaDocument = $schema;
        Validator::setSchemaDocument($schema);

        return $schema;
    }

    /**
     * Removes the last entry into the path.
     *
     * @return mixed
     */
    public static function removePath()
    {
        if (count(static::$path)) {
            return array_pop(static::$path);
        }

        return null;
    }

    /**
     * Resets the parser
     *
     * @param  bool $resetDocuments Whether to reset the current documents
     * @return void
     */
    public static function reset($resetDocuments = true)
    {
        static::$directives = [];
        static::$document = null;
        static::$fragments = ['definition' => [], 'spread' => []];
        static::$implementers = [];
        static::$inputTypes = [];
        static::$objectTypes = [];
        static::$operations = [];
        static::$outputTypes = [];
        static::$selectionSets = [];
        static::$string = '';

        if ($resetDocuments) {
            static::$documents = [];
        }
    }

    /**
     * Resets the parser for query documents
     *
     * @return void
     */
    protected static function resetQuery()
    {
        static::$fragments = ['definition' => [], 'spread' => []];
        static::$fragmentSpreads = [];
        static::$operations = [];
        static::$path = [];
        static::$selectionSets = [];
    }

    /**
     * Sets the current scope.
     *
     * @param  mixed  $scope The next scope or the name of the next scope.
     * @param  string $string The remaining string to be parsed.
     * @param  bool   $forField Indicates if the next scope is for a field.
     * @return array
     */
    public static function setScope($scope, $string = null, $forField = false)
    {
        if (!static::$schemaDocument) {
            return;
        }

        $previousScope = static::$scope;

        if (!is_string($scope) && isset($scope['name'])) {
            static::$scope = $scope;
            return $previousScope;
        }

        if (is_array($scope)) {
            $scope = $scope['value'];
        }

        if ($scope === '__query' ||
            $scope === '__mutation' ||
            $scope === '__subscription'
        ) {
            $name = substr($scope, 2);

            $operations = static::$schemaDocument['__schema']['operations'];
            $operation = $operations[$name] ?? null;
            if ($operation === null) {
                static::throwInvalid(
                    "The operation `$name` is not defined in the schema.",
                    $string
                );
            }

            static::$scope = static::$schemaDocument[$operation['name']];
            return $previousScope;
        }

        if ((
            $scope === 'Boolean' ||
            $scope === 'Float' ||
            $scope === 'ID' ||
            $scope === 'Int' ||
            $scope === 'String'
        )) {
            if ($forField) {
                static::$scope = [];
                return $previousScope;
            }

            static::throwInvalid(
                "Fragment cannot be defined for `$scope`.",
                $string
            );
        }

        $next = static::$schemaDocument[$scope] ?? null;
        if ($next === null) {
            static::throwInvalid(
                "The definition `$scope` is not defined in the schema.",
                $string
            );
        }

        if ((
            $next['type'] !== 'interface' &&
            $next['type'] !== 'type' &&
            $next['type'] !== 'union'
        ) && (
            $forField === false
        )) {
            static::throwInvalid(
                "Fragment cannot be defined for `$scope`.",
                $string
            );
        }

        static::$scope = $next;
        return $previousScope;
    }

    /**
     * Sets the current scope for a field.
     *
     * @param  string $fieldName The field to set the scope for.
     * @param  string $string The remaining string to be parsed.
     * @return array
     */
    public static function setScopeForField($fieldName, $string)
    {
        if ($fieldName === '__typename') {
            if (Server::$useIntrospection === false) {
                static::throwInvalid(
                    "Introspection is turned off, cannot select `__typename`.",
                    $string
                );
            }

            return static::$scope;
        }

        $type = static::$scope['fields'][$fieldName]['type']['value'] ?? null;
        if ($type === null) {
            $currentScope = static::$scope['name'] ?? null;
            if ($currentScope === null) {
                static::throwInvalid(
                    "Selections are not allowed on scalars or enums.",
                    $string
                );
            }

            $type = static::$scope['type'];
            if ($type === 'enum' || $type === 'scalar') {
                static::throwInvalid(
                    "Selections are not allowed on `$currentScope`.",
                    $string
                );
            }
        }

        return static::setScope($type, $string, true);
    }

    /**
     * Sets the current variable definitions.
     *
     * This is used by operations so as their bodies get parsed, they can have
     * access to the variable definitions.
     *
     * @param  array $definitions The current definitions.
     * @return void
     */
    public static function setVariableDefinitions($definitions)
    {
        static::$variableDefinitions = $definitions;
    }

    /**
     * Throws an invalid document error.
     *
     * @param  string $message The message to throw.
     * @param  string $string The remaining string to be parsed.
     * @throws Parser\Exceptions\InvalidDocument
     */
    public static function throwInvalid($message, $string = null)
    {
        if (static::$skipValidation) {
            return;
        }

        if ($string === null) {
            throw new Parser\Exceptions\InvalidDocument($message);
        }

        [$line, $column] = static::getLineAndColumn($string);
        throw new Parser\Exceptions\InvalidDocument(
            $message,
            $line,
            $column
        );
    }

    /**
     * Throws a syntax error.
     *
     * @param  string $message The message to throw.
     * @param  string $string The remaining string to be parsed.
     * @throws Parser\Exceptions\SyntaxError
     */
    public static function throwSyntax($expected, $string)
    {
        $found = static::getFound($string);
        [$line, $column] = static::getLineAndColumn($string);
        throw new Parser\Exceptions\SyntaxError(
            $expected,
            $found,
            $line,
            $column
        );
    }

    /**
     * Validates the document.
     *
     * @return void
     */
    protected function validateDocument()
    {
        if (static::$skipValidation) {
            return;
        }

        Validator::setDocument(static::getCurrentDocument());

        // Schema document validations
        Validator::validateDirectives(static::$directives);
        Validator::validateImplementers(static::$implementers);
        Validator::validateInputTypes(static::$inputTypes);
        Validator::validateObjectTypes(static::$objectTypes);
        Validator::validateOutputTypes(static::$outputTypes);

        // Query document validations
        Validator::validateFragments(static::$fragments);
        Validator::validateOperations(static::$operations);
        Validator::validateSelectionSets(static::$selectionSets);
    }
}
