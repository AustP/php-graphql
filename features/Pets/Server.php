<?php

namespace GraphQL\Features\Pets;

use GraphQL\Server as GraphQLServer;

class Server extends GraphQLServer
{
    public function __construct()
    {
        $path = dirname(__DIR__) . '/schemas/pets.graphql';
        parent::__construct($path);

        Database::create();

        $this->register('dog', [$this, 'dog']);
        $this->register('human', [$this, 'human']);
        $this->register('pet', [$this, 'pet']);
        $this->register('catOrDog', [$this, 'catOrDog']);
        $this->register('arguments', [$this, 'arguments']);
        $this->register('findDog', [$this, 'findDog']);
        $this->register('booleanList', [$this, 'booleanList']);
    }

    protected function arguments()
    {
        return Database::getArguments();
    }

    protected function booleanList()
    {
        true;
    }

    protected function catOrDog()
    {
        $rand = mt_rand(1, 2);
        if ($rand === 1) {
            return Database::getCat();
        }

        return Database::getDog();
    }

    protected function dog()
    {
        return Database::getDog();
    }

    protected function findDog()
    {
        return Database::getDog();
    }

    protected function human()
    {
        return Database::getHuman();
    }

    protected function pet()
    {
        return Database::getDog();
    }
}
