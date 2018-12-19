# php-graphql

*Because building a PHP GraphQL server should be easy.*

## Introduction

php-graphql is a schema-driven GraphQL server implemented in PHP. This means that you can focus more on building a great API and less on figuring out how to get your GraphQL server set up.

This implementation was built against the [official GraphQL specs](https://github.com/facebook/graphql/tree/master/spec). The spec is fully implemented with the exception of two items. The first is subscriptions aren't implemented yet. And the second is that query execution runs synchronously because PHP doesn't support asynchronous operations natively.

## Getting started

### Top-Level Resolvers

Let's start with a simple example. Here is our schema document:

```(graphql)
type Query {
    hello: String!
}
```

And here is our PHP file (notice the embedded query):

```(php)
<?php

require('vendor/autoload.php');

$server = new GraphQL\Server('/path/to/schema.graphql');

$server->register('hello', function () {
    return 'Hello World!';
});

$query = '{
    hello
}';

$response = $server->handle($query);
print(json_encode($response));
```

The response from our server would be: `{"data":{"hello":"Hello World!"}}`.

This example is pretty contrived but already you can see how php-graphql utilizes your schema document so all that you need to do is register a few resolver functions. You will need to register a resolver function for each top-level field in your schema document. (Any fields in Query, Mutation, or Subscription).

### Arguments

Now let's continue by upgrading our schema document:

```(graphql)
type Query {
    hello: String!
    echo(message: String = "Pong!"): String!
}
```

And altering our PHP file:

```(php)
$server->register('echo', function ($args) {
    return $args['message'];
});

$query = '{
    ping: echo(message: "Ping!")
    pong: echo
}';
```

The response from our server would be: `{"data":{"ping":"Ping!","pong":"Pong!"}}`.

The `$args` set contains all of the arguments passed to the field, and for missing arguments that have default values, the default values will be present. For top-level resolvers, it is the first parameter passed in, but for nested resolvers, it is the second parameter passed in.

### Variables

Now it's time to make our server work with client-side variables. How the server gets the variables is outside of our scope, but the point remains the same:

```(php)
$query = 'query PingPong($one: String, $two: String = "Two!", $three: String) {
    one: echo(message: $one)
    two: echo(message: $two)
    three: echo(message: $three)
}';

$variables = ['one' => 'One!'];

$response = $server->handle($query, $variables);
```

This time the response would be: `{"data":{"one":"One!","two":"Two!","three":"Pong!"}}`.

If you reference any variables in your query, you can pass in their values as the second parameter to the `handle` method. Notice that the order of resolution for default values is the variable's default value followed by the schema's default value.

## Resolvers

### ResolverTrait

Top-level resolvers are straightforward in that they're just a function. But in order to handle the deep nested queries that GraphQL supports, we must use something more robust.

There are two ways to implement resolvers in php-graphql. The first way is by using the ResolverTrait. When you use the `ResolverTrait` in a class, you just need to define the resolve method to make your class a resolver. Let's go with another example:

```(graphql)
type Query {
    kaladin: Human!
}

type Human {
    name: String!
    friends: [Human!]!
}
```

```(php)
class Human
{
    use GraphQL\Schema\ResolverTrait;

    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    protected function resolve($fieldName, $args)
    {
        if ($fieldName === 'name') {
            return $this->name;
        } elseif ($fieldName === 'friends') {
            return [
                new self('Adolin'),
                new self('Dalinar')
            ];
        }
    }
}
```

```(php)
$server->register('kaladin', function () {
    return new Human('Kaladin');
});

$query = '{
    kaladin {
        name
        friends {
            name
        }
    }
}';
```

In this case, the server would respond with: `{"data":{"kaladin":{"name":"Kaladin","friends":[{"name":"Adolin"},{"name":"Dalinar"}]}}}`.

You can see how easy it is to convert any class you already have into a resolver. The `resolve` method only takes the two parameters. The first is the field name being resolved and the second is the set of arguments passed to the field.

In the `resolve` method above, the friends response returns a list of Human instantiations. Because they too are resolvers, they subsequently have the resolve method called on them to resolve their names.

### Resolver class

Although using `ResolverTrait` is the recommended approach, sometimes you need to implement a resolver without dealing with creating your own class. That's when you should look at instantiating the `Resolver` class with your resolver function. With the above schema, it would look something like this:

```(php)
$server->register('kaladin', function () {
    return new GraphQL\Schema\Resolver(function ($fieldName, $args) {
        if ($fieldName === 'name') {
            return 'Kaladin';
        }
    });
});

$query = '{
    kaladin {
        name
    }
}';
```

The server in this case would return: `{"data":{"kaladin":{"name":"Kaladin"}}}`.

Of course if you tried to make the resolver return a list of friends which could then in turn be queried, it's gonna start getting ugly. This is why using `ResolverTrait` in a class-based system is the recommended approach.

## Code Design

### Class-Based System

Because of the way you write GraphQL schema documents, it becomes quite handy if you have a one-to-one mapping between the types in your schema document and PHP classes. When you do this, you can compare your schema type definition directly against the class's resolve method to ensure full API coverage.

By default, php-graphql assumes that your resolver classes' names line up with the types defined in your schema document. If they don't, it is important for you to override the default typename mapping function. This method is used for determining if a resolver should run if it's part of a union or if it's an interface. (See Options.Introspection below on how to override the method).

Pasting full classes here would be less than useful, but there is an example for you to follow in this repository. Compare the src/schemas/introspection.graphql schema document to the PHP classes defined in src/Introspection to get a feel of how useful this pattern is.

The astute reader might even notice that there are classes defined for the enum types. If the system comes across a class when it's expecting an enum or scalar, it will check if it's a resolver (`$class->isResolver`). If it is, the system will call the `resolve` method on the class.

These `resolve` methods must be declared `public` and they receive different parameters than normal resolvers. For enums, the first parameter is a list of possible enum values and the second is the schema definition for the enum. For scalars, the first and only parameter is the schema definition for the scalar.

*Note: Only for enums and scalars can you "short-circuit" the logic by setting `public $isResolver = true;` and have the resolution work out. For other types, you must use `ResolverTrait`.*

When you define your own scalars, using a scalar class to resolve the value is a great way to make sure your scalar is always returned in the correct format. Take the following as an example:

```(graphql)
scalar Date
```

```(php)
class ScalarDate
{
    public $isResolver = true;

    protected $timestamp;

    public function __construct($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function resolve()
    {
        return date('c', $this->timestamp);
    }
}
```

## Options

### Caching

By default, the server will cache well-structured and valid documents. On subsequent requests, the document will be loaded without having to re-parse or re-validate their contents. Having caching turned on significantly reduces the amount of time it takes to process a request.

| Property          | Default   | Description                                |
|-------------------|-----------|--------------------------------------------|
| Server::$cacheDir | `"/tmp/"` | The directory to write the cache files in. |
| Server::$useCache | `true`    | Whether to use caching or not.             |

### Result Coercion

The server will not automatically coerce results to be the correct type. The reason for this is because it is a blind coercion so it could result in data loss. Because you are in control of the data that is returned, you will know if you can turn on result coercion without losing any data.

| Property                   | Default | Description                |
|----------------------------|---------|----------------------------|
| Server::$useResultCoercion | `false` | Whether to coerce results. |

### Introspection

By default, the server is setup to respond to introspection queries. If the caching system is enabled, the time it takes to setup the introspection system is non-measurable. However, it can be disabled.

| Property                   | Default | Description                              |
|----------------------------|---------|------------------------------------------|
| Server::$useIntrospection  | `true`  | Whether to use the introspection system. |

Another aspect of the introspection system that can be configured is how the system resolves `__typename` requests in a query. By default, the system will return the name of the Resolver class in which the `__typename` request was encountered. If the name of the class is `Resolver` however, it will return the name of the current scope.

If you wish to override this behavior (i.e. your class names don't match your schema type names), you can call `Server::setTypenameResolver($resolver)` with a resolver function. The resolver function will be passed the current resolver as the first parameter and the current scope name as the second parameter.

## Installation

```(bash)
composer require austp/php-graphql
```

## Running Tests

```(bash)
composer install
vendor/bin/peridot
```
