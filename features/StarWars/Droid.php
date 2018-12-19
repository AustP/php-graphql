<?php

namespace GraphQL\Features\StarWars;

class Droid extends Character
{
    protected $primaryFunction;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->primaryFunction = $args['primaryFunction'] ?? null;
    }

    public function resolve($fieldName, $args)
    {
        if ($fieldName === 'primaryFunction') {
            return $this->primaryFunction;
        }

        return parent::resolve($fieldName, $args);
    }
}
