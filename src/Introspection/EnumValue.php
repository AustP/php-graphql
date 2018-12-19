<?php

namespace GraphQL\Introspection;

use GraphQL\Schema\ResolverTrait;

class EnumValue
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
        if ($fieldName === 'description') {
            // description: String
            return $this->definition['description'] ?? null;
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
        } elseif ($fieldName === 'isDeprecated') {
            // isDeprecated: Boolean!
            return isset($this->definition['directives']['deprecated']);
        } elseif ($fieldName === 'name') {
            // name: String!
            return $this->definition['name'];
        }
    }
}
