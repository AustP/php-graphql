<?php

namespace GraphQL\Features\Pets;

class Database
{
    protected static $alien;
    protected static $arguments;
    protected static $cat;
    protected static $dog;
    protected static $human;

    public static function create()
    {
        // store instantiated versions so on recursive queries,
        // PHP can just use references instead of instantiating new ones

        static::$alien = new Alien([
            'homePlanet' => 'Krypton',
            'name' => 'Kal-El'
        ]);

        static::$arguments = new Arguments();

        static::$cat = new Cat([
            'commands' => ['JUMP'],
            'meowVolume' => 11,
            'name' => 'Red XIII',
            'nickname' => 'Red'
        ]);

        static::$dog = new Dog([
            'barkVolume' => 7,
            'commands' => ['DOWN', 'SIT'],
            'name' => 'Copper'
        ]);

        static::$human = new Human([
            'name' => 'Cloud'
        ]);
    }

    public static function getAlien()
    {
        return static::$alien;
    }

    public static function getArguments()
    {
        return static::$arguments;
    }

    public static function getCat()
    {
        return static::$cat;
    }

    public static function getDog()
    {
        return static::$dog;
    }

    public static function getHuman()
    {
        return static::$human;
    }
}
