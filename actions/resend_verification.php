<?php
/**
 * Resend a verification email.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $_ core */
defined('P_RUN') or die('Direct access prohibited');

if (!Tilmeld::$config->verify_email['value'] || !gatekeeper() || !isset($_SESSION['user']->secret)) {
	$_->page->ajax('false');
	return;
}

// Send the verification email.
if ($_SESSION['user']->send_email_verification())
	$_->page->ajax('true');
else
	$_->page->ajax('false');