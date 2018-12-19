<?php

namespace GraphQL\Features\StarWars;

class Character
{
    use \GraphQL\Schema\ResolverTrait;

    protected $appearsIn;
    protected $friends;
    protected $id;
    protected $name;

    public function __construct($args)
    {
        $this->appearsIn = $args['appearsIn'];
        $this->friends = $args['friends'] ?? null;
        $this->id = $args['id'];
        $this->name = $args['name'];
    }

    public function matches($text)
    {
        return stripos($this->name, $text) !== false;
    }

    public function resolve($fieldName, $args)
    {
        if ($fieldName === 'appearsIn') {
            return $this->appearsIn;
        } elseif ($fieldName === 'friends') {
            return array_map(function ($friendId) {
                return Database::getCharacter($friendId);
            }, $this->friends);
        } elseif ($fieldName === 'id') {
            return $this->id;
        } elseif ($fieldName === 'name') {
            return $this->name;
        }
    }
}
