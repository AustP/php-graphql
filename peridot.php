<?php

require('vendor/autoload.php');

use GraphQL\Server;
use Peridot\Plugin\GherkinPlugin;

Server::$useCache = false;

return function ($emitter) {
    new GherkinPlugin($emitter);
};
