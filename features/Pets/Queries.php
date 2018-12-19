<?php

namespace GraphQL\Features\Pets;

class Queries
{
    protected static $queries = [
        'documents' => [
            [
                'document' => 'extend type Dog {color: String}
                    query getDogName {dog {name color}}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 1, 'line' => 1]],
                        'message' => '(1:1) Definition `Dog` is not defined.',
                        'path' => []
                    ]]
                ]
            ]
        ],
        'operations' => [
            [
                'document' => 'query getDogName{dog{name}}
                    query getOwnerName{dog{owner{name}}}',
                'operationName' => 'getDogName',
                'response' => [
                    'data' => [
                        'dog' => [
                            'name' => 'Copper'
                        ]
                    ]
                ]
            ],
            [
                'document' => 'query getName{dog{name}}
                    query getName{dog{owner{name}}}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 2]],
                        'message' => '(2:21) Operation `getName` is already defined.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'query dogOperation{dog{name}}
                    mutation dogOperation{mutateDog{name}}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 2]],
                        'message' => '(2:21) Operation `dogOperation` is already defined.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => '{dog{name}}',
                'response' => [
                    'data' => [
                        'dog' => [
                            'name' => 'Copper'
                        ]
                    ]
                ]
            ],
            [
                'document' => '{dog{name}}
                    query getName{dog{owner{name}}}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 2]],
                        'message' => '(2:21) An anonymous operation must be the only defined operation.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'subscription sub{newMessage{body sender}}',
                'response' => ['data' => 'TODO: Implement Subscriptions']
            ],
            [
                'document' => 'subscription sub {...newMessageFields}
                    fragment newMessageFields on Subscription {newMessage{body sender}}',
                'response' => ['data' => 'TODO: Implement Subscriptions']
            ],
            [
                'document' => 'subscription sub {
                    newMessage{body sender}
                    disallowedSecondRootField
                }',
                'response' => [
                    'errors' => [[
                        'message' => 'Subscription operations must have exactly one root field.'
                    ]]
                ]
            ],
            [
                'document' => 'subscription sub{...multipleSubscriptions}
                    fragment multipleSubscriptions on Subscription {
                        newMessage{body sender}
                        disallowedSecondRootField
                    }',
                'response' => [
                    'errors' => [[
                        'message' => 'Subscription operations must have exactly one root field.'
                    ]]
                ]
            ],
            [
                'document' => 'subscription sub{newMessage{body sender}__typename}',
                'response' => [
                    'errors' => [[
                        'message' => 'Subscription operations must have exactly one root field.'
                    ]]
                ]
            ]
        ],
        'fields' => [
            [
                'document' => 'fragment fieldNotDefined on Dog {meowVolume}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 34, 'line' => 1]],
                        'message' => '(1:34) The field `meowVolume` is not defined in `Dog`.',
                        'path' => ['meowVolume']
                    ]]
                ]
            ],
            [
                'document' => 'fragment aliasedLyingFieldTargetNotDefined on Dog {
                    barkVolume: kawVolume
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 2]],
                        'message' => '(2:21) The field `kawVolume` is not defined in `Dog`.',
                        'path' => ['barkVolume']
                    ]]
                ]
            ],
            [
                'document' => 'fragment interfaceFieldSelection on Pet {
                    name
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment definedOnImplementorsButNotInterface on Pet {
                    nickname
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 2]],
                        'message' => '(2:21) The field `nickname` is not defined in `Pet`.',
                        'path' => ['nickname']
                    ]]
                ]
            ],
            [
                'document' => 'fragment inDirectFieldSelectionOnUnion on CatOrDog {
                    __typename
                    ... on Pet {name}
                    ... on Dog {barkVolume}
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment directFieldSelectionOnUnion on CatOrDog {
                    name barkVolume
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 2]],
                        'message' => '(2:21) The field `name` is not defined in `CatOrDog`. Did you mean to use an inline fragment on `Cat` or `Dog`?',
                        'path' => ['name']
                    ]]
                ]
            ],
            [
                'document' => 'fragment mergeIdenticalFields on Dog {name name}
                    fragment mergeIdenticalAliasesAndFields on Dog {
                        otherName: name otherName: name
                    }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment conflictingBecauseAlias on Dog {
                    name: nickname name
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 41, 'line' => 1]],
                        'message' => '(1:41) Fields in a selection set need to be able to merge.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment mergeIdenticalFieldsWithIdenticalArgs on Dog {
                    doesKnowCommand(dogCommand: SIT)
                    doesKnowCommand(dogCommand: SIT)
                } fragment mergeIdenticalFieldsWithIdenticalValues on Dog {
                    doesKnowCommand(dogCommand: $dogCommand)
                    doesKnowCommand(dogCommand: $dogCommand)
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment conflictingArgsOnValues on Dog {
                    doesKnowCommand(dogCommand: SIT)
                    doesKnowCommand(dogCommand: HEEL)
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 41, 'line' => 1]],
                        'message' => '(1:41) Fields in a selection set need to be able to merge.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment conflictingArgsValueAndVar on Dog {
                    doesKnowCommand(dogCommand: SIT)
                    doesKnowCommand(dogCommand: $dogCommand)
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 44, 'line' => 1]],
                        'message' => '(1:44) Fields in a selection set need to be able to merge.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment conflictingArgsWithVars on Dog {
                    doesKnowCommand(dogCommand: $varOne)
                    doesKnowCommand(dogCommand: $varTwo)
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 41, 'line' => 1]],
                        'message' => '(1:41) Fields in a selection set need to be able to merge.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment differingArgs on Dog {
                    doesKnowCommand(dogCommand: SIT)
                    doesKnowCommand(dogCommand: HEEL)
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 31, 'line' => 1]],
                        'message' => '(1:31) Fields in a selection set need to be able to merge.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment safeDifferingFields on Pet {
                    ... on Dog {volume: barkVolume}
                    ... on Cat {volume: meowVolume}
                } fragment safeDifferingArgs on Pet {
                    ... on Dog {doesKnowCommand(dogCommand: SIT)}
                    ... on Cat {doesKnowCommand(catCommand: JUMP)}
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment conflictingDifferingResponses on Pet {
                    ... on Dog {someValue: nickname}
                    ... on Cat {someValue: meowVolume}
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 47, 'line' => 1]],
                        'message' => '(1:47) Fields in a selection set need to be able to merge.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment scalarSelection on Dog {
                    barkVolume
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment scalarSelectionsNotAllowedOnInt on Dog {
                    barkVolume {sinceWhen}
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 33, 'line' => 2]],
                        'message' => '(2:33) The field `sinceWhen` is not defined in the current scope.',
                        'path' => ['barkVolume', 'sinceWhen']
                    ]]
                ]
            ],
            [
                'document' => 'query directQueryOnObjectWithoutSubFields {
                    human
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 2]],
                        'message' => '(2:21) Selections are required on `human`.',
                        'path' => ['human']
                    ]]
                ]
            ],
            [
                'document' => 'query directQueryOnUnionWithoutSubFields {
                    catOrDog
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 2]],
                        'message' => '(2:21) Selections are required on `catOrDog`.',
                        'path' => ['catOrDog']
                    ]]
                ]
            ]
        ],
        'arguments' => [
            [
                'document' => 'fragment argOnRequiredArg on Dog {
                    doesKnowCommand(dogCommand: SIT)
                } fragment argOnOptional on Dog {
                    isHousetrained(atOtherHomes: true) @include(if: true)
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment invalidArgName on Dog {
                    doesKnowCommand(command: CLEAN_UP_HOUSE)
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 44, 'line' => 2]],
                        'message' => '(2:44) Argument `command` is not defined on `doesKnowCommand`.',
                        'path' => ['doesKnowCommand']
                    ]]
                ]
            ],
            [
                'document' => 'fragment invalidArgName on Dog {
                    isHousetrained(atOtherHomes: true) @include(unless: false)
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 71, 'line' => 2]],
                        'message' => '(2:71) Argument `unless` is not defined on `@include`.',
                        'path' => ['isHousetrained']
                    ]]
                ]
            ],
            [
                'document' => 'fragment multipleArgs on Arguments {
                    multipleReqs(x: 1, y: 2)
                } fragment multipleArgsReverseOrder on Arguments {
                    multipleReqs(y: 2, x: 1)
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment goodBooleanArg on Arguments {
                    booleanArgField(booleanArg: true)
                } fragment goodNonNullArg on Arguments {
                    nonNullBooleanArgField(nonNullBooleanArg: true)
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment goodBooleanArgDefault on Arguments {
                    booleanArgField
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment missingRequiredArg on Arguments {
                    nonNullBooleanArgField
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 17, 'line' => 3]],
                        'message' => '(3:17) Argument `nonNullBooleanArg` is required on `nonNullBooleanArgField`.',
                        'path' => ['nonNullBooleanArgField']
                    ]]
                ]
            ],
            [
                'document' => 'fragment missingRequiredArg on Arguments {
                    nonNullBooleanArgField(nonNullBooleanArg: null)
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 63, 'line' => 2]],
                        'message' => '(2:63) Value for `nonNullBooleanArg` cannot be null.',
                        'path' => ['nonNullBooleanArgField']
                    ]]
                ]
            ]
        ],
        'fragments' => [
            [
                'document' => '{
                    dog {...fragmentOne ...fragmentTwo}
                } fragment fragmentOne on Dog {
                    name
                } fragment fragmentTwo on Dog {
                    owner {name}
                }',
                'response' => [
                    'data' => [
                        'dog' => [
                            'name' => 'Copper',
                            'owner' => ['name' => 'Cloud']
                        ]
                    ]
                ]
            ],
            [
                'document' => '{
                    dog {...fragmentOne}
                } fragment fragmentOne on Dog {
                    name
                } fragment fragmentOne on Dog {
                    owner {name}
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 19, 'line' => 5]],
                        'message' => '(5:19) Operation `fragmentOne` is already defined.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment correctType on Dog {
                  name
                } fragment inlineFragment on Dog {
                    ... on Dog {name}
                } fragment inlineFragment2 on Dog {
                    ... @include(if: true) {name}
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment notOnExistingType on NotInSchema {
                    name
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 28, 'line' => 1]],
                        'message' => '(1:28) The definition `NotInSchema` is not defined in the schema.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment inlineNotExistingType on Dog {
                    ... on NotInSchema {name}
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 25, 'line' => 2]],
                        'message' => '(2:25) The definition `NotInSchema` is not defined in the schema.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment fragOnObject on Dog {
                    name
                } fragment fragOnInterface on Pet {
                    name
                } fragment fragOnUnion on CatOrDog {
                    ... on Dog {name}
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment fragOnScalar on Int {
                    something
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 23, 'line' => 1]],
                        'message' => '(1:23) Fragment cannot be defined for `Int`.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment inlineFragOnScalar on Dog {
                    ... on Boolean {somethingElse}
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 25, 'line' => 2]],
                        'message' => '(2:25) Fragment cannot be defined for `Boolean`.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment nameFragment on Dog {name}
                    {dog{name}}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 1, 'line' => 1]],
                        'message' => '(1:1) Fragment `nameFragment` must be used in the document.',
                        'path' => []
                    ]]
                ],
                'unused' => true
            ],
            [
                'document' => '{dog {...undefinedFragment}}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 7, 'line' => 1]],
                        'message' => '(1:7) Fragment `undefinedFragment` is not defined in the document.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => '{
                    dog {...nameFragment}
                } fragment nameFragment on Dog {
                    name ...barkVolumeFragment
                } fragment barkVolumeFragment on Dog {
                    barkVolume ...nameFragment
                }',
                'response' => [
                    'errors' => [[
                        'message' => 'Including `barkVolumeFragment` in `nameFragment` causes a cyclic dependency.'
                    ]]
                ]
            ],
            [
                'document' => '{
                    dog {...dogFragment}
                } fragment dogFragment on Dog {
                    name owner {...ownerFragment}
                } fragment ownerFragment on Human {
                    name pets {...dogFragment}
                }',
                'response' => [
                    'errors' => [[
                        'message' => 'Including `ownerFragment` in `owner` causes a cyclic dependency.'
                    ]]
                ]
            ],
            [
                'document' => 'fragment dogFragment on Dog {
                    ... on Dog {barkVolume}
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment catInDogFragmentInvalid on Dog {
                    ... on Cat {meowVolume}
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 2]],
                        'message' => '(2:21) Inline fragment can never spread.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment petNameFragment on Pet {
                    name
                } fragment interfaceWithinObjectFragment on Dog {
                    ...petNameFragment
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment catOrDogNameFragment on CatOrDog {
                    ... on Cat {meowVolume}
                } fragment unionWithObjectFragment on Dog {
                    ...catOrDogNameFragment
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment petFragment on Pet {
                    name ... on Dog {barkVolume}
                } fragment catOrDogFragment on CatOrDog {
                    ... on Cat {meowVolume}
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment sentientFragment on Sentient {
                    ... on Dog {barkVolume}
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 2]],
                        'message' => '(2:21) Inline fragment can never spread.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment humanOrAlienFragment on HumanOrAlien {
                    ... on Cat {meowVolume}
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 2]],
                        'message' => '(2:21) Inline fragment can never spread.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'fragment unionWithInterface on Pet {
                    ...dogOrHumanFragment
                } fragment dogOrHumanFragment on DogOrHuman {
                    ... on Dog {barkVolume}
                }',
                'response' => ['data' => []]
            ],
            [
                'document' => 'fragment nonIntersectingInterfaces on Pet {
                    ...sentientFragment
                } fragment sentientFragment on Sentient {
                    name
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 2]],
                        'message' => '(2:21) Fragment `sentientFragment` can never spread.',
                        'path' => []
                    ]]
                ]
            ]
        ],
        'values' => [
            [
                'document' => 'fragment goodBooleanArg on Arguments {
                    booleanArgField(booleanArg: true)
                } fragment coercedIntIntoFloatArg on Arguments {
                    floatArgField(floatArg: 123)
                } query goodComplexDefaultValue($search: ComplexInput = { name: "Copper" }) {
                    findDog(complex: $search) {name}
                }',
                'response' => [
                    'data' => [
                        'findDog' => [
                            'name' => 'Copper'
                        ]
                    ]
                ]
            ],
            [
                'document' => 'fragment stringIntoInt on Arguments {
                    intArgField(intArg: "123")
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 41, 'line' => 2]],
                        'message' => '(2:41) Expected `intArg` to be an Int, found `String`.',
                        'path' => ['intArgField']
                    ]]
                ]
            ],
            [
                'document' => 'query badComplexValue {
                    findDog(complex: { name: 123 })
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 38, 'line' => 2]],
                        'message' => '(2:38) Expected `name` to contain a String, found `Int`.',
                        'path' => ['findDog']
                    ]]
                ]
            ],
            [
                'document' => '{findDog(complex:{name:"Copper"}){name}}',
                'response' => [
                    'data' => [
                        'findDog' => [
                            'name' => 'Copper'
                        ]
                    ]
                ]
            ],
            [
                'document' => '{
                    findDog(complex:{favoriteCookieFlavor:"Bacon"}){name}
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 37, 'line' => 2]],
                        'message' => '(2:37) `favoriteCookieFlavor` is not defined in `ComplexInput`.',
                        'path' => ['findDog']
                    ]]
                ]
            ],
            [
                'document' => '{findDog(complex:{name:"A" name:"Copper"}){name}}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 28, 'line' => 1]],
                        'message' => '(1:28) Field `name` is already defined.',
                        'path' => ['findDog']
                    ]]
                ]
            ]
        ],
        'directives' => [
            [
                'document' => 'query @skip(if: $foo) {dog}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 7, 'line' => 1]],
                        'message' => '(1:7) Directive `@skip` cannot be used on `QUERY`.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'query ($foo: Boolean = true, $bar: Boolean = false) {
                    dog @skip(if: $foo) @skip(if: $bar)
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 41, 'line' => 2]],
                        'message' => '(2:41) Directive `@skip` is already defined.',
                        'path' => ['dog']
                    ]]
                ]
            ],
            [
                'document' => 'query ($foo: Boolean = true, $bar: Boolean = false) {
                    dog @skip(if: $foo) {name}
                    human @skip(if: $bar) {name}
                }',
                'response' => [
                    'data' => [
                        'human' => [
                            'name' => 'Cloud'
                        ]
                    ]
                ]
            ]
        ],
        'variables' => [
            [
                'document' => 'query houseTrainedQuery($atOtherHomes: Boolean, $atOtherHomes: Boolean) {
                    dog {isHousetrained(atOtherHomes: $atOtherHomes)}
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 49, 'line' => 1]],
                        'message' => '(1:49) Variable `$atOtherHomes` is already defined.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'query A($atOtherHomes: Boolean) {
                    ...HouseTrainedFragment
                } query B($atOtherHomes: Boolean) {
                    ...HouseTrainedFragment
                } fragment HouseTrainedFragment on Query {
                    dog {isHousetrained(atOtherHomes: $atOtherHomes)}
                }',
                'operationName' => 'A',
                'response' => [
                    'data' => [
                        'dog' => [
                            'isHousetrained' => false
                        ]
                    ]
                ]
            ],
            [
                'document' => 'query takesBoolean($atOtherHomes: Boolean) {
                    dog {isHousetrained(atOtherHomes: $atOtherHomes)}
                } query takesComplexInput($complexInput: ComplexInput) {
                    findDog(complex: $complexInput) {name}
                } query TakesListOfBooleanBang($booleans: [Boolean!]) {
                    booleanList(booleanListArg: $booleans)
                }',
                'operationName' => 'takesBoolean',
                'response' => [
                    'data' => [
                        'dog' => [
                            'isHousetrained' => false
                        ]
                    ]
                ]
            ],
            [
                'document' => 'query takesCat($cat: Cat) {dog}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 16, 'line' => 1]],
                        'message' => '(1:16) Variable `$cat` must be an input type.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'query takesDogBang($dog: Dog!) {dog}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 20, 'line' => 1]],
                        'message' => '(1:20) Variable `$dog` must be an input type.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'query takesListOfPet($pets: [Pet]) {dog}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 22, 'line' => 1]],
                        'message' => '(1:22) Variable `$pets` must be an input type.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'query takesCatOrDog($catOrDog: CatOrDog) {dog}',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 21, 'line' => 1]],
                        'message' => '(1:21) Variable `$catOrDog` must be an input type.',
                        'path' => []
                    ]]
                ]
            ],
            [
                'document' => 'query variableIsDefined($atOtherHomes: Boolean) {
                    dog {isHousetrained(atOtherHomes: $atOtherHomes)}
                }',
                'response' => [
                    'data' => [
                        'dog' => [
                            'isHousetrained' => false
                        ]
                    ]
                ]
            ],
            [
                'document' => 'query variableIsNotDefined {
                    dog {isHousetrained(atOtherHomes: $atOtherHomes)}
                }',
                'response' => [
                    'errors' => [[
                        'locations' => [['column' => 55, 'line' => 2]],
                        'message' => '(2:55) Variable `$atOtherHomes` is not defined in the operation.',
                        'path' => ['dog', 'isHousetrained']
                    ]]
                ]
            ],
            [
                'document' => 'query variableIsDefinedUsedInSingleFragment($atOtherHomes: Boolean) {
                    dog {...isHousetrainedFragment}
                } fragment isHousetrainedFragment on Dog {
                    isHousetrained(atOtherHomes: $atOtherHomes)
                }',
                'response' => [
                    'data' => [
                        'dog' => [
                            'isHousetrained' => false
                        ]
                    ]
                ]
            ],
            [
                'document' => 'query variableIsNotDefinedUsedInSingleFragment {
                    dog {...isHousetrainedFragment}
                } fragment isHousetrainedFragment on Dog {
                    isHousetrained(atOtherHomes: $atOtherHomes)
                }',
                'response' => [
                    'errors' => [[
                        'message' => 'Variable `$atOtherHomes` is not defined in `variableIsNotDefinedUsedInSingleFragment`.' // ignore-code-standards
                    ]]
                ]
            ],
            [
                'document' => 'query variableIsNotDefinedUsedInNestedFragment {
                    dog {...outerHousetrainedFragment}
                } fragment outerHousetrainedFragment on Dog {
                    ...isHousetrainedFragment
                } fragment isHousetrainedFragment on Dog {
                    isHousetrained(atOtherHomes: $atOtherHomes)
                }',
                'response' => [
                    'errors' => [[
                        'message' => 'Variable `$atOtherHomes` is not defined in `variableIsNotDefinedUsedInNestedFragment`.' // ignore-code-standards
                    ]]
                ]
            ],
            [
                'document' => 'query housetrainedQueryOne($atOtherHomes: Boolean) {
                    dog {...isHousetrainedFragment}
                } query housetrainedQueryTwo($atOtherHomes: Boolean) {
                    dog {...isHousetrainedFragment}
                } fragment isHousetrainedFragment on Dog {
                    isHousetrained(atOtherHomes: $atOtherHomes)
                }',
                'operationName' => 'housetrainedQueryOne',
                'response' => [
                    'data' => [
                        'dog' => [
                            'isHousetrained' => false
                        ]
                    ]
                ]
            ],
            [
                'document' => 'query housetrainedQueryOne($atOtherHomes: Boolean) {
                    dog {...isHousetrainedFragment}
                } query housetrainedQueryTwoNotDefined {
                    dog {...isHousetrainedFragment}
                } fragment isHousetrainedFragment on Dog {
                    isHousetrained(atOtherHomes: $atOtherHomes)
                }',
                'operationName' => 'housetrainedQueryOne',
                'response' => [
                    'errors' => [[
                        'message' => 'Variable `$atOtherHomes` is not defined in `housetrainedQueryTwoNotDefined`.'
                    ]]
                ]
            ],
            [
                'document' => 'query variableUnused($atOtherHomes: Boolean) {
                    dog {isHousetrained}
                }',
                'response' => [
                    'errors' => [[
                        'message' => 'Variable `$atOtherHomes` is not used in `variableUnused`.'
                    ]]
                ]
            ],
            [
                'document' => 'query variableUsedInFragment($atOtherHomes: Boolean) {
                    dog {...isHousetrainedFragment}
                } fragment isHousetrainedFragment on Dog {
                    isHousetrained(atOtherHomes: $atOtherHomes)
                }',
                'response' => [
                    'data' => [
                        'dog' => [
                            'isHousetrained' => false
                        ]
                    ]
                ]
            ],
            [
                'document' => 'query variableNotUsedWithinFragment($atOtherHomes: Boolean) {
                    dog {...isHousetrainedWithoutVariableFragment}
                } fragment isHousetrainedWithoutVariableFragment on Dog {
                    isHousetrained
                }',
                'response' => [
                    'errors' => [[
                        'message' => 'Variable `$atOtherHomes` is not used in `variableNotUsedWithinFragment`.'
                    ]]
                ]
            ],
            [
                'document' => 'query queryWithUsedVar($atOtherHomes: Boolean) {
                    dog {...isHousetrainedFragment}
                } query queryWithExtraVar($atOtherHomes: Boolean, $extra: Int) {
                    dog {...isHousetrainedFragment}
                } fragment isHousetrainedFragment on Dog {
                    isHousetrained(atOtherHomes: $atOtherHomes)
                }',
                'operationName' => 'queryWithUsedVar',
                'response' => [
                    'errors' => [[
                        'message' => 'Variable `$extra` is not used in `queryWithExtraVar`.'
                    ]]
                ]
            ],
            [
                'document' => 'query intCannotGoIntoBoolean($intArg: Int) {
                    arguments {booleanArgField(booleanArg: $intArg)}
                }',
                'response' => [
                    'errors' => [[
                        'message' => 'Variable `$intArg` cannot be used as an argument to `booleanArg`.'
                    ]]
                ]
            ],
            [
                'document' => 'query booleanListCannotGoIntoBoolean($booleanListArg: [Boolean]) {
                    arguments {booleanArgField(booleanArg: $booleanListArg)}
                }',
                'response' => [
                    'errors' => [[
                        'message' => 'Variable `$booleanListArg` cannot be used as an argument to `booleanArg`.'
                    ]]
                ]
            ],
            [
                'document' => 'query booleanArgQuery($booleanArg: Boolean) {
                    arguments {nonNullBooleanArgField(nonNullBooleanArg: $booleanArg)}
                }',
                'response' => [
                    'errors' => [[
                        'message' => 'Variable `$booleanArg` cannot be used as an argument to `nonNullBooleanArg`.'
                    ]]
                ]
            ],
            [
                'document' => 'query nonNullListToList($nonNullBooleanList: [Boolean]!) {
                    arguments {booleanListArgField(booleanListArg: $nonNullBooleanList)}
                }',
                'response' => [
                    'data' => [
                        'arguments' => [
                            'booleanListArgField' => [true]
                        ]
                    ]
                ],
                'variables' => [
                    'nonNullBooleanList' => [true]
                ]
            ],
            [
                'document' => 'query listToNonNullList($booleanList: [Boolean]) {
                    arguments {
                        booleanListArgField(booleanListArg: $booleanList)
                    }
                }',
                'response' => [
                    'errors' => [[
                        'message' => 'Variable `$booleanList` cannot be used as an argument to `booleanListArg`.'
                    ]]
                ]
            ],
            [
                'document' => 'query booleanArgQueryWithDefault($booleanArg: Boolean) {
                    arguments {optionalNonNullBooleanArgField(optionalBooleanArg: $booleanArg)}
                }',
                'response' => [
                    'data' => [
                        'arguments' => [
                            'optionalNonNullBooleanArgField' => true
                        ]
                    ]
                ]
            ],
            [
                'document' => 'query booleanArgQueryWithDefault($booleanArg: Boolean = true) {
                    arguments {nonNullBooleanArgField(nonNullBooleanArg: $booleanArg)}
                }',
                'response' => [
                    'data' => [
                        'arguments' => [
                            'nonNullBooleanArgField' => false
                        ]
                    ]
                ]
            ]
        ]
    ];

    public static function get($type)
    {
        return static::$queries[$type];
    }
}
