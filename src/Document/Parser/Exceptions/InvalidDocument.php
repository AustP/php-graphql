<?php

namespace GraphQL\Document\Parser\Exceptions;

use GraphQL\Document\Exceptions\ServerError;
use GraphQL\Document\Parser;

class InvalidDocument extends ServerError
{
    public $column;
    public $line;
    public $path;

    public function __construct($message, $line = null, $column = null)
    {
        if ($line === null && $column === null) {
            return parent::__construct($message);
        }

        $this->column = $column;
        $this->documentLine = $line;
        $this->path = Parser::$path;

        $message = "($line:$column) " . $message;
        parent::__construct($message);
    }
}
