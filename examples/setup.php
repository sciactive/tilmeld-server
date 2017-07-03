<?php

error_reporting(E_ALL);

date_default_timezone_set('America/Los_Angeles');

include __DIR__.'/../vendor/autoload.php';
include __DIR__.'/../src/autoload.php';

\Tilmeld\Tilmeld::configure();

// This is how you enter the setup app.
// (only for umail) include 'UserVerifyMail.php'; // Make sure all of your definition classes are loaded.
$tilmeldURL = '../'; // This is the URL of the Tilmeld root.
$sciactiveBaseURL = '../node_modules/'; // This is the URL of the SciActive libraries.
$restEndpoint = 'rest.php'; // This is the URL of the Nymph endpoint.
include '../src/setup.php'; // And this will load the Tilmeld setup app.
