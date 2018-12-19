<?php

namespace GraphQL\Features\StarWars;

class Queries
{
    protected static $invalids = [
        [
            'document' => '{
                hero {...NameAndAppearancesAndFriends}
            } fragment NameAndAppearancesAndFriends on Character {
                name appearsIn friends {...NameAndAppearancesAndFriends}
            }',
            'response' => [
                'errors' => [[
                    'message' => 'Including `NameAndAppearancesAndFriends` in `friends` causes a cyclic dependency.',
                ]]
            ]
        ],
        [
            'document' => '{hero{favoriteSpaceship}}',
            'response' => [
                'errors' => [[
                    'locations' => [['column' => 7, 'line' => 1]],
                    'message' => '(1:7) The field `favoriteSpaceship` is not defined in `Character`.',
                    'path' => ['hero', 'favoriteSpaceship']
                ]]
            ]
        ],
        [
            'document' => '{hero}',
            'response' => [
                'errors' => [[
                    'locations' => [['column' => 2, 'line' => 1]],
                    'message' => '(1:2) Selections are required on `hero`.',
                    'path' => ['hero']
                ]]
            ]
        ],
        [
            'document' => '{hero{name{firstCharacterOfName}}}',
            'response' => [
                'errors' => [[
                    'locations' => [['column' => 12, 'line' => 1]],
                    'message' => '(1:12) The field `firstCharacterOfName` is not defined in the current scope.',
                    'path' => ['hero', 'name', 'firstCharacterOfName']
                ]]
            ]
        ],
        [
            'document' => '{hero{name primaryFunction}}',
            'response' => [
                'errors' => [[
                    'locations' => [['column' => 12, 'line' => 1]],
                    'message' => '(1:12) The field `primaryFunction` is not defined in `Character`.',
                    'path' => ['hero', 'primaryFunction']
                ]]
            ]
        ]
    ];

    protected static $mutations = [
        'initial' => [
            [
                'document' => 'mutation CreateReviewForEpisode($ep: Episode!, $review: ReviewInput!) {
                    createReview(episode: $ep, review: $review) {stars commentary}
                }',
                'response' => [
                    'data' => [
                        'createReview' => [
                            'commentary' => 'This is a great movie!',
                            'stars' => 5
                        ]
                    ]
                ],
                'variables' => [
                    'ep' => 'JEDI',
                    'review' => [
                        'commentary' => 'This is a great movie!',
                        'stars' => 5
                    ]
                ]
            ]
        ]
    ];

    protected static $queries = [
        'fields' => [
            [
                'document' => '{hero{name}}',
                'response' => ['data' => ['hero' => ['name' => 'R2-D2']]]
            ],
            [
                'document' => '{hero{name friends{name}}}',
                'response' => [
                    'data' => [
                        'hero' => [
                            'name' => 'R2-D2',
                            'friends' => [
                                ['name' => 'Luke Skywalker'],
                                ['name' => 'Han Solo'],
                                ['name' => 'Leia Organa']
                            ]
                        ]
                    ]
                ]
            ],
            [
                'document' => '{hero{name appearsIn}}',
                'response' => [
                    'data' => [
                        'hero' => [
                            'name' => 'R2-D2',
                            'appearsIn' => [
                                'EMPIRE',
                                'JEDI',
                                'NEWHOPE'
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'arguments' => [
            [
                'document' => '{human(id:"1000"){name height}}',
                'response' => [
                    'data' => [
                        'human' => [
                            'name' => 'Luke Skywalker',
                            'height' => 1.72
                        ]
                    ]
                ]
            ],
            [
                'document' => '{human(id:"1000"){name height(unit:FOOT)}}',
                'response' => [
                    'data' => [
                        'human' => [
                            'name' => 'Luke Skywalker',
                            'height' => 5.6430448
                        ]
                    ]
                ]
            ],
            [
                'document' => 'query{hero{name}droid(id:"2000"){name}}',
                'response' => [
                    'data' => [
                        'hero' => [
                            'name' => 'R2-D2'
                        ],
                        'droid' => [
                            'name' => 'C-3PO'
                        ]
                    ]
                ]
            ],
            [
                'document' => '{human(id: 1002) {
                    name appearsIn starships{name}}
                }',
                'response' => [
                    'data' => [
                        'human' => [
                            'name' => 'Han Solo',
                            'appearsIn' => [
                                'EMPIRE',
                                'JEDI',
                                'NEWHOPE'
                            ],
                            'starships' => [
                                ['name' => 'Millenium Falcon'],
                                ['name' => 'Imperial Shuttle']
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'aliases' => [
            [
                'document' => '{
                    empireHero: hero(episode: EMPIRE){name}
                    jediHero: hero(episode: JEDI){name}
                }',
                'response' => [
                    'data' => [
                        'empireHero' => [
                            'name' => 'Luke Skywalker'
                        ],
                        'jediHero' => [
                            'name' => 'R2-D2'
                        ]
                    ]
                ]
            ]
        ],
        'fragments' => [
            [
                'document' => '{
                    leftComparison:hero(episode:EMPIRE){...comparisonFields}
                    rightComparison:hero(episode:JEDI){...comparisonFields}
                }
                fragment comparisonFields on Character {
                    name appearsIn friends{name}
                }',
                'response' => [
                    'data' => [
                        'leftComparison' => [
                            'name' => 'Luke Skywalker',
                            'appearsIn' => [
                                'EMPIRE',
                                'JEDI',
                                'NEWHOPE'
                            ],
                            'friends' => [
                                ['name' => 'Han Solo'],
                                ['name' => 'Leia Organa'],
                                ['name' => 'C-3PO'],
                                ['name' => 'R2-D2']
                            ]
                        ],
                        'rightComparison' => [
                            'name' => 'R2-D2',
                            'appearsIn' => [
                                'EMPIRE',
                                'JEDI',
                                'NEWHOPE'
                            ],
                            'friends' => [
                                ['name' => 'Luke Skywalker'],
                                ['name' => 'Han Solo'],
                                ['name' => 'Leia Organa']
                            ]
                        ]
                    ]
                ]
            ],
            [
                'document' => '{
                    hero {
                        ...NameAndAppearances
                        friends {
                            ...NameAndAppearances friends {...NameAndAppearances}
                        }
                    }
                } fragment NameAndAppearances on Character {name appearsIn}',
                'response' => [
                    "data" => [
                        "hero" => [
                            "name" => "R2-D2",
                            "appearsIn" => [
                                "EMPIRE",
                                "JEDI",
                                "NEWHOPE"
                            ],
                            "friends" => [
                                [
                                    "name" => "Luke Skywalker",
                                    "appearsIn" => [
                                        "EMPIRE",
                                        "JEDI",
                                        "NEWHOPE"
                                    ],
                                    "friends" => [
                                        [
                                            "name" => "Han Solo",
                                            "appearsIn" => [
                                                "EMPIRE",
                                                "JEDI",
                                                "NEWHOPE"
                                            ]
                                        ],
                                        [
                                            "name" => "Leia Organa",
                                            "appearsIn" => [
                                                "EMPIRE",
                                                "JEDI",
                                                "NEWHOPE"
                                            ]
                                        ],
                                        [
                                            "name" => "C-3PO",
                                            "appearsIn" => [
                                                "EMPIRE",
                                                "JEDI",
                                                "NEWHOPE"
                                            ]
                                        ],
                                        [
                                            "name" => "R2-D2",
                                            "appearsIn" => [
                                                "EMPIRE",
                                                "JEDI",
                                                "NEWHOPE"
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    "name" => "Han Solo",
                                    "appearsIn" => [
                                        "EMPIRE",
                                        "JEDI",
                                        "NEWHOPE"
                                    ],
                                    "friends" => [
                                        [
                                            "name" => "Luke Skywalker",
                                            "appearsIn" => [
                                                "EMPIRE",
                                                "JEDI",
                                                "NEWHOPE"
                                            ]
                                        ],
                                        [
                                            "name" => "Leia Organa",
                                            "appearsIn" => [
                                                "EMPIRE",
                                                "JEDI",
                                                "NEWHOPE"
                                            ]
                                        ],
                                        [
                                            "name" => "R2-D2",
                                            "appearsIn" => [
                                                "EMPIRE",
                                                "JEDI",
                                                "NEWHOPE"
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    "name" => "Leia Organa",
                                    "appearsIn" => [
                                        "EMPIRE",
                                        "JEDI",
                                        "NEWHOPE"
                                    ],
                                    "friends" => [
                                        [
                                            "name" => "Luke Skywalker",
                                            "appearsIn" => [
                                                "EMPIRE",
                                                "JEDI",
                                                "NEWHOPE"
                                            ]
                                        ],
                                        [
                                            "name" => "Han Solo",
                                            "appearsIn" => [
                                                "EMPIRE",
                                                "JEDI",
                                                "NEWHOPE"
                                            ]
                                        ],
                                        [
                                            "name" => "C-3PO",
                                            "appearsIn" => [
                                                "EMPIRE",
                                                "JEDI",
                                                "NEWHOPE"
                                            ]
                                        ],
                                        [
                                            "name" => "R2-D2",
                                            "appearsIn" => [
                                                "EMPIRE",
                                                "JEDI",
                                                "NEWHOPE"
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'document' => '{
                    hero {name ...DroidFields}
                } fragment DroidFields on Droid {
                    primaryFunction
                }',
                'response' => [
                    'data' => [
                        'hero' => [
                            'name' => 'R2-D2',
                            'primaryFunction' => 'Astromech'
                        ]
                    ]
                ]
            ]
        ],
        'operationNames' => [
            [
                'document' => 'query HeroNameAndFriends{
                    hero{name friends{name}}
                }',
                'response' => [
                    'data' => [
                        'hero' => [
                            'name' => 'R2-D2',
                            'friends' => [
                                ['name' => 'Luke Skywalker'],
                                ['name' => 'Han Solo'],
                                ['name' => 'Leia Organa']
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'variables' => [
            [
                'document' => 'query HeroNameAndFriends($episode: Episode) {
                    hero(episode: $episode) {name friends {name}}
                }',
                'response' => [
                    'data' => [
                        'hero' => [
                            'name' => 'R2-D2',
                            'friends' => [
                                ['name' => 'Luke Skywalker'],
                                ['name' => 'Han Solo'],
                                ['name' => 'Leia Organa']
                            ]
                        ]
                    ]
                ],
                'variables' => [
                    'episode' => 'JEDI'
                ]
            ],
            [
                'document' => 'query HeroNameAndFriends($episode: Episode = JEDI) {
                    hero(episode: $episode) {name friends {name}}
                }',
                'response' => [
                    'data' => [
                        'hero' => [
                            'name' => 'R2-D2',
                            'friends' => [
                                ['name' => 'Luke Skywalker'],
                                ['name' => 'Han Solo'],
                                ['name' => 'Leia Organa']
                            ]
                        ]
                    ]
                ]
            ],
            [
                'document' => 'query DroidById($id: ID!) {
                    droid(id: $id){name}
                }',
                'response' => [
                    'data' => [
                        'droid' => [
                            'name' => 'C-3PO'
                        ]
                    ]
                    ],
                    'variables' => [
                        'id' => 2000
                    ]
            ]
        ],
        'directives' => [
            [
                'document' => 'query Hero($episode: Episode, $withFriends: Boolean!) {
                    hero(episode: $episode) {
                        name friends @include(if: $withFriends) {name}
                    }
                }',
                'response' => [
                    'data' => [
                        'hero' => [
                            'name' => 'R2-D2'
                        ]
                    ]
                ],
                'variables' => [
                    'episode' => 'JEDI',
                    'withFriends' => false
                ]
            ],
            [
                'document' => 'query Hero($episode: Episode, $withFriends: Boolean!) {
                    hero(episode: $episode) {
                        name friends @include(if: $withFriends) {name}
                    }
                }',
                'response' => [
                    'data' => [
                        'hero' => [
                            'name' => 'R2-D2',
                            'friends' => [
                                ['name' => 'Luke Skywalker'],
                                ['name' => 'Han Solo'],
                                ['name' => 'Leia Organa']
                            ]
                        ]
                    ]
                ],
                'variables' => [
                    'episode' => 'JEDI',
                    'withFriends' => true
                ]
            ]
        ],
        'inlineFragments' => [
            [
                'document' => 'query HeroForEpisode($ep: Episode!) {
                    hero(episode: $ep) {
                        name
                        ... on Droid {primaryFunction}
                        ... on Human {height}
                    }
                }',
                'response' => [
                    'data' => [
                        'hero' => [
                            'name' => 'R2-D2',
                            'primaryFunction' => 'Astromech'
                        ]
                    ]
                ],
                'variables' => [
                    'ep' => 'JEDI'
                ]
            ],
            [
                'document' => '{
                    hero {name ... on Droid {primaryFunction}}
                }',
                'response' => [
                    'data' => [
                        'hero' => [
                            'name' => 'R2-D2',
                            'primaryFunction' => 'Astromech'
                        ]
                    ]
                ]
            ]
        ],
        'metaFields' => [
            [
                'document' => '{
                    search(text: "an") {
                        __typename
                        ... on Human {name height}
                        ... on Droid {name}
                        ... on Starship {name}
                    }
                }',
                'response' => [
                    'data' => [
                        'search' => [
                            ['__typename' => 'Human', 'name' => 'Han Solo', 'height' => 1.8],
                            ['__typename' => 'Human', 'name' => 'Leia Organa', 'height' => 1.5],
                            ['__typename' => 'Starship', 'name' => 'TIE Advanced x1']
                        ]
                    ]
                ]
            ]
        ],
        'introspections' => [
            [
                'document' => '{__schema{types{name}}}',
                'response' => [
                    'data' => [
                        '__schema' => [
                            'types' => [
                                ['name' => 'Character'],
                                ['name' => 'Droid'],
                                ['name' => 'Episode'],
                                ['name' => 'Human'],
                                ['name' => 'LengthUnit'],
                                ['name' => 'Mutation'],
                                ['name' => 'Query'],
                                ['name' => 'Review'],
                                ['name' => 'ReviewInput'],
                                ['name' => 'SearchResult'],
                                ['name' => 'Starship'],
                                ['name' => 'Subscription'],
                                ['name' => '__Directive'],
                                ['name' => '__DirectiveLocation'],
                                ['name' => '__EnumValue'],
                                ['name' => '__Field'],
                                ['name' => '__InputValue'],
                                ['name' => '__Schema'],
                                ['name' => '__Type'],
                                ['name' => '__TypeKind']
                            ]
                        ]
                    ]
                ]
            ],
            [
                'document' => '{__type(name:"Droid"){
                    kind interfaces{name}enumValues{name}
                }}',
                'response' => [
                    'data' => [
                        '__type' => [
                            'kind' => 'OBJECT',
                            'interfaces' => [
                                ['name' => 'Character']
                            ],
                            'enumValues' => null
                        ]
                    ]
                ]
            ],
            [
                'document' => '{hero{__typename name}}',
                'response' => [
                    'data' => [
                        'hero' => [
                            '__typename' => 'Droid',
                            'name' => 'R2-D2'
                        ]
                    ]
                ]
            ]
        ],
        'noIntrospections' => [
            [
                'document' => '{__schema{types{name}}}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 2, 'line' => 1]],
                        'message' => '(1:2) The field `__schema` is not defined in `Query`.',
                        'path' => ['__schema']
                    ]]
                ]
            ],
            [
                'document' => '{hero{__typename name}}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 7, 'line' => 1]],
                        'message' => '(1:7) Introspection is turned off, cannot select `__typename`.',
                        'path' => ['hero', '__typename']
                    ]]
                ]
            ]
        ]
    ];

    public static function get($type)
    {
        return static::$queries[$type];
    }

    public static function getInvalids()
    {
        return static::$invalids;
    }

    public static function getMutation($type)
    {
        return static::$mutations[$type];
    }
}
