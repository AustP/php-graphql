<?php

namespace GraphQL\Features\StarWars;

class Database
{
    protected static $droids = [];
    protected static $humans = [];
    protected static $starships = [];

    public static function create()
    {
        // store instantiated versions so on recursive queries,
        // PHP can just use references instead of instantiating new ones

        static::$humans[1000] = new Human([
            'appearsIn' => ['EMPIRE', 'JEDI', 'NEWHOPE'],
            'friends' => [1002, 1003, 2000, 2001],
            'height' => 1.72,
            'id' => 1000,
            'mass' => 77,
            'name' => 'Luke Skywalker',
            'starships' => [3001, 3003]
        ]);

        static::$humans[1001] = new Human([
            'appearsIn' => ['EMPIRE', 'JEDI', 'NEWHOPE'],
            'friends' => [1004],
            'height' => 2.02,
            'id' => 1001,
            'mass' => 136,
            'name' => 'Darth Vadar',
            'starships' => [3002]
        ]);

        static::$humans[1002] = new Human([
            'appearsIn' => ['EMPIRE', 'JEDI', 'NEWHOPE'],
            'friends' => [1000, 1003, 2001],
            'height' => 1.8,
            'id' => 1002,
            'mass' => 80,
            'name' => 'Han Solo',
            'starships' => [3000, 3003]
        ]);

        static::$humans[1003] = new Human([
            'appearsIn' => ['EMPIRE', 'JEDI', 'NEWHOPE'],
            'friends' => [1000, 1002, 2000, 2001],
            'height' => 1.5,
            'id' => 1003,
            'mass' => 49,
            'name' => 'Leia Organa',
            'starships' => []
        ]);

        static::$humans[1004] = new Human([
            'appearsIn' => ['NEWHOPE'],
            'friends' => [1001],
            'height' => 1.8,
            'id' => 1004,
            'mass' => null,
            'name' => 'Wilhuff Tarkin',
            'starships' => []
        ]);

        static::$droids[2000] = new Droid([
            'appearsIn' => ['EMPIRE', 'JEDI', 'NEWHOPE'],
            'friends' => [1000, 1002, 1003, 2001],
            'id' => 2000,
            'name' => 'C-3PO',
            'primaryFunction' => 'Protocol'
        ]);

        static::$droids[2001] = new Droid([
            'appearsIn' => ['EMPIRE', 'JEDI', 'NEWHOPE'],
            'friends' => [1000, 1002, 1003],
            'id' => 2001,
            'name' => 'R2-D2',
            'primaryFunction' => 'Astromech'
        ]);



        static::$starships[3000] = new Starship([
            'id' => 3000,
            'length' => 34.37,
            'name' => 'Millenium Falcon'
        ]);

        static::$starships[3001] = new Starship([
            'id' => 3001,
            'length' => 12.5,
            'name' => 'X-Wing'
        ]);

        static::$starships[3002] = new Starship([
            'id' => 3002,
            'length' => 9.2,
            'name' => 'TIE Advanced x1'
        ]);

        static::$starships[3003] = new Starship([
            'id' => 3003,
            'length' => 20,
            'name' => 'Imperial Shuttle'
        ]);
    }

    public static function getCharacter($id)
    {
        $droid = static::getDroid($id);
        if ($droid !== null) {
            return $droid;
        }

        return static::getHuman($id);
    }

    public static function getDroid($id)
    {
        return static::$droids[$id] ?? null;
    }

    public static function getHuman($id)
    {
        return static::$humans[$id] ?? null;
    }

    public static function getStarship($id)
    {
        return static::$starships[$id] ?? null;
    }

    public static function search($text)
    {
        $matches = [];

        foreach (static::$droids as $droid) {
            if ($text === '' || $droid->matches($text)) {
                $matches[] = $droid;
            }
        }

        foreach (static::$humans as $human) {
            if ($text === '' || $human->matches($text)) {
                $matches[] = $human;
            }
        }

        foreach (static::$starships as $starship) {
            if ($text === '' || $starship->matches($text)) {
                $matches[] = $starship;
            }
        }

        return $matches;
    }
}
