<?php
/**
 * Save changes to a user.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $_ core */
defined('P_RUN') or die('Direct access prohibited');


if (Tilmeld::$config['email_usernames'] || in_array('email', Tilmeld::$config['user_fields'])) {
	// Only send an email if they don't have the ability to edit all users.
	if (Tilmeld::$config['verifyEmail'] && !gatekeeper('com_user/edituser')) {
		if (isset($user->guid) && $user->email != $_REQUEST['email']) {
			if (Tilmeld::$config['email_rate_limit'] !== '' && isset($user->emailChangeDate) && $user->emailChangeDate > strtotime('-'.Tilmeld::$config['email_rate_limit']))
				pines_notice('You already changed your email address recently. Please wait until '.format_date(strtotime('+'.Tilmeld::$config['email_rate_limit'], $user->emailChangeDate), 'full_short').' to change your email address again.');
			else {
				if (isset($user->secret)) {
					// The user hasn't verified their previous email, so just update it.
					$user->email = $_REQUEST['email'];
					// This will cause a new verification email to be sent when
					// the user is saved. We need to change the secret, so the
					// old link doesn't verify the new address.
					$user->secret = uniqid('', true);
				} else {
					$user->newEmailAddress = $_REQUEST['email'];
					$user->newEmailSecret = uniqid('', true);
					// Save the old email in case the verification link is clicked.
					$user->cancelEmailAddress = $user->email;
					$user->cancelEmailSecret = uniqid('', true);
					$user->emailChangeDate = time();
					$verifyEmail = true;
				}
			}
		}
	} else
		$user->email = $_REQUEST['email'];
	if (isset($user->secret) && gatekeeper('com_user/edituser') && $_REQUEST['email_verified'] == 'ON') {
		if (Tilmeld::$config['unverified_access'])
			$user->groups = (array) \Nymph\Nymph::getEntities(array('class' => '\Tilmeld\Group', 'skip_ac' => true), array('&', 'data' => array('defaultSecondary', true)));
		$user->enable();
		unset($user->secret);
	}
	if ($user->email && $_REQUEST['mailingList'] != 'ON' && !$_->com_mailer->unsubscribe_query($user->email)) {
		if (!$_->com_mailer->unsubscribe_add($user->email))
			pines_error('Your email could not be removed from the mailing list. Please try again, and if the problem persists, contact an administrator.');
	} elseif ($user->email && $_REQUEST['mailingList'] == 'ON' && $_->com_mailer->unsubscribe_query($user->email)) {
		if (!$_->com_mailer->unsubscribe_remove($user->email))
			pines_error('Your email could not be added to the mailing list. Please try again, and if the problem persists, contact an administrator.');
	}
}
if (in_array('phone', Tilmeld::$config['user_fields']))
	$user->phone = preg_replace('/\D/', '', $_REQUEST['phone']);
if (in_array('fax', Tilmeld::$config['user_fields']))
	$user->fax = preg_replace('/\D/', '', $_REQUEST['fax']);
if (Tilmeld::$config['referral_codes'])
	$user->referral_code = $_REQUEST['referral_code'];
if (in_array('timezone', Tilmeld::$config['user_fields']))
	$user->timezone = $_REQUEST['timezone'];

// Location
if (in_array('address', Tilmeld::$config['user_fields'])) {
	$user->addressType = $_REQUEST['addressType'];
	$user->addressStreet = $_REQUEST['addressStreet'];
	$user->addressStreet2 = $_REQUEST['addressStreet2'];
	$user->addressCity = $_REQUEST['addressCity'];
	$user->addressState = $_REQUEST['addressState'];
	$user->addressZip = $_REQUEST['addressZip'];
	$user->addressInternational = $_REQUEST['addressInternational'];
}

// Go through a list of all groups, and assign them if they're selected.
// Groups that the user does not have access to will not be received from the
// entity manager after com_user filters the result, and thus will not be
// assigned.
if ( gatekeeper('com_user/assigngroup') ) {
	$highest_primary_parent = Tilmeld::$config['highest_primary'];
	$primary_groups = array();
	if ($highest_primary_parent == 0) {
		$primary_groups = \Nymph\Nymph::getEntities(array('class' => '\Tilmeld\Group'));
	} else {
		if ($highest_primary_parent > 0) {
			$highest_primary_parent = Group::factory($highest_primary_parent);
			if (isset($highest_primary_parent->guid))
				$primary_groups = $highest_primary_parent->getDescendants();
		}
	}
	$group = Group::factory((int) $_REQUEST['group']);
	foreach ($primary_groups as $cur_group) {
		if ($cur_group->is($group)) {
			$user->group = $group;
			break;
		}
	}
	// What if the user can't assign the current primary group, so it defaults to null?
	//if ($_REQUEST['group'] == 'null' || $_REQUEST['no_primary_group'] == 'ON' )
	if ($_REQUEST['group'] == 'null')
		unset($user->group);

	if (!(gatekeeper('com_user/edituser') && $_REQUEST['email_verified'] == 'ON' && Tilmeld::$config['unverified_access'])) {
		$highest_secondary_parent = Tilmeld::$config['highest_secondary'];
		$secondary_groups = array();
		if ($highest_secondary_parent == 0) {
			$secondary_groups = \Nymph\Nymph::getEntities(array('class' => '\Tilmeld\Group'));
		} else {
			if ($highest_secondary_parent > 0) {
				$highest_secondary_parent = Group::factory($highest_secondary_parent);
				if (isset($highest_secondary_parent->guid))
					$secondary_groups = $highest_secondary_parent->getDescendants();
			}
		}
		$groups = array_map('intval', (array) $_REQUEST['groups']);
		foreach ($secondary_groups as $cur_group) {
			if (in_array($cur_group->guid, $groups))
				$user->add_group($cur_group);
			else
				$user->del_group($cur_group);
		}
	}
}

if ( gatekeeper('com_user/abilities') ) {
	$user->inheritAbilities = ($_REQUEST['inheritAbilities'] == 'ON');
	$sections = array('system');
	foreach ($_->components as $cur_component)
		$sections[] = $cur_component;
	foreach ($sections as $cur_section) {
		if ($cur_section == 'system')
			$section_abilities = (array) $_->info->abilities;
		else
			$section_abilities = (array) $_->info->$cur_section->abilities;
		foreach ($section_abilities as $cur_ability) {
			if ( isset($_REQUEST[$cur_section]) && (array_search($cur_ability[0], $_REQUEST[$cur_section]) !== false) )
				$user->grant($cur_section.'/'.$cur_ability[0]);
			else
				$user->revoke($cur_section.'/'.$cur_ability[0]);
		}
	}
}


$un_check = Tilmeld::check_username($user->username, $user->guid);
if (!$un_check['result']) {
	$user->print_form();
	pines_notice($un_check['message']);
	return;
}
if (in_array('email', Tilmeld::$config['user_fields'])) {
	$test = \Nymph\Nymph::getEntity(
			array('class' => '\Tilmeld\User', 'skip_ac' => true),
			array('&',
				'match' => array('email', '/^'.preg_quote($user->email, '/').'$/i'),
				'!guid' => $user->guid
			)
		);
	if (isset($test)) {
		$user->print_form();
		pines_notice('There is already a user with that email address. Please use a different email.');
		return;
	}
}
if (empty($user->password) && !Tilmeld::$config['pw_empty']) {
	$user->print_form();
	pines_notice('Please specify a password.');
	return;
}
if ($user->save()) {
	pines_notice('Saved user ['.$user->username.']');
	pines_log('Saved user ['.$user->username.']');
	if (Tilmeld::$config['verifyEmail'] && $verifyEmail) {
		// Send the verification email.
		$link = h(pines_url('com_user', 'verifyuser', array('id' => $user->guid, 'type' => 'change', 'secret' => $user->newEmailSecret), true));
		$link2 = h(pines_url('com_user', 'verifyuser', array('id' => $user->guid, 'type' => 'cancelchange', 'secret' => $user->cancelEmailSecret), true));
		$macros = array(
			'old_email' => h($user->email),
			'new_email' => h($user->newEmailAddress),
			'to_phone' => h(format_phone($user->phone)),
			'to_fax' => h(format_phone($user->fax)),
			'to_timezone' => h($user->timezone),
			'to_address' => $user->addressType == 'us' ? h("{$user->addressStreet} {$user->addressStreet2}").'<br />'.h("{$user->addressCity}, {$user->addressState} {$user->addressZip}") : '<pre>'.h($user->addressInternational).'</pre>'
		);
		$macros2 = $macros;
		$macros['verify_link'] = $link;
		$macros2['cancel_link'] = $link2;
		// Two emails, first goes to the new address for verification.
		// Second goes to the old email address to cancel the change.
		$recipient = (object) array(
			'email' => $user->newEmailAddress,
			'username' => $user->username,
			'name' => $user->name,
			'nameFirst' => $user->nameFirst,
			'nameLast' => $user->nameLast,
		);
		if ($_->com_mailer->send_mail('com_user/verifyEmail_change', $macros, $recipient) && $_->com_mailer->send_mail('com_user/cancel_email_change', $macros2, $user))
			pines_notice('A verification link has been sent to your new email address. Please click the link provided to verify your new address.');
		else
			pines_error('Couldn\'t send verification email.');
	}
} else
	pines_error('Error saving user. Do you have permission?');

if (gatekeeper('com_user/listusers')) {
	if ($user->hasTag('enabled'))
		pines_redirect(pines_url('com_user', 'listusers'));
	else
		pines_redirect(pines_url('com_user', 'listusers', array('enabled' => 'false')));
} else
	pines_redirect(pines_url());