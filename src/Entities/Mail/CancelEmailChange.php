<?php namespace Tilmeld\Entities\Mail;

// phpcs:disable Generic.Files.LineLength.TooLong

/**
 * Cancel Email Change
 *
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @see http://tilmeld.org/
 */
class CancelEmailChange extends \uMailPHP\Definition {
  public static $cname = 'Cancel Email Change';
  public static $description = 'This email is sent to a user\'s old email when they change their email to let them cancel their change.';
  public static $expectsRecipient = true;
  public static $macros = [
    'cancel_link' => 'The URL to cancel the change, to be used in a link.',
    'old_email' => 'The old email address.',
    'new_email' => 'The new email address.',
    'to_phone' => 'The recipient\'s phone number.',
    'to_timezone' => 'The recipient\'s timezone.',
  ];

  public static function getMacro($name) {
  }

  public static function getSubject() {
    return 'Hey #to_first_name#, your email address has been changed on #site_name#.';
  }

  public static function getHTML() {
    return file_get_contents(__DIR__.'/html/CancelEmailChange.html');
  }
}
