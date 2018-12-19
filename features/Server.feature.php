<?php

namespace GraphQL\Features;

use GraphQL\Document\Parser\Validator;
use GraphQL\Server;
use Mockery;
use Webmozart\Assert\Assert;

feature(
    'GraphQL Server',
    '',
    'As a developer',
    'I want to run a schema-first GraphQL server',
    'So that I can handle GraphQL DSL requests',
    isolatedStories(
        background(
            function () {
                $this->parser = Mockery::spy('alias:GraphQL\Document\Parser');
            },
            function () {
                Mockery::close();
            }
        ),
        'I want to be able to start a server with a path to a document',
        function () {
            $path = __DIR__ . '/schemas/starwars.graphql';
            new Server($path);

            $this->parser->shouldHaveReceived()->parseSchema($path);
        },
        'I want to be able to start a server with multiple document paths',
        function () {
            $path1 = __DIR__ . '/schemas/starwars.1.graphql';
            $path2 = __DIR__ . '/schemas/starwars.2.graphql';
            $path3 = __DIR__ . '/schemas/starwars.3.graphql';
            new Server($path1, $path2, $path3);

            $this->parser->shouldHaveReceived()->parseSchema($path1, $path2, $path3);
        },
        'I want to be able to start a server with a document string',
        function () {
            $document = "type Query {echo: String}";
            new Server($document);

            $this->parser->shouldHaveReceived()->parseSchema($document);
        }
    ),
    stories(
        'Starting the Star Wars server...',
        function () {
            $this->server = new StarWars\Server();
        },
        'I want to be able to handle query requests',
        function () {
            $queries = StarWars\Queries::get('fields');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to make sure query requests run concurrently',
        'I want to be able to handle query requests with arguments',
        function () {
            $queries = StarWars\Queries::get('arguments');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to be able to handle query requests with aliases',
        function () {
            $queries = StarWars\Queries::get('aliases');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to be able to handle query requests with fragments',
        function () {
            $queries = StarWars\Queries::get('fragments');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to be able to handle query requests with operation names',
        function () {
            $queries = StarWars\Queries::get('operationNames');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to be able to handle query requests with variables',
        function () {
            $queries = StarWars\Queries::get('variables');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to be able to handle query requests with directives',
        function () {
            $queries = StarWars\Queries::get('directives');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to be able to handle query requests with inline fragments',
        function () {
            $queries = StarWars\Queries::get('inlineFragments');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to be able to handle query requests with meta fields',
        function () {
            $queries = StarWars\Queries::get('metaFields');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to make sure invalid queries from the documentation error out correctly',
        function () {
            $invalids = StarWars\Queries::getInvalids();
            foreach ($invalids as $invalid) {
                $document = $invalid['document'];
                $response = $invalid['response'];
                $variables = $invalid['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For invalid document: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to be able to handle mutation requests',
        function () {
            $mutations = StarWars\Queries::getMutation('initial');
            foreach ($mutations as $mutation) {
                $document = $mutation['document'];
                $response = $mutation['response'];
                $variables = $mutation['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For mutation: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to make sure mutation requests run sequentially',
        function () {
            $document = 'mutation {
                one: incrementCredits(amount: 1000)
                two: incrementCredits(amount: 2000)
                three: incrementCredits(amount: 4000)
                four: incrementCredits(amount: 8000)
                five: incrementCredits(amount: 16000)
            }';

            $result = $this->server->handle($document);

            $response = [
                'data' => [
                    'one' => 2000,
                    'two' => 4000,
                    'three' => 8000,
                    'four' => 16000,
                    'five' => 32000
                ]
            ];

            Assert::eq(
                $result,
                $response,
                "For mutation: $document, expected\n `" .
                    json_encode($response) . "` but got\n `" .
                    json_encode($result) . "`"
            );
        },
        'I want to be able to handle subscription requests',
        'I want the server to support introspection',
        function () {
            $queries = StarWars\Queries::get('introspections');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to be able to run a server without introspection',
        function () {
            Server::$useIntrospection = false;
            $server = new Server('type Query {hero: Hero} type Hero {name: String}');

            $queries = StarWars\Queries::get('noIntrospections');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                Server::$useIntrospection = false;
                $result = $server->handle($document, $variables);
                Server::$useIntrospection = true;

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want the server to be able to coerce results',
        function () {
            Server::$useResultCoercion = true;

            $document = 'type Query{int:Int}';
            $server = new Server($document);

            $server->register('int', function () {
                return "3";
            });

            $result = $server->handle('{int}');
            Server::$useResultCoercion = false;

            $response = ['data' => ['int' => 3]];

            Assert::eq(
                $result,
                $response,
                "For query: $document, expected\n `" .
                    json_encode($response) . "` but got\n `" .
                    json_encode($result) . "`"
            );
        }
    ),
    stories(
        'Starting the Pets server...',
        function () {
            Validator::$mustUseFragment = false;
            $this->server = new Pets\Server();
        },
        'I want to make sure documents are valid',
        function () {
            $queries = Pets\Queries::get('documents');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to make sure operations are valid',
        function () {
            $queries = Pets\Queries::get('operations');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];
                $operationName = $query['operationName'] ?? null;

                $result = $this->server->handle(
                    $document,
                    $variables,
                    $operationName
                );

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to make sure fields are valid',
        function () {
            $queries = Pets\Queries::get('fields');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to make sure arguments are valid',
        function () {
            $queries = Pets\Queries::get('arguments');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to make sure fragments are valid',
        function () {
            $queries = Pets\Queries::get('fragments');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $unused = $query['unused'] ?? false;
                if ($unused) {
                    Validator::$mustUseFragment = true;
                }

                $result = $this->server->handle($document, $variables);

                if ($unused) {
                    Validator::$mustUseFragment = false;
                }

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to make sure values are valid',
        function () {
            $queries = Pets\Queries::get('values');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to make sure directives are valid',
        function () {
            $queries = Pets\Queries::get('directives');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];

                $result = $this->server->handle($document, $variables);

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        },
        'I want to make sure variables are valid',
        function () {
            $queries = Pets\Queries::get('variables');
            foreach ($queries as $query) {
                $document = $query['document'];
                $response = $query['response'];
                $variables = $query['variables'] ?? [];
                $operationName = $query['operationName'] ?? null;

                $result = $this->server->handle(
                    $document,
                    $variables,
                    $operationName
                );

                Assert::eq(
                    $result,
                    $response,
                    "For query: $document, expected\n `" .
                        json_encode($response) . "` but got\n `" .
                        json_encode($result) . "`"
                );
            }
        }
    )
);
