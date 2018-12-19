<?php

namespace GraphQL\Schema;

class Resolver
{
    use ResolverTrait;

    protected $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    protected function resolve($fieldName, $args)
    {
        return call_user_func_array($this->callback, [$fieldName, $args]);
    }
}
