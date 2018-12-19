<?php

namespace GraphQL\Introspection;

class TypeKind
{
    public $isResolver = true;

    protected const MAPPING = [
        'enum' => 'ENUM',
        'input' => 'INPUT_OBJECT',
        'interface' => 'INTERFACE',
        'scalar' => 'SCALAR',
        'type' => 'OBJECT',
        'union' => 'UNION'
    ];

    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function resolve($typeKinds)
    {
        $type = self::MAPPING[$this->type];
        return isset($typeKinds[$type]) ? $type : null;
    }
}
