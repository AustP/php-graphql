<?php

namespace GraphQL\Introspection;

class DirectiveLocation
{
    public $isResolver = true;

    protected $location;

    public function __construct($location)
    {
        $this->location = $location;
    }

    public function resolve($locations)
    {
        return isset($locations[$this->location]) ?
            $this->location :
            null;
    }
}
