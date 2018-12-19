<?php

namespace GraphQL\Introspection;

use GraphQL\Schema\ResolverTrait;

class Type
{
    use ResolverTrait;

    protected $definition;
    protected $schema;

    public function __construct($definition, $schema)
    {
        if (is_string($definition)) {
            if ((
                $definition === 'Boolean' ||
                $definition === 'Float' ||
                $definition === 'ID' ||
                $definition === 'Int' ||
                $definition === 'String'
            )) {
                $this->definition = [
                    'description' => 'Built-in scalar type',
                    'name' => $definition,
                    'type' => 'scalar'
                ];
            } else {
                $this->definition = $schema[$definition];
            }
        } else {
            $this->definition = $definition;
        }

        $this->schema = $schema;
    }

    protected function resolve($fieldName, $args)
    {
        if ($fieldName === 'description') {
            // description: String
            return $this->definition['description'] ?? null;
        } elseif ($fieldName === 'enumValues') {
            // # ENUM only
            // enumValues(includeDeprecated: Boolean = false): [__EnumValue!]
            if ($this->definition['type'] !== 'enum') {
                return null;
            }

            $includeDeprecated = $args['includeDeprecated'] ?? false;

            $enumValues = [];
            foreach ($this->definition['values'] as $enum) {
                $deprecated = isset($enum['directives']['deprecated']);
                if ($deprecated && !$includeDeprecated) {
                    continue;
                }

                $schema = $this->schema;
                $enumValues[$enum['name']] = new EnumValue($enum, $schema);
            }

            // make enumValues return in alphabetical order
            uksort($enumValues, function ($a, $b) {
                return $a > $b ? 1 : -1;
            });

            return array_values($enumValues);
        } elseif ($fieldName === 'fields') {
            // # OBJECT and INTERFACE only
            // fields(includeDeprecated: Boolean = false): [__Field!]
            if ((
                $this->definition['type'] !== 'interface' &&
                $this->definition['type'] !== 'type'
            )) {
                return null;
            }

            $includeDeprecated = $args['includeDeprecated'] ?? false;

            $fields = [];
            foreach ($this->definition['fields'] as $field) {
                $deprecated = isset($field['directives']['deprecated']);
                if ($deprecated && !$includeDeprecated) {
                    continue;
                }

                if (strpos($field['name'], '__') === 0) {
                    continue;
                }

                $fields[$field['name']] = new Field($field, $this->schema);
            }

            // make fields return in alphabetical order
            uksort($fields, function ($a, $b) {
                return $a > $b ? 1 : -1;
            });

            return array_values($fields);
        } elseif ($fieldName === 'inputFields') {
            // # INPUT_OBJECT only
            // inputFields: [__InputValue!]
            if ($this->definition['type'] !== 'input') {
                return null;
            }

            $inputFields = [];
            foreach ($this->definition['fields'] as $name => $field) {
                $inputFields[$name] = new InputValue($field, $this->schema);
            }

            // make inputFields return in alphabetical order
            uksort($inputFields, function ($a, $b) {
                return $a > $b ? 1 : -1;
            });

            return array_values($inputFields);
        } elseif ($fieldName === 'interfaces') {
            // # OBJECT only
            // interfaces: [__Type!]
            if ($this->definition['type'] !== 'type') {
                return null;
            }

            $interfaces = [];
            foreach ($this->definition['implements'] ?? [] as $interface) {
                $name = $interface['name'];
                $definition = $this->schema[$name];

                $interfaces[$name] = new self($definition, $this->schema);
            }

            // make interfaces return in alphabetical order
            uksort($interfaces, function ($a, $b) {
                return $a > $b ? 1 : -1;
            });

            return array_values($interfaces);
        } elseif ($fieldName === 'kind') {
            // kind: __TypeKind!
            return new TypeKind($this->definition['type']);
        } elseif ($fieldName === 'name') {
            // name: String
            return $this->definition['name'];
        } elseif ($fieldName === 'ofType') {
            // # NON_NULL and LIST only
            // ofType: __Type
            return null;
        } elseif ($fieldName === 'possibleTypes') {
            // # INTERFACE and UNION only
            // possibleTypes: [__Type!]
            if ((
                $this->definition['type'] !== 'interface' &&
                $this->definition['type'] !== 'union'
            )) {
                return null;
            }

            $schema = $this->schema;
            $types = [];

            if ($this->definition['type'] === 'interface') {
                foreach ($schema as $definition) {
                    foreach ($definition['implements'] ?? [] as $interface) {
                        if ($interface['name'] === $this->definition['name']) {
                            $name = $definition['name'];
                            $types[$name] = new self($definition, $schema);
                        }
                    }
                }
            }

            if ($this->definition['type'] === 'union') {
                foreach ($this->definition['types'] as $typeName => $type) {
                    $types[$typeName] = new self($schema[$typeName], $schema);
                }
            }

            // make possible types return in alphabetical order
            uksort($types, function ($a, $b) {
                return $a > $b ? 1 : -1;
            });

            return array_values($types);
        }
    }
}
