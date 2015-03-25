<?php
require 'vendor/autoload.php';
use Nymph\Nymph as Nymph;
\SciActive\RequirePHP::_('NymphConfig', [], function(){
	$nymph_config = include(__DIR__.DIRECTORY_SEPARATOR.'vendor/sciactive/nymph/conf/defaults.php');
	$nymph_config->MySQL->host['value'] = '127.0.0.1';
	$nymph_config->MySQL->database['value'] = 'nymph_test';
	$nymph_config->MySQL->user['value'] = 'nymph_test';
	$nymph_config->MySQL->password['value'] = 'omgomg';
	return $nymph_config;
});

echo Nymph::getUID('test');
echo Nymph::newUID('test');
echo Nymph::newUID('test');
echo Nymph::newUID('test');
