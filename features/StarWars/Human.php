<?php

namespace GraphQL\Features\StarWars;

class Human extends Character
{
    protected $height;
    protected $mass;
    protected $starships;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->height = $args['height'] ?? null;
        $this->mass = $args['mass'] ?? null;
        $this->starships = $args['starships'] ?? null;
    }

    protected function getHeight($unit)
    {
        if ($unit === 'METER') {
            return $this->height;
        } elseif ($unit === 'FOOT') {
            return $this->height * 3.28084;
        }
    }

    public function resolve($fieldName, $args)
    {
        if ($fieldName === 'height') {
            return $this->getHeight($args['unit'] ?? 'METER');
        } elseif ($fieldName === 'mass') {
            return $this->mass;
        } elseif ($fieldName === 'starships') {
            return array_map(function ($starshipId) {
                return Database::getStarship($starshipId);
            }, $this->starships);
        }

        return parent::resolve($fieldName, $args);
    }
}
