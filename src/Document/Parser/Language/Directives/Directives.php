<?php

namespace GraphQL\Document\Parser\Language\Directives;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\util\getRepeating;

// Directives[Const] : Directive[?Const]+
function Directives($string, $location)
{
    [$directives, $substr] = getRepeating(
        $string,
        function ($directives, $substr) use ($location) {
            [$directive, $nextSubstr] = Directive($substr, $location);
            if ($directive === null) {
                return null;
            }

            if (isset($directives[$directive['name']])) {
                Parser::throwInvalid(
                    "Directive `@{$directive['name']}` is already defined.",
                    $substr
                );
            }

            $directives[$directive['name']] = $directive;
            return [$directives, $nextSubstr];
        },
        [],
        1
    );
    if ($directives === null) {
        return [null, $string];
    }

    return [$directives, $substr];
}

function DirectivesConst($string, $location)
{
    [$directives, $substr] = getRepeating(
        $string,
        function ($directives, $substr) use ($location) {
            [$directive, $nextSubstr] = DirectiveConst($substr, $location);
            if ($directive === null) {
                return null;
            }

            if (isset($directives[$directive['name']])) {
                Parser::throwInvalid(
                    "Directive `@{$directive['name']}` is already defined.",
                    $substr
                );
            }

            $directives[$directive['name']] = $directive;
            return [$directives, $nextSubstr];
        },
        [],
        1
    );
    if ($directives === null) {
        return [null, $string];
    }

    return [$directives, $substr];
}
