<?php

// Nymph's configuration.
$nymphConfig = [];
// Check for production postgres var.
if (getenv('NYMPH_PRODUCTION')) {
  $dbopts = parse_url(getenv('DATABASE_URL'));
  $nymphConfig['driver'] = 'PostgreSQL';
  $nymphConfig['PostgreSQL'] = [
    'database' => ltrim($dbopts["path"], '/'),
    'host' => $dbopts["host"],
    'port' => $dbopts["port"],
    'user' => $dbopts["user"],
    'password' => $dbopts["pass"]
  ];
} elseif (getenv('MYSQL_HOST')) {
  $nymphConfig['MySQL'] = [
    'host' => getenv('MYSQL_HOST'),
    'database' => getenv('MYSQL_DATABASE'),
    'user' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD')
  ];
} else {
  if (getenv('DB') === 'pgsql') {
    $nymphConfig['driver'] = 'PostgreSQL';
    $nymphConfig['PostgreSQL'] = [
      'host' => '127.0.0.1',
      'database' => 'nymph_example',
      'user' => 'nymph_example',
      'password' => 'omgomg'
    ];
  } else {
    $nymphConfig['MySQL'] = [
      'host' => '127.0.0.1',
      'database' => 'nymph_example',
      'user' => 'nymph_example',
      'password' => 'omgomg'
    ];
  }
}
\Nymph\Nymph::configure($nymphConfig);

// Nymph PubSub's configuration.
if (getenv('NYMPH_PRODUCTION')) {
  // If we're on Heroku, the entry is the pubsub demo.
  $entry = 'wss://nymph-pubsub-demo.herokuapp.com:443/';
} elseif (getenv('PUBSUB_HOST')) {
  // If we're in Docker, the entry is provided by Docker.
  $entry = 'ws://'.getenv('PUBSUB_HOST').'/';
} else {
  // If we're not in either, it's probaby on the same host.
  $entry = 'ws://localhost:8081/';
}
\Nymph\PubSub\Server::configure(['entries' => [$entry]]);


// uMailPHP's configuration.
\uMailPHP\Mail::configure([
  'site_name' => 'Tilmeld Example Site',
  'site_link' => 'http://localhost/tilmeld/',
  'master_address' => 'someone@example.com',
  'testing_mode' => true,
  'testing_email' => 'hperrin@localhost',
]);


// Tilmeld's configuration.
\Tilmeld\Tilmeld::configure([
  'email_usernames' => true
]);
