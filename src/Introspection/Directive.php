<?php

namespace GraphQL\Introspection;

use GraphQL\Schema\ResolverTrait;

class Directive
{
    use ResolverTrait;

    protected $definition;
    protected $schema;

    public function __construct($definition, $schema)
    {
        $this->definition = $definition;
        $this->schema = $schema;
    }

    protected function resolve($fieldName)
    {
        if ($fieldName === 'args') {
            // args: [__InputValue!]!
            $args = [];
            foreach ($this->definition['arguments'] ?? [] as $arg) {
                $args[$arg['name']] = new InputValue($arg, $this->schema);
            }

            // make args return in alphabetical order
            uksort($args, function ($a, $b) {
                return $a > $b ? 1 : -1;
            });

            return array_values($args);

            return $args;
        } elseif ($fieldName === 'description') {
            // description: String
            return $this->definition['description'] ?? null;
        } elseif ($fieldName === 'locations') {
            // locations: [__DirectiveLocation!]!
            $locations = [];
            foreach ($this->definition['locations'] ?? [] as $location) {
                $locations[] = new DirectiveLocation($location);
            }

            // make locations return in alphabetical order
            usort($locations, function ($a, $b) {
                return $a > $b ? 1 : -1;
            });

            return $locations;
        } elseif ($fieldName === 'name') {
            // name: String!
            return $this->definition['name'];
        }
    }
}
