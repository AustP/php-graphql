<?php

namespace GraphQL\Features\StarWars;

class Starship
{
    use \GraphQL\Schema\ResolverTrait;

    protected $id;
    protected $length;
    protected $name;

    public function __construct($args)
    {
        $this->id = $args['id'];
        $this->length = $args['length'] ?? null;
        $this->name = $args['name'];
    }

    protected function getLength($unit)
    {
        if ($unit === 'METER') {
            return $this->length;
        } elseif ($unit === 'FOOT') {
            return $this->length * 3.28084;
        }
    }

    public function matches($text)
    {
        return stripos($this->name, $text) !== false;
    }

    public function resolve($fieldName, $args)
    {
        if ($fieldName === 'id') {
            return $this->id;
        } elseif ($fieldName === 'length') {
            return $this->getLength($args['unit'] ?? 'METER');
        } elseif ($fieldName === 'name') {
            return $this->name;
        }
    }
}
