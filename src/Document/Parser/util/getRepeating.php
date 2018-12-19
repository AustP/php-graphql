<?php

namespace GraphQL\Document\Parser\util;

function getRepeating($string, $reducer, $value, $minimum = 0)
{
    $count = 0;
    $substr = $string;
    while (true) {
        if (strlen($substr) === 0) {
            break;
        }

        $result = $reducer($value, $substr);
        if ($result === null) {
            break;
        }

        [$value, $substr] = $result;
        $count++;
    }

    return [$count >= $minimum ? $value : null, $substr];
}
