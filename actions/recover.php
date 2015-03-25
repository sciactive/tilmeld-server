<?php
/**
 * Print the account recovery form.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $_ core */
defined('P_RUN') or die('Direct access prohibited');

if (!Tilmeld::$config->pw_recovery['value'])
	throw new HttpClientException(null, 404);

if (empty($_REQUEST['type'])) {
	$module = new module('com_user', 'recover', 'content');
	return;
}

switch ($_REQUEST['type']) {
	case 'password':
	default:
		$user = user::factory($_REQUEST['account']);
		break;
	case 'username':
		$user = $_->nymph->getEntity(
				array('class' => user),
				array('&',
					'tag' => array('com_user', 'user', 'enabled'),
					'strict' => array('email', $_REQUEST['account'])
				)
			);
		break;
}

if (!isset($user) || !isset($user->guid) || !$user->hasTag('enabled') || !gatekeeper('com_user/login', $user)) {
	pines_error('Requested user id is not accessible.');
	Tilmeld::print_login();
	return;
}

// Create a unique secret.
$user->secret = uniqid('', true);
$user->secret_time = time();
if (!$user->save()) {
	pines_error('Couldn\'t save user secret.');
	return;
}

// Send the recovery email.
$link = h(pines_url('com_user', 'recoverpassword', array('id' => $user->guid, 'secret' => $user->secret), true));
$macros = array(
	'recover_link' => $link,
	'minutes' => h(Tilmeld::$config->pw_recovery_minutes['value']),
	'to_phone' => h(format_phone($user->phone)),
	'to_fax' => h(format_phone($user->fax)),
	'to_timezone' => h($user->timezone),
	'to_address' => $user->address_type == 'us' ? h("{$user->address_1} {$user->address_2}").'<br />'.h("{$user->city}, {$user->state} {$user->zip}") : '<pre>'.h($user->address_international).'</pre>'
);
if ($_->com_mailer->send_mail('com_user/recover_account', $macros, $user))
	pines_notice('We have sent an email to your registered email address. Please check your email to continue with account recovery.');
else
	pines_error('Couldn\'t send recovery email.');

pines_redirect(pines_url());