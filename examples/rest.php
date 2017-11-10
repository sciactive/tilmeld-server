<?php

error_reporting(E_ALL);

date_default_timezone_set('America/Los_Angeles');

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/autoload.php';
require __DIR__.'/config.php';

$NymphREST = new \Nymph\REST();

try {
  if (in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'DELETE'])) {
    parse_str(file_get_contents("php://input"), $args);
    $NymphREST->run($_SERVER['REQUEST_METHOD'], $args['action'], $args['data']);
  } else {
    $NymphREST->run($_SERVER['REQUEST_METHOD'], $_REQUEST['action'], $_REQUEST['data']);
  }
} catch (\Nymph\Exceptions\QueryFailedException $e) {
  echo $e->getMessage()."\n\n".$e->getQuery();
}
