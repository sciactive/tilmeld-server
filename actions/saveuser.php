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
	if ($user->email && $_REQUEST['mailingList'] != 'ON' && !$_->com_mailer->unsubscribe_query($user->email)) {
		if (!$_->com_mailer->unsubscribe_add($user->email))
			pines_error('Your email could not be removed from the mailing list. Please try again, and if the problem persists, contact an administrator.');
	} elseif ($user->email && $_REQUEST['mailingList'] == 'ON' && $_->com_mailer->unsubscribe_query($user->email)) {
		if (!$_->com_mailer->unsubscribe_remove($user->email))
			pines_error('Your email could not be added to the mailing list. Please try again, and if the problem persists, contact an administrator.');
	}
}

