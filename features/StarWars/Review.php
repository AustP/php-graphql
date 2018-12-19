<?php

namespace GraphQL\Features\StarWars;

class Review
{
    use \GraphQL\Schema\ResolverTrait;

    protected $commentary;
    protected $episode;
    protected $stars;

    public function __construct($args)
    {
        $this->commentary = $args['commentary'] ?? null;
        $this->episode = $args['episode'];
        $this->stars = $args['stars'];
    }

    public function resolve($fieldName, $args)
    {
        if ($fieldName === 'commentary') {
            return $this->commentary;
        } elseif ($fieldName === 'episode') {
            return $this->episode;
        } elseif ($fieldName === 'stars') {
            return $this->stars;
        }
    }
}
