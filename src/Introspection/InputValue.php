<?php

namespace GraphQL\Introspection;

use GraphQL\Schema\ResolverTrait;

class InputValue
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
        } elseif ($fieldName === 'defaultValue') {
            // defaultValue: String
            if (!isset($this->definition['default'])) {
                return null;
            }

            $type = $this->definition['default']['type'];
            $value = $this->definition['default']['value'];

            if ($type === 'Boolean') {
                return $value ? 'true' : 'false';
            }

            return (string)$value;
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
