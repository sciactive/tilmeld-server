<?php namespace Tilmeld\Entities\Mail;

// phpcs:disable Generic.Files.LineLength.TooLong

/**
 * Verify Email Change
 *
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @see http://tilmeld.org/
 */
class VerifyEmailChange extends \uMailPHP\Definition {
  public static $cname = 'Verify Email Change';
  public static $description = 'This email is sent to a user\'s new email when they change their email to let them verify their new address.';
  public static $expectsRecipient = true;
  public static $macros = [
    'verify_link' => 'The URL to verify the email address, to be used in a link.',
    'old_email' => 'The old email address.',
    'new_email' => 'The new email address.',
    'to_phone' => 'The recipient\'s phone number.',
    'to_timezone' => 'The recipient\'s timezone.',
  ];

  public static function getMacro($name) {
  }

  public static function getSubject() {
    return 'Hey #to_first_name#, please verify your new email address for #site_name#.';
  }

  public static function getHTML() {
    return file_get_contents(__DIR__.'/html/VerifyEmailChange.html');
  }
}
