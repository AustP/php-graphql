<?php

namespace GraphQL\Document\Parser\util;

function Keyword($string, $keyword)
{
    return LexicalToken($string, function ($string) use ($keyword) {
        $substr = substr($string, 0, strlen($keyword));
        if ($substr === $keyword) {
            return [$keyword, substr($string, strlen($keyword))];
        }

        return [null, $string];
    });
}
