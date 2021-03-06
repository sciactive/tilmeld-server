<?php namespace Tilmeld\Entities\Mail;

// phpcs:disable Generic.Files.LineLength.TooLong

/**
 * New User Registered
 *
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @see http://tilmeld.org/
 */
class UserRegistered extends \uMailPHP\Definition {
  public static $cname = 'New User Registered';
  public static $description = 'This email is sent when a new user registers himself on the site.';
  public static $expectsRecipient = false;
  public static $macros = [
    'user_username' => 'The user\'s username.',
    'user_name' => 'The user\'s full name.',
    'user_first_name' => 'The user\'s first name.',
    'user_last_name' => 'The user\'s last name.',
    'user_email' => 'The user\'s email.',
    'user_phone' => 'The user\'s phone number.',
    'user_timezone' => 'The user\'s timezone.',
  ];

  public static function getMacro($name) {
  }

  public static function getSubject() {
    return '#to_first_name#, New user [#user_username#] registered on #site_name#.';
  }

  public static function getHTML() {
    return file_get_contents(__DIR__.'/html/UserRegistered.html');
  }
}
