<?php namespace Tilmeld\Mail;
/**
 * VerifyEmail class.
 *
 * @package Tilmeld
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

/**
 * Verify Email
 *
 * @package Tilmeld
 */
class VerifyEmail extends \uMailPHP\Definition {
  public static $cname = 'Verify Email';
  public static $description = 'This email is sent to a new user to let them verify their address.';
  public static $expectsRecipient = true;
  public static $macros = [
    'verify_link' => 'The URL to verify the email address, to be used in a link.',
    'to_phone' => 'The recipient\'s phone number.',
    'to_timezone' => 'The recipient\'s timezone.',
    'to_address' => 'The recipient\'s address.',
  ];

  public static function getMacro($name) {}

  public static function getSubject() {
    return 'Hi #to_first_name#, please verify your email at #site_name#.';
  }

  public static function getHTML() {
    return file_get_contents(__DIR__.'/../../html/Mail/VerifyEmail.html');
  }
}
