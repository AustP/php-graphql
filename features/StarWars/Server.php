<?php

namespace GraphQL\Features\StarWars;

use GraphQL\Server as GraphQLServer;

class Server extends GraphQLServer
{
    protected $credits = 1000;

    public function __construct()
    {
        $path = dirname(__DIR__) . '/schemas/starwars.graphql';

        $incrementCredits = 'extend type Mutation {
            incrementCredits(amount: Int!): Int!
        }';

        parent::__construct($path, $incrementCredits);

        Database::create();

        $this->register('hero', [$this, 'hero']);
        $this->register('reviews', [$this, 'reviews']);
        $this->register('search', [$this, 'search']);
        $this->register('character', [$this, 'character']);
        $this->register('droid', [$this, 'droid']);
        $this->register('human', [$this, 'human']);
        $this->register('starship', [$this, 'starship']);

        $this->register('createReview', [$this, 'createReview']);
        $this->register('incrementCredits', [$this, 'incrementCredits']);
    }

    protected function character($args)
    {
        return Database::getCharacter($args['id']);
    }

    protected function createReview($args)
    {
        return new Review([
            'commentary' => $args['review']['commentary'] ?? null,
            'episode' => $args['episode'],
            'stars' => $args['review']['stars']
        ]);
    }

    protected function droid($args)
    {
        return Database::getDroid($args['id']);
    }

    protected function human($args)
    {
        return Database::getHuman($args['id']);
    }

    protected function hero($args)
    {
        $episode = $args['episode'] ?? '';

        if ($episode === 'EMPIRE') {
            // Luke is the hero of The Empire Strikes Back
            return Database::getCharacter(1000);
        }

        // R2-D2 is the hero for A New Hope and The Last Jedi
        return Database::getCharacter(2001);
    }

    protected function incrementCredits($args)
    {
        $this->credits += $args['amount'];
        return $this->credits;
    }

    protected function reviews($args)
    {
        return null;
    }

    protected function search($args)
    {
        return Database::search($args['text'] ?? '');
    }

    protected function starship($args)
    {
        return Database::getStarship($args['id']);
    }
}
