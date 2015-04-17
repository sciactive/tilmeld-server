<?php

error_reporting(E_ALL);

include dirname(__DIR__).'/vendor/autoload.php';
include dirname(__DIR__).'/src/autoload.php';

// µMailPHP's config.
\SciActive\RequirePHP::_('µMailPHPConfig', [], function(){
	$config = include('../conf/defaults.php');
	$config->site_name['value'] = 'µMailPHP Example Site';
	$config->site_link['value'] = 'http://localhost/umailphp/';
	$config->unsubscribe_url['value'] = 'http://localhost/umailphp/examples/unsubscribe.php';
	$config->master_address['value'] = 'hperrin@gmail.com';
	$config->testing_mode['value'] = true;
	$config->testing_email['value'] = 'hperrin@gmail.com';
	return $config;
});

// This is how you enter the setup app.
// (only for umail) include 'UserVerifyMail.php'; // Make sure all of your definition classes are loaded.
$baseURL = '../'; // This is the URL of the Tilmeld root.
$sciactiveBaseURL = '../bower_components/'; // This is the URL of the SciActive libraries.
$restEndpoint = 'rest.php'; // This is the URL of the Nymph endpoint.
include '../src/setup.php'; // And this will load the Tilmeld setup app.