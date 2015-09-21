<?php
/**
 * Recover a user's password.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/lgpl.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $_ core */
defined('P_RUN') or die('Direct access prohibited');

if (!Tilmeld::$config['pw_recovery']) {
	throw new HttpClientException(null, 404);
}

$user = User::factory((int) $_REQUEST['id']);

if (!isset($user->guid)) {
	pines_notice('The specified user id is not available.');
	Tilmeld::print_login();
	return;
}

if (!isset($user->recoverSecret) || $_REQUEST['secret'] != $user->recoverSecret || strtotime('+'.Tilmeld::$config['pw_recovery_minutes'].' minutes', $user->recoverSecretTime) < time() ) {
	pines_notice('The secret code given does not match this user.');
	Tilmeld::print_login();
	return;
}

if ($_REQUEST['form'] != 'true') {
	$module = new module('com_user', 'recover_password', 'content');
	$module->entity = $user;
	$module->recoverSecret = $_REQUEST['secret'];
	return;
}

if (empty($_REQUEST['password'])) {
	pines_notice('Password cannot be empty');
	Tilmeld::print_login();
	return;
}

$user->password($_REQUEST['password']);
unset($user->recoverSecret);
unset($user->recoverSecretTime);
if ($user->save()) {
	pines_notice('Your password has been reset. You can now login using your new password.');
} else {
	pines_error('Error saving new password.');
}

Tilmeld::print_login();