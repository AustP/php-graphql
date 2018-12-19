<?php

namespace GraphQL\Document\Parser\Exceptions;

use GraphQL\Document\Exceptions\ServerError;
use GraphQL\Document\Parser;

class SyntaxError extends ServerError
{
    public $column;
    public $expected;
    public $found;
    public $documentLine;
    public $path;

    public function __construct($expected, $found, $line, $column)
    {
        $this->column = $column;
        $this->expected = $expected;
        $this->found = $found;
        $this->documentLine = $line;
        $this->path = Parser::$path;

        $message = "($line:$column) Expected `$expected` but found `$found`.";
        parent::__construct($message);
    }
}
