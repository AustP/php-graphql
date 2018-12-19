<?php

namespace GraphQL\Features\Pets;

class Arguments
{
    use \GraphQL\Schema\ResolverTrait;

    public function resolve($fieldName, $args)
    {
        if ($fieldName === 'booleanArgField') {
            // booleanArgField(booleanArg: Boolean): Boolean
            return null;
        } elseif ($fieldName === 'booleanListArgField') {
            // booleanListArgField(booleanListArg: [Boolean]!): [Boolean]
            return [true];
        } elseif ($fieldName === 'floatArgField') {
            // floatArgField(floatArg: Float): Float
            return 1.2;
        } elseif ($fieldName === 'intArgField') {
            // intArgField(intArg: Int): Int
            return 42;
        } elseif ($fieldName === 'multipleReqs') {
            // multipleReqs(x: Int!, y: Int!): Int!
            return 7;
        } elseif ($fieldName === 'nonNullBooleanArgField') {
            // nonNullBooleanArgField(nonNullBooleanArg: Boolean!): Boolean!
            return false;
        } elseif ($fieldName === 'optionalNonNullBooleanArgField') {
            // optionalNonNullBooleanArgField(optionalBooleanArg: Boolean! = false): Boolean!
            return true;
        }
    }
}
