<?php

namespace GraphQL\Document\Parser\util;

use GraphQL\Document\Parser;

function arrayMergeUnique($arr1, $arr2, $identifier, $string, $type)
{
    $found = array_intersect_key($arr1, $arr2);
    if (count($found) >= 1) {
        $message = $type . ' `' . array_pop($found)['name']
            . "` is already defined on `$identifier`.";
        Parser::throwInvalid($message, $string);
        return [$definitions, $substr];
    }

    return array_merge($arr1, $arr2);
}
