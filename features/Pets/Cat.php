<?php

namespace GraphQL\Features\Pets;

class Cat extends Pet
{
    protected $commands;
    protected $meowVolume;
    protected $nickname;

    public function __construct($args)
    {
        $this->commands = $args['commands'] ?? [];
        $this->meowVolume = $args['meowVolume'] ?? null;
        $this->nickname = $args['nickname'] ?? null;

        parent::__construct($args);
    }

    public function resolve($fieldName, $args)
    {
        if ($fieldName === 'doesKnowCommand') {
            return in_array($args['catCommand'], $this->commands);
        } elseif ($fieldName === 'meowVolume') {
            return $this->meowVolume;
        } elseif ($fieldName === 'nickname') {
            return $this->nickname;
        }

        return parent::resolve($fieldName, $args);
    }
}
