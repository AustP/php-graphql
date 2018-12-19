<?php

namespace GraphQL\Document\Parser\Language\SelectionSets;

use GraphQL\Document\Parser;
use function GraphQL\Document\Parser\Language\SourceText\Punctuator;
use function GraphQL\Document\Parser\util\getRepeating;

// SelectionSet : { Selection+ }
function SelectionSet($string)
{
    [$leftBracket, $substr] = Punctuator($string);
    if ($leftBracket !== '{') {
        return [null, $string];
    }

    [$selections, $nextSubstr] = getRepeating(
        $substr,
        function ($selections, $substr) {
            [$selection, $nextSubstr] = Selection($substr);
            if ($selection === null) {
                return null;
            }

            // NOTE: we don't want a unique-keyed array here
            $selections[] = $selection;
            return [$selections, $nextSubstr];
        },
        [],
        1
    );
    if ($selections === null) {
        Parser::throwSyntax('Selection', $substr);
    }

    [$rightBracket, $substr] = Punctuator($nextSubstr);
    if ($rightBracket !== '}') {
        Parser::throwSyntax('}', $nextSubstr);
    }

    return [['scope' => Parser::$scope, 'set' => $selections], $substr];
}
