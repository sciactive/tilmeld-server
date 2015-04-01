<?php namespace Tilmeld\Mail;
/**
 * RecoverAccount class.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

/**
 * Recover Account
 *
 * @package Tilmeld
 */
class RecoverAccount extends \µMailPHP\Definition {
	public static $cname = 'Recover Account';
	public static $description = 'This email is sent when a user can\'t access their account so they can recover their username and/or password.';
	public static $expectsRecipient = true;
	public static $unsubscribe = false;
	public static $macros = [
		'recover_link' => 'The URL to change their password, to be used in a link.',
		'minutes' => 'How many minutes a recovery request is valid.',
		'to_phone' => 'The recipient\'s phone number.',
		'to_fax' => 'The recipient\'s fax number.',
		'to_timezone' => 'The recipient\'s timezone.',
		'to_address' => 'The recipient\'s address.',
	];

	public static function getMacro($name) {}

	public static function getSubject() {
		return 'Hey #to_first_name#, here\'s the account recovery for #to_username# at #site_name#.';
	}

	public static function getHTML() {
		return file_get_contents(__DIR__.'/../../html/Mail/RecoverAccount.html');
	}
}
