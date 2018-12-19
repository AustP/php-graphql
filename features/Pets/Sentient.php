<?php

namespace GraphQL\Features\Pets;

class Sentient
{
    use \GraphQL\Schema\ResolverTrait;

    protected $name;

    public function __construct($args)
    {
        $this->name = $args['name'];
    }

    public function resolve($fieldName, $args)
    {
        if ($fieldName === 'name') {
            return $this->name;
        }
    }
}
