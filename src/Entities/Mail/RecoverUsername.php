<?php namespace Tilmeld\Entities\Mail;

// phpcs:disable Generic.Files.LineLength.TooLong

/**
 * Recover Account Username
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://tilmeld.org/
 */
class RecoverUsername extends \uMailPHP\Definition {
  public static $cname = 'Recover Username';
  public static $description = 'This email is sent when a user can\'t access their account so they can recover their username.';
  public static $expectsRecipient = true;
  public static $macros = [
    'to_phone' => 'The recipient\'s phone number.',
    'to_fax' => 'The recipient\'s fax number.',
    'to_timezone' => 'The recipient\'s timezone.',
    'to_address' => 'The recipient\'s address.',
  ];

  public static function getMacro($name) {
  }

  public static function getSubject() {
    return 'Hey #to_first_name#, here\'s your username for #site_name#.';
  }

  public static function getHTML() {
    return file_get_contents(__DIR__.'/html/RecoverUsername.html');
  }
}
