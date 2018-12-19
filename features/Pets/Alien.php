<?php

namespace GraphQL\Features\Pets;

class Alien extends Sentient
{
    protected $homePlanet;

    public function __construct($args)
    {
        $this->homePlanet = $args['homePlanet'] ?? null;

        parent::__construct($args);
    }

    public function resolve($fieldName, $args)
    {
        if ($fieldName === 'homePlanet') {
            return $this->homePlanet;
        }

        return parent::resolve($fieldName, $args);
    }
}
