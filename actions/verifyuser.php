<?php
/**
 * Verify a newly registered user's e-mail address.
 *
 * @package Components\user
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $_ core */
defined('P_RUN') or die('Direct access prohibited');

$user = User::factory((int) $_REQUEST['id']);

if (!isset($user->guid)) {
  pines_notice('The specified user id is not available.');
  Tilmeld::print_login();
  return;
}

switch ($_REQUEST['type']) {
  case 'register':
  default:
    // Verify new user's email address.
    if (!isset($user->secret) || $_REQUEST['secret'] != $user->secret) {
      $module = new module('com_user', 'note_bad_verification', 'content');
      return;
    }

    if (Tilmeld::$config['unverified_access'])
      $user->groups = (array) \Nymph\Nymph::getEntities(
          ['class' => '\Tilmeld\Entities\Group', 'skip_ac' => true],
          ['&',
            'data' => ['defaultSecondary', true]
          ]
      );
    $user->enable();
    unset($user->secret);
    break;
  case 'change':
    // Email address change.
    if (!isset($user->newEmailSecret) || $_REQUEST['secret'] != $user->newEmailSecret)
      punt_user('The secret code given does not match this user.');

    if (Tilmeld::$config['email_usernames']) {
      $un_check = Tilmeld::check_username($user->newEmailAddress, $user->guid);
      if (!$un_check['result']) {
        $user->print_form();
        pines_notice($un_check['message']);
        return;
      }
    }
    $test = \Nymph\Nymph::getEntity(
        ['class' => '\Tilmeld\Entities\User', 'skip_ac' => true],
        ['&',
          'match' => ['email', '/^'.preg_quote($user->newEmailAddress, '/').'$/i'],
          '!guid' => $user->guid
        ]
    );
    if (isset($test)) {
      $user->print_form();
      pines_notice('There is already a user with that email address. Please use a different email.');
      return;
    }

    $user->email = $user->newEmailAddress;
    unset($user->newEmailAddress, $user->newEmailSecret);
    break;
  case 'cancelchange':
    // Cancel an email address change.
    if (!isset($user->cancelEmailSecret) || $_REQUEST['secret'] != $user->cancelEmailSecret)
      punt_user('The secret code given does not match this user.');

    $user->email = $user->cancelEmailAddress;
    unset($user->newEmailAddress, $user->newEmailSecret, $user->cancelEmailAddress, $user->cancelEmailSecret);
    break;
}

if ($user->save()) {
  switch ($_REQUEST['type']) {
    case 'register':
    default:
      pines_log('Validated user ['.$user->username.']');
      Tilmeld::login($user);
      $notice = new module('com_user', 'note_welcome', 'content');
      if (!empty($_REQUEST['url'])) {
        pines_notice('Thank you. Your account has been verified.');
        pines_redirect(urldecode($_REQUEST['url']));
        return;
      }
      break;
    case 'change':
      pines_notice('Thank you. Your new email address has been verified.');
      pines_redirect(pines_url());
      break;
    case 'cancelchange':
      pines_notice('The email address change has been canceled.');
      pines_redirect(pines_url());
      break;
  }
} else {
  pines_error('Error saving user.');
}
