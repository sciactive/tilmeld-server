<?php namespace Tilmeld;
/**
 * MailVerifyEmailChange class.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

/**
 * Verify Email Change
 *
 * @package Tilmeld
 */
class MailVerifyEmailChange extends \µMailPHP\Definition {
	public static $cname = 'Verify Email Change';
	public static $description = 'This email is sent to a user\'s new email when they change their email to let them verify their new address.';
	public static $expectsRecipient = true;
	public static $unsubscribe = false;
	public static $macros = [
		'verify_link' => 'The URL to verify the email address, to be used in a link.',
		'old_email' => 'The old email address.',
		'new_email' => 'The new email address.',
		'to_phone' => 'The recipient\'s phone number.',
		'to_fax' => 'The recipient\'s fax number.',
		'to_timezone' => 'The recipient\'s timezone.',
		'to_address' => 'The recipient\'s address.',
	];

	public static function getMacro($name) {}

	public static function getSubject() {
		return 'Hey #to_first_name#, please verify your new email address for #site_name#.';
	}

	public static function getHTML() {
		return file_get_contents(__DIR__.'/../html/mails/verify_email_change.html');
	}
}
