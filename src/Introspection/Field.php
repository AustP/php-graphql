<?php

namespace GraphQL\Introspection;

use GraphQL\Schema\ResolverTrait;

class Field
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
        } elseif ($fieldName === 'deprecationReason') {
            // deprecationReason: String
            $isDeprecated = isset($this->definition['directives']['deprecated']);
            if (!$isDeprecated) {
                return null;
            }

            $definition = $this->schema['deprecated'];
            $default = $definition['arguments']['reason']['default']['value'];

            $deprecated = $this->definition['directives']['deprecated'];
            $reason = $deprecated['arguments']['reason']['value']['value'] ??
                $default;

            return $reason;
        } elseif ($fieldName === 'description') {
            // description: String
            return $this->definition['description'] ?? null;
        } elseif ($fieldName === 'isDeprecated') {
            // isDeprecated: Boolean!
            return isset($this->definition['directives']['deprecated']);
        } elseif ($fieldName === 'name') {
            // name: String!
            return $this->definition['name'];
        } elseif ($fieldName === 'type') {
            // type: __Type!
            $type = $this->definition['type']['value'];
            while (is_array($type)) {
                $type = $type['value'];
            }

            return new Type($type, $this->schema);
        }
    }
}
