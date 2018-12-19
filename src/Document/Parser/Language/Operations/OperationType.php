<?php

namespace GraphQL\Document\Parser\Language\Operations;

use function GraphQL\Document\Parser\util\Keyword;

// OperationType : one of query mutation subscription
function OperationType($string)
{
    [$query, $substr] = Keyword($string, 'query');
    if ($query === 'query') {
        return [$query, $substr];
    }

    [$mutation, $substr] = Keyword($string, 'mutation');
    if ($mutation === 'mutation') {
        return [$mutation, $substr];
    }

    [$subscription, $substr] = Keyword($string, 'subscription');
    if ($subscription === 'subscription') {
        return [$subscription, $substr];
    }

    return [null, $string];
}
