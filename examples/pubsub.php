<?php

error_reporting(E_ALL);

if (php_sapi_name() != "cli") {
  die("You can only run pubsub.php from the command line.");
}

date_default_timezone_set('America/Los_Angeles');

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/autoload.php';
require __DIR__.'/config.php';

$config = [];
// If we're on production, bind to the given port.
if (getenv('NYMPH_PRODUCTION') && getenv('PORT')) {
  $config['port'] = (int) getenv('PORT');
} else {
  $config['port'] = 8081;
}

$opts = getopt('p:e:r:');
// This lets us load multiple nymph-pubsub servers.
if (isset($opts['p'])) {
  $config['port'] = (int) $opts['p'];
}
if (isset($opts['e'])) {
  $config['entries'] = [];
  foreach (explode(',', $opts['e']) as $port) {
    $config['entries'][] =
        ($port == '443' ? 'wss' : 'ws') . "://127.0.0.1:{$port}/";
  }
}
if (isset($opts['r'])) {
  $config['relays'] = [];
  foreach (explode(',', $opts['r']) as $port) {
    $config['relays'][] =
        ($port == '443' ? 'wss' : 'ws') . "://127.0.0.1:{$port}/";
  }
}

\Nymph\Nymph::connect();

if (in_array('-d', $argv)) {
  function shutdown() {
    posix_kill(posix_getpid(), SIGHUP);
  }

  // Switch over to daemon mode.
  if ($pid = pcntl_fork()) {
    return;
  }

  register_shutdown_function('shutdown');
} else {
  error_reporting(E_ALL);
}

$server = new \Nymph\PubSub\Server($config);
$server->run();
