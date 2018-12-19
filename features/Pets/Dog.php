<?php

namespace GraphQL\Features\Pets;

class Dog extends Pet
{
    protected $barkVolume;
    protected $commands;
    protected $isHousetrained;
    protected $isHousetrainedAtOtherHomes;
    protected $nickname;

    public function __construct($args)
    {
        $this->barkVolume = $args['barkVolume'] ?? null;
        $this->commands = $args['commands'] ?? [];
        $this->isHousetrained = $args['isHousetrained'] ?? false;
        $this->isHousetrainedAtOtherHomes =
            $args['isHousetrainedAtOtherHomes'] ?? false;
        $this->nickname = $args['nickname'] ?? null;

        parent::__construct($args);
    }

    public function resolve($fieldName, $args)
    {
        if ($fieldName === 'barkVolume') {
            return $this->barkVolume;
        } elseif ($fieldName === 'doesKnowCommand') {
            return in_array($args['dogCommand'], $this->commands);
        } elseif ($fieldName === 'isHousetrained') {
            if (($args['atOtherHomes'] ?? false) === true) {
                return $this->isHousetrainedAtOtherHomes;
            } else {
                return $this->isHousetrained;
            }
        } elseif ($fieldName === 'nickname') {
            return $this->nickname;
        } elseif ($fieldName === 'owner') {
            return Database::getHuman();
        }

        return parent::resolve($fieldName, $args);
    }
}
