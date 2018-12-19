<?php

namespace GraphQL\Introspection;

use GraphQL\Schema\ResolverTrait;

class Schema
{
    use ResolverTrait;

    protected $schema;

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

    protected function resolve($fieldName, $args)
    {
        if ($fieldName === 'directives') {
            // directives: [__Directive!]!
            $schema = $this->schema;

            $directives = [];
            foreach ($schema as $name => $definition) {
                if ($definition['type'] === 'directive') {
                    $directives[$name] = new Directive($definition, $schema);
                }
            }

            // make directives return in alphabetical order
            uksort($directives, function ($a, $b) {
                return $a > $b ? 1 : -1;
            });

            return array_values($directives);
        } elseif ($fieldName === 'mutationType') {
            // mutationType: __Type
            $operations = $this->schema['__schema']['operations'];
            $mutationName = $operations['mutation']['name'] ?? null;
            if ($mutationName === null) {
                return null;
            }

            return new Type($this->schema[$mutationName], $this->schema);
        } elseif ($fieldName === 'queryType') {
            // queryType: __Type!
            $operations = $this->schema['__schema']['operations'];
            $queryName = $operations['query']['name'];

            return new Type($this->schema[$queryName], $this->schema);
        } elseif ($fieldName === 'subscriptionType') {
            // subscriptionType: __Type
            $operations = $this->schema['__schema']['operations'];
            $subscriptionName = $operations['subscription']['name'] ?? null;
            if ($subscriptionName === null) {
                return null;
            }

            return new Type($this->schema[$subscriptionName], $this->schema);
        } elseif ($fieldName === 'types') {
            // types: [__Type!]!
            $types = [];
            foreach ($this->schema as $name => $definition) {
                if ((
                    $definition['type'] === 'enum' ||
                    $definition['type'] === 'input' ||
                    $definition['type'] === 'interface' ||
                    $definition['type'] === 'type' ||
                    $definition['type'] === 'union'
                )) {
                    $types[$name] = new Type($definition, $this->schema);
                }
            }

            // make types return in alphabetical order
            uksort($types, function ($a, $b) {
                return $a > $b ? 1 : -1;
            });

            return array_values($types);
        }
    }
}
