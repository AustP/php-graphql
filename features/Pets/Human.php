<?php

namespace GraphQL\Features\Pets;

class Human extends Sentient
{
    protected $pets;

    public function resolve($fieldName, $args)
    {
        if ($fieldName === 'pets') {
            return [Database::getCat(), Database::getDog()];
        }

        return parent::resolve($fieldName, $args);
    }
}
