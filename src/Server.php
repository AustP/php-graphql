<?php

namespace GraphQL;

use GraphQL\Document\Exceptions\QueryError;
use GraphQL\Document\Exceptions\ServerError;
use GraphQL\Document\Parser;
use GraphQL\Document\Parser\Exceptions\InvalidDocument;
use GraphQL\Document\Parser\Exceptions\SyntaxError;
use GraphQL\Introspection\Schema;
use GraphQL\Introspection\Type;
use GraphQL\Schema\ResolverTrait;

class Server
{
    use ResolverTrait;

    protected $document = [];
    protected $handlers = [];
    protected $schema = [];

    public static $cacheDir = '/tmp/';
    protected static $typenameResolver = null;
    public static $useCache = true;
    public static $useIntrospection = true;
    public static $useResultCoercion = false;

    /**
     * Instantiates the server.
     *
     * @param  string ...$items File paths or schema strings
     * @return void
     */
    public function __construct(...$items)
    {
        $dir = __DIR__ . '/schemas/';

        Parser::reset();

        if (static::$useIntrospection) {
            Parser::parseIntrospection(
                $dir . 'introspection.graphql'
            );
        }

        Parser::parseFile($dir . 'directives.graphql');
        $this->schema = Parser::parseSchema(...$items);

        if (static::$useIntrospection) {
            $this->setupIntrospection();
        }
    }

    /**
     * Executes the selected operation.
     *
     * @param  string $operation The operation to execute.
     * @param  array  $variables The variables of the operation.
     * @return array
     */
    protected function execute($operation, $variables)
    {
        if ($operation['type'] === 'subscription') {
            return ['data' => 'TODO: Implement Subscriptions'];
        }

        $selectionSet = $operation['selectionSet'];

        // $this->executeResolver comes from ResolverTrait
        $data = $this->executeResolver(
            $this->schema,
            $this->document,
            $variables,
            $selectionSet
        );

        $response = ['data' => $data];

        $errors = $this->resolverGetErrors();
        if ($errors !== null && count($errors) >= 1) {
            $response['errors'] = $errors;
        }

        return $response;
    }

    /**
     * Extracts data from the supplied exception to be returned.
     *
     * @param  \Exception $exception The exception to extract the data from
     * @return array
     */
    protected function getError($exception)
    {
        $error = [];
        if (isset($exception->column)) {
            $error['locations'] = [[
                'column' => $exception->column,
                'line' => $exception->documentLine
            ]];
        }

        $error['message'] = $exception->getMessage();

        if (isset($error['locations'])) {
            $error['path'] = $exception->path;
        }

        return $error;
    }

    /**
     * Returns the formatted response for the supplied query and variables.
     *
     * If more than one operation exists in the query, $operationName must be
     * supplied.
     *
     * @param  string $query The query to handle.
     * @param  array  $variables The variables to use in the query.
     * @param  string $operationName The operation in the query to use.
     * @return array
     */
    public function handle($query, $variables = [], $operationName = null)
    {
        $this->document = [];

        try {
            $this->document = Parser::parseQuery($query);

            if ((
                $operationName !== null &&
                !isset($this->document[$operationName])
            )) {
                Parser::throwInvalid(
                    "Operation `$operationName` is not defined in the document."
                );
            }

            if ($operationName === null) {
                foreach ($this->document as $name => $operation) {
                    if ((
                        $operation['type'] === 'query' ||
                        $operation['type'] === 'mutation' ||
                        $operation['type'] === 'subscription'
                    )) {
                        if ($operationName !== null) {
                            Parser::throwInvalid(
                                "When no operation is chosen, there needs to " .
                                "be only one operation defined in the document."
                            );
                        }

                        $operationName = $name;
                    }
                }
            }

            if ($operationName === null) {
                return ['data' => []];
            }

            $operation = $this->document[$operationName];

            // $this->resolverCoerceValues comes from ResolverTrait
            $coercedVariables = $this->resolverCoerceValues(
                $this->schema,
                $operation['__variableDefinitions'] ?? [],
                $variables
            );
        } catch (InvalidDocument | SyntaxError | QueryError $exception) {
            $error = $this->getError($exception);
            return [
                'errors' => [$error]
            ];
        }

        return $this->execute($operation, $coercedVariables);
    }

    /**
     * Registers a callback to be executed when a query asks for the
     * corresponding field name.
     *
     * @param  string   $fieldName The field name to respond to.
     * @param  callable $callback The callback to execute.
     * @return void
     */
    public function register($fieldName, $callback)
    {
        $this->handlers[$fieldName] = $callback;
    }

    /**
     * Resolves the field by passing the information along to the handlers.
     *
     * @param  string $fieldName The field being resolved.
     * @param  array  $args The arguments supplied to the field.
     * @return mixed
     */
    protected function resolve($fieldName, $args)
    {
        if (!isset($this->handlers[$fieldName])) {
            static::throwError(
                "There is no handler for the field `$fieldName`."
            );
        }

        return $this->handlers[$fieldName]($args);
    }

    /**
     * Registers the introspection handlers.
     *
     * @return void
     */
    protected function setupIntrospection()
    {
        $this->register('__schema', function () {
            return new Schema($this->schema);
        });

        $this->register('__type', function ($args) {
            $type = $this->schema[$args['name']] ?? null;
            if ($type === null) {
                return null;
            }

            return new Type($type, $this->schema);
        });
    }

    /**
     * Returns the __typename for the specified resolver.
     *
     * @param  object $resolver The current resolver.
     * @param  string $scopeName The name of the current scope.
     * @return string
     */
    public static function mapTypename($resolver, $scopeName)
    {
        if (static::$typenameResolver !== null) {
            return call_user_func_array(
                static::$typenameResolver,
                [$resolver, $scopeName]
            );
        } else {
            $class = explode('\\', get_class($resolver));
            $typeName = array_pop($class);
            if ($typeName === 'Resolver') {
                $typeName = $scopeName;
            }

            if (strpos($scopeName, '__') === 0) {
                $typeName = '__' . $typeName;
            }

            return $typeName;
        }
    }

    /**
     * Overrides the default __typename resolver.
     *
     * The resolver will be passed the current resolver and current scope name
     * as it's parameters.
     *
     * @param  callable $resolver The new __typename resolver.
     * @return void
     */
    public static function setTypenameResolver($resolver)
    {
        static::$typenameResolver = $resolver;
    }

    /**
     * Throws a server error.
     *
     * @param  string $message The message to throw.
     * @throws ServerError
     */
    public static function throwError($message)
    {
        throw new ServerError($message);
    }

    /**
     * Throws a query error.
     *
     * @param  string $message The message to throw.
     * @throws QueryError
     */
    public static function throwQuery($message)
    {
        throw new QueryError($message);
    }
}
