<?php
namespace Tilmeld\Entities;

use \Tilmeld\Tilmeld as Tilmeld;

/**
 * User class.
 *
 * @package Tilmeld
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 * @property int $guid The GUID of the user.
 * @property string $username The user's username.
 * @property string $nameFirst The user's first name.
 * @property string $nameMiddle The user's middle name.
 * @property string $nameLast The user's last name.
 * @property string $name The user's full name.
 * @property string $email The user's email address.
 * @property string $originalEmail Used to save the current email to send verification if it changes.
 * @property string $phone The user's telephone number.
 * @property string $addressType The user's address type. "us" or "international".
 * @property string $addressStreet The user's address line 1 for US addresses.
 * @property string $addressStreet2 The user's address line 2 for US addresses.
 * @property string $addressCity The user's city for US addresses.
 * @property string $addressState The user's state abbreviation for US addresses.
 * @property string $addressZip The user's ZIP code for US addresses.
 * @property string $addressInternational The user's full address for international addresses.
 * @property \Tilmeld\Entities\Group $group The user's primary group.
 * @property array $groups The user's secondary groups.
 * @property bool $inheritAbilities Whether the user should inherit the abilities of his groups.
 * @property string $passwordTemp Temporary storage for passwords. This will be hashed before going into the database.
 */
class User extends AbleObject {
  const ETYPE = 'tilmeld_user';
  protected $tags = [];
  protected $clientEnabledMethods = [
    'checkUsername',
    'checkEmail',
    'checkPhone',
    'getAvatar',
    'register',
    'logout',
    'login',
    'recover',
  ];
  public static $clientEnabledStaticMethods = [
    'current',
    'getClientConfig',
  ];
  protected $privateData = [
    'email',
    'originalEmail',
    'phone',
    'addressType',
    'addressStreet',
    'addressStreet2',
    'addressCity',
    'addressState',
    'addressZip',
    'addressInternational',
    'abilities',
    'inheritAbilities',
    'timezone',
    'recoverSecret',
    'recoverSecretTime',
    'password',
    'passwordTemp',
    'salt',
    'secret',
    'cancelEmailAddress',
    'cancelEmailSecret',
    'emailChangeDate',
  ];
  protected $whitelistData = [];
  protected $whitelistTags = [];
  protected $clientClassName = 'User';

  /**
   * Gatekeeper ability cache.
   *
   * Gatekeeper will cache the user's abilities that it calculates, so it can
   * check faster if that user has been checked before.
   *
   * @var array
   * @access private
   */
  private $gatekeeperCache = [];

  /**
   * Load a user.
   *
   * @param int|string $id The ID or username of the user to load, 0 for a new user.
   */
  public function __construct($id = 0) {
    if ($id > 0 || (string) $id === $id) {
      if ((int) $id === $id) {
        $entity = \Nymph\Nymph::getEntity(['class' => get_class($this)], ['&', 'guid' => (int) $id]);
      } else {
        $entity = \Nymph\Nymph::getEntity(['class' => get_class($this)], ['&', 'strict' => ['username', (string) $id]]);
      }
      if (isset($entity)) {
        $this->guid = $entity->guid;
        $this->tags = $entity->tags;
        $this->putData($entity->getData(), $entity->getSData());
        if (!isset($this->secret) && (!isset($this->emailChangeDate) || $this->emailChangeDate < strtotime('-'.Tilmeld::$config['email_rate_limit']))) {
          $this->originalEmail = $this->email;
        }
        return;
      }
    }
    // Defaults.
    $this->enabled = true;
    $this->abilities = [];
    $this->groups = [];
    $this->inheritAbilities = true;
    $this->addressType = 'us';
    $this->updateDataProtection();
  }

  public static function current($returnObjectIfNotExist = false) {
    if (!isset($_SESSION['tilmeld_user'])) {
      Tilmeld::session();
    }
    if (!isset($_SESSION['tilmeld_user'])) {
      return $returnObjectIfNotExist ? self::factory() : null;
    }
    return $_SESSION['tilmeld_user'];
  }

  public function putData($data, $sdata = []) {
    $return = parent::putData($data, $sdata);
    $this->updateDataProtection();
    return $return;
  }

  public function updateDataProtection() {
    if (Tilmeld::$config['email_usernames']) {
      $this->privateData[] = 'username';
    }
    if (Tilmeld::gatekeeper('tilmeld/manage')) {
      // Users who can edit other users can see most of their data.
      $this->privateData = [
        'recoverSecret',
        'recoverSecretTime',
        'password',
        'salt'
      ];
      $this->whitelistData = false;
      return;
    }
    if (self::current() !== null && self::current(true)->is($this)) {
      // Users can check to see what abilities they have.
      $this->clientEnabledMethods[] = 'gatekeeper';

      // Users can see their own data, and edit some of it.
      $this->whitelistData[] = 'username';
      $this->whitelistData[] = 'passwordTemp';
      if (in_array('name', Tilmeld::$config['user_fields'])) {
        $this->whitelistData[] = 'nameFirst';
        $this->whitelistData[] = 'nameMiddle';
        $this->whitelistData[] = 'nameLast';
        $this->whitelistData[] = 'name';
      }
      if (in_array('email', Tilmeld::$config['user_fields'])) {
        $this->whitelistData[] = 'email';
      }
      if (in_array('phone', Tilmeld::$config['user_fields'])) {
        $this->whitelistData[] = 'phone';
      }
      if (in_array('timezone', Tilmeld::$config['user_fields'])) {
        $this->whitelistData[] = 'timezone';
      }
      if (in_array('address', Tilmeld::$config['user_fields'])) {
        $this->whitelistData[] = 'addressType';
        $this->whitelistData[] = 'addressStreet';
        $this->whitelistData[] = 'addressStreet2';
        $this->whitelistData[] = 'addressCity';
        $this->whitelistData[] = 'addressState';
        $this->whitelistData[] = 'addressZip';
        $this->whitelistData[] = 'addressInternational';
      }
      $this->privateData = [
        'originalEmail',
        'secret',
        'cancelEmailAddress',
        'cancelEmailSecret',
        'recoverSecret',
        'recoverSecretTime',
        'password',
        'salt'
      ];
    }
  }

  /**
   * Override the magic method, for email usernames.
   *
   * @param string $name The name of the variable.
   * @return mixed The value of the variable or nothing if it doesn't exist.
   */
  public function &__get($name) {
    if (Tilmeld::$config['email_usernames'] && $name == 'username') {
      if (parent::__get('email')) {
        return parent::__get('email');
      }
      return parent::__get('username');
    }
    return parent::__get($name);
  }

  /**
   * Override the magic method, for email usernames.
   *
   * @param string $name The name of the variable.
   * @return bool
   */
  public function __isset($name) {
    if (Tilmeld::$config['email_usernames'] && $name == 'username') {
      return (parent::__isset('email') || parent::__isset('username'));
    }
    return parent::__isset($name);
  }

  /**
   * Override the magic method, for email usernames.
   *
   * @param string $name The name of the variable.
   * @param string $value The value of the variable.
   * @return mixed The value of the variable.
   */
  public function __set($name, $value) {
    if (Tilmeld::$config['email_usernames'] && ($name == 'username' || $name == 'email')) {
      parent::__set('username', $value);
      return parent::__set('email', $value);
    }
    return parent::__set($name, $value);
  }

  /**
   * Override the magic method, for email usernames.
   *
   * @param string $name The name of the variable.
   */
  public function __unset($name) {
    if (Tilmeld::$config['email_usernames'] && ($name == 'username' || $name == 'email')) {
      parent::__unset('username');
      return parent::__unset('email');
    }
    return parent::__unset($name);
  }

  public function getAvatar() {
    $proto = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    if (!isset($this->email) || empty($this->email)) {
      return $proto.'://secure.gravatar.com/avatar/?d=mm&s=40';
    }
    return $proto.'://secure.gravatar.com/avatar/'.md5(strtolower(trim($this->email))).'?d=identicon&s=40';
  }

  public function delete() {
    if (!self::current(true)->gatekeeper('tilmeld/manage')) {
      return false;
    }
    return parent::delete();
  }

  public function save() {
    if (!isset($this->username)) {
      return false;
    }

    // Formatting.
    $this->username = trim($this->username);
    $this->email = trim($this->email);
    $this->nameFirst = trim($this->nameFirst);
    $this->nameMiddle = trim($this->nameMiddle);
    $this->nameLast = trim($this->nameLast);
    $this->phone = preg_replace('/\D/', '', $this->phone);
    $this->name = $this->nameFirst.(!empty($this->nameMiddle) ? ' '.$this->nameMiddle : '').(!empty($this->nameLast) ? ' '.$this->nameLast : '');

    // Verification.
    $unCheck = $this->checkUsername();
    if (!$unCheck['result']) {
      throw new Exceptions\BadUsernameException($unCheck['message']);
    }
    if (!Tilmeld::$config['email_usernames']) {
      $emCheck = $this->checkEmail();
      if (!$emCheck['result']) {
        throw new Exceptions\BadEmailException($emCheck['message']);
      }
    }

    // Email changes.
    if (!Tilmeld::gatekeeper('tilmeld/admin')) {
      // The user isn't an admin, so email address changes should contain
      // some security measures.
      if (Tilmeld::$config['verify_email']) {
        // The user needs to verify this new email address.
        if (!isset($this->guid)) {
          $this->secret = uniqid('', true);
          $sendVerification = true;
        } elseif ($this->email !== $this->originalEmail) {
          // The user already has an old email address.
          if (Tilmeld::$config['email_rate_limit'] !== '' && isset($this->emailChangeDate) && $this->emailChangeDate > strtotime('-'.Tilmeld::$config['email_rate_limit'])) {
            throw new Exceptions\EmailChangeRateLimitExceededException('You already changed your email address recently. Please wait until '.\uMailPHP\Mail::formatDate(strtotime('+'.Tilmeld::$config['email_rate_limit'], $this->emailChangeDate), 'full_short').' to change your email address again.');
            //$this->email = $this->originalEmail;
          } else {
            if (!isset($this->secret) &&
                (
                  // Make sure the user has at least the rate
                  // limit time to cancel an email change.
                  !isset($this->emailChangeDate) ||
                  $this->emailChangeDate < strtotime('-'.Tilmeld::$config['email_rate_limit'])
                )
              ) {
              // Save the old email in case the cancel change
              // link is clicked.
              $this->cancelEmailAddress = $this->originalEmail;
              $this->cancelEmailSecret = uniqid('', true);
              $this->emailChangeDate = time();
            }
            $this->secret = uniqid('', true);
            $sendVerification = true;
          }
        }
      } elseif (isset($this->guid) &&
          !empty($this->originalEmail) &&
          $this->originalEmail !== $this->email &&
          (
            // Make sure the user has at least the rate limit time
            // to cancel an email change.
            !isset($this->emailChangeDate) ||
            $this->emailChangeDate < strtotime('-'.Tilmeld::$config['email_rate_limit'])
          )
        ) {
        // The user doesn't need to verify their new email address, but
        // should be able to cancel the email change from their old
        // address.
        $this->cancelEmailAddress = $this->originalEmail;
        $this->cancelEmailSecret = uniqid('', true);
        $sendVerification = true;
      }
    }

    if (!isset($this->password) && !isset($this->passwordTemp)) {
      throw new Exceptions\BadDataException('A password is required.');
    }

    if (isset($this->passwordTemp) && $this->passwordTemp !== '') {
      $this->password($this->passwordTemp);
      unset($this->passwordTemp);
    }

    $return = parent::save();
    if ($return && $sendVerification) {
      // The email has changed, so send a new verification email.
      $this->sendEmailVerification();
    }
    return $return;
  }

  /**
   * Check to see if a user has an ability.
   *
   * This function will check both user and group abilities, if the user is
   * marked to inherit the abilities of its group.
   *
   * If $ability is null, it will check to see if the user is currently logged
   * in.
   *
   * If the user has the "system/admin" ability, this function will return true.
   *
   * @param string $ability The ability.
   * @return bool True or false.
   */
  public function gatekeeper($ability = null) {
    if (!isset($ability)) {
      return self::current(true)->is($this);
    }
    // Check the cache to see if we've already checked this user.
    if ($this->gatekeeperCache) {
      $abilities =& $this->gatekeeperCache;
    } else {
      $abilities = $this->abilities;
      if ($this->inheritAbilities) {
        foreach ($this->groups as &$cur_group) {
          if (!isset($cur_group->guid)) {
            continue;
          }
          $abilities = array_merge($abilities, $cur_group->abilities);
        }
        unset($cur_group);
        if (isset($this->group)) {
          $abilities = array_merge($abilities, $this->group->abilities);
        }
      }
      $this->gatekeeperCache = $abilities;
    }
    if ((array) $abilities !== $abilities) {
      return false;
    }
    return (in_array($ability, $abilities) || in_array('system/admin', $abilities));
  }

  public function clearCache() {
    $return = parent::clearCache();
    $this->gatekeeperCache = [];
    return $return;
  }

  /**
   * Send the user email verification/change/cancellation links.
   *
   * @return bool True on success, false on failure.
   */
  public function sendEmailVerification() {
    if (!isset($this->guid)) {
      return false;
    }
    $success = true;
    if (isset($this->secret) && !isset($this->cancelEmailSecret)) {
      $link = htmlspecialchars(Tilmeld::$config['setup_url'].(strpos(Tilmeld::$config['setup_url'], '?') ? '&' : '?').'action=verifyemail&id='.$this->guid.'&secret='.$this->secret);
      $macros = [
        'verify_link' => $link,
        'to_phone' => htmlspecialchars(\uMailPHP\Mail::formatPhone($this->phone)),
        'to_timezone' => htmlspecialchars($this->timezone),
        'to_address' => $this->addressType == 'us' ? htmlspecialchars("{$this->addressStreet} {$this->addressStreet2}").'<br />'.htmlspecialchars("{$this->addressCity}, {$this->addressState} {$this->addressZip}") : '<pre>'.htmlspecialchars($this->addressInternational).'</pre>'
      ];
      $mail = new \uMailPHP\Mail('\Tilmeld\Entities\Mail\VerifyEmail', $this, $macros);
      $success = $success && $mail->send();
    }
    if (isset($this->secret) && isset($this->cancelEmailSecret)) {
      $link = htmlspecialchars(Tilmeld::$config['setup_url'].(strpos(Tilmeld::$config['setup_url'], '?') ? '&' : '?').'action=verifyemailchange&id='.$this->guid.'&secret='.$this->secret);
      $macros = [
        'verify_link' => $link,
        'old_email' => htmlspecialchars($this->cancelEmailAddress),
        'new_email' => htmlspecialchars($this->email),
        'to_phone' => htmlspecialchars(\uMailPHP\Mail::formatPhone($this->phone)),
        'to_timezone' => htmlspecialchars($this->timezone),
        'to_address' => $this->addressType == 'us' ? htmlspecialchars("{$this->addressStreet} {$this->addressStreet2}").'<br />'.htmlspecialchars("{$this->addressCity}, {$this->addressState} {$this->addressZip}") : '<pre>'.htmlspecialchars($this->addressInternational).'</pre>'
      ];
      $mail = new \uMailPHP\Mail('\Tilmeld\Entities\Mail\VerifyEmailChange', $this, $macros);
      $success = $success && $mail->send();
    }
    if (isset($this->cancelEmailSecret)) {
      $link = htmlspecialchars(Tilmeld::$config['setup_url'].(strpos(Tilmeld::$config['setup_url'], '?') ? '&' : '?').'action=cancelemailchange&id='.$this->guid.'&secret='.$this->cancelEmailSecret);
      $macros = [
        'cancel_link' => $link,
        'old_email' => htmlspecialchars($this->cancelEmailAddress),
        'new_email' => htmlspecialchars($this->email),
        'to_phone' => htmlspecialchars(\uMailPHP\Mail::formatPhone($this->phone)),
        'to_timezone' => htmlspecialchars($this->timezone),
        'to_address' => $this->addressType == 'us' ? htmlspecialchars("{$this->addressStreet} {$this->addressStreet2}").'<br />'.htmlspecialchars("{$this->addressCity}, {$this->addressState} {$this->addressZip}") : '<pre>'.htmlspecialchars($this->addressInternational).'</pre>'
      ];
      $mail = new \uMailPHP\Mail('\Tilmeld\Entities\Mail\CancelEmailChange', $this, $macros);
      $success = $success && $mail->send();
    }
    return $success;
  }

  /**
   * Print a form to edit the user.
   *
   * @return module The form's module.
   */
  public function printForm() {
    $module = new module('com_user', 'form_user', 'content');
    $module->entity = $this;
    $module->display_username = gatekeeper('com_user/usernames');
    $module->display_enable = gatekeeper('com_user/enabling');
    $module->display_email_verified = gatekeeper('com_user/edituser');
    $module->display_password = gatekeeper('com_user/passwords');
    $module->display_pin = gatekeeper('com_user/assignpin');
    $module->display_groups = gatekeeper('com_user/assigngroup');
    $module->display_abilities = gatekeeper('com_user/abilities');
    $module->sections = ['system'];
    $highest_parent = Tilmeld::$config['highest_primary'];
    if ($highest_parent == 0) {
      $module->group_array_primary = \Nymph\Nymph::getEntities(['class' => '\Tilmeld\Entities\Group'], ['&', 'data' => ['enabled', true]]);
    } elseif ($highest_parent < 0) {
      $module->group_array_primary = [];
    } else {
      $highest_parent = Group::factory($highest_parent);
      if (!isset($highest_parent->guid)) {
        $module->group_array_primary = [];
      } else {
        $module->group_array_primary = $highest_parent->getDescendants();
      }
    }
    $highest_parent = Tilmeld::$config['highest_secondary'];
    if ($highest_parent == 0) {
      $module->group_array_secondary = \Nymph\Nymph::getEntities(['class' => '\Tilmeld\Entities\Group'], ['&', 'data' => ['enabled', true]]);
    } elseif ($highest_parent < 0) {
      $module->group_array_secondary = [];
    } else {
      $highest_parent = Group::factory($highest_parent);
      if (!isset($highest_parent->guid)) {
        $module->group_array_secondary = [];
      } else {
        $module->group_array_secondary = $highest_parent->getDescendants();
      }
    }

    return $module;
  }

  /**
   * Add the user to a (secondary) group.
   *
   * @param \Tilmeld\Entities\Group $group The group.
   * @return mixed True if the user is already in the group. The resulting array of groups if the user was not.
   */
  public function addGroup($group) {
    if (!$group->inArray((array) $this->groups)) {
      $this->groups[] = $group;
      return $this->groups;
    }
    return true;
  }

  /**
   * Check the given password against the user's.
   *
   * @param string $password The password in question.
   * @return bool True if the passwords match, otherwise false.
   */
  public function checkPassword($password) {
    if (!isset($this->salt)) {
      $pass = ($this->password == $password);
      $cur_type = 'salt';
    } elseif ($this->salt == '7d5bc9dc81c200444e53d1d10ecc420a') {
      $pass = ($this->password == md5($password.$this->salt));
      $cur_type = 'digest';
    } else {
      $pass = ($this->password == md5($password.$this->salt));
      $cur_type = 'salt';
    }
    if ($pass && $cur_type != Tilmeld::$config['pw_method']) {
      switch (Tilmeld::$config['pw_method']) {
        case 'plain':
          unset($this->salt);
          $this->password = $password;
          break;
        case 'salt':
          $this->salt = md5(rand());
          $this->password = md5($password.$this->salt);
          break;
        case 'digest':
        default:
          $this->salt = '7d5bc9dc81c200444e53d1d10ecc420a';
          $this->password = md5($password.$this->salt);
          break;
      }
      $this->save();
    }
    return $pass;
  }

  /**
   * Remove the user from a (secondary) group.
   *
   * @param \Tilmeld\Entities\Group $group The group.
   * @return mixed True if the user wasn't in the group. The resulting array of groups if the user was.
   */
  public function delGroup($group) {
    if ( $group->inArray((array) $this->groups) ) {
      foreach ((array) $this->groups as $key => $cur_group) {
        if ($group->is($cur_group)) {
          unset($this->groups[$key]);
        }
      }
      return $this->groups;
    }
    return true;
  }

  /**
   * Check whether the user is in a (primary or secondary) group.
   *
   * @param mixed $group The group, or the group's GUID.
   * @return bool True or false.
   */
  public function inGroup($group = null) {
    if (is_numeric($group)) {
      $group = Group::factory((int) $group);
    }
    if (!isset($group->guid)) {
      return false;
    }
    return ($group->inArray((array) $this->groups) || $group->is($this->group));
  }

  /**
   * Check whether the user is a descendant of a group.
   *
   * @param mixed $group The group, or the group's GUID.
   * @return bool True or false.
   */
  public function isDescendant($group = null) {
    if (is_numeric($group)) {
      $group = Group::factory((int) $group);
    }
    if (!isset($group->guid)) {
      return false;
    }
    // Check to see if the user is in a descendant group of the given group.
    if (isset($this->group->guid) && $this->group->isDescendant($group)) {
      return true;
    }
    foreach ((array) $this->groups as $cur_group) {
      if ($cur_group->isDescendant($group)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Change the user's password.
   *
   * @param string $password The new password.
   * @return string The resulting MD5 sum which is stored in the entity.
   */
  public function password($password) {
    switch (Tilmeld::$config['pw_method']) {
      case 'plain':
        unset($this->salt);
        return $this->password = $password;
      case 'salt':
        $this->salt = md5(rand());
        return $this->password = md5($password.$this->salt);
      case 'digest':
      default:
        $this->salt = '7d5bc9dc81c200444e53d1d10ecc420a';
        return $this->password = md5($password.$this->salt);
    }
  }

  /**
   * Return the user's timezone.
   *
   * First checks if the user has a timezone set, then the primary group, then
   * the secondary groups, then the system default. The first timezone found
   * is returned.
   *
   * @param bool $return_date_time_zone_object Whether to return an object of the DateTimeZone class, instead of an identifier string.
   * @return string|DateTimeZone The timezone identifier or the DateTimeZone object.
   */
  public function getTimezone($return_date_time_zone_object = false) {
    if (!empty($this->timezone)) {
      return $return_date_time_zone_object ? new DateTimeZone($this->timezone) : $this->timezone;
    }
    if (isset($this->group->guid) && !empty($this->group->timezone)) {
      return $return_date_time_zone_object ? new DateTimeZone($this->group->timezone) : $this->group->timezone;
    }
    foreach ((array) $this->groups as $cur_group) {
      if (!empty($cur_group->timezone)) {
        return $return_date_time_zone_object ? new DateTimeZone($cur_group->timezone) : $cur_group->timezone;
      }
    }
    $timezone = date_default_timezone_get();
    return $return_date_time_zone_object ? new DateTimeZone($timezone) : $timezone;
  }

  /**
   * Check that a username is valid.
   *
   * @return array An associative array with a boolean 'result' entry and a 'message' entry.
   */
  public function checkUsername() {
    if (!Tilmeld::$config['email_usernames']) {
      if (empty($this->username)) {
        return ['result' => false, 'message' => 'Please specify a username.'];
      }
      if (Tilmeld::$config['max_username_length'] > 0 && strlen($this->username) > Tilmeld::$config['max_username_length']) {
        return ['result' => false, 'message' => 'Usernames must not exceed '.Tilmeld::$config['max_username_length'].' characters.'];
      }
      if (array_diff(str_split($this->username), str_split(Tilmeld::$config['valid_chars']))) {
        return ['result' => false, 'message' => Tilmeld::$config['valid_chars_notice']];
      }
      if (!preg_match(Tilmeld::$config['valid_regex'], $this->username)) {
        return ['result' => false, 'message' => Tilmeld::$config['valid_regex_notice']];
      }
      $selector = ['&',
          'ilike' => ['username', str_replace(['%', '_'], ['\%', '\_'], $this->username)]
        ];
      if (isset($this->guid)) {
        $selector['!guid'] = $this->guid;
      }
      $test = \Nymph\Nymph::getEntity(
          ['class' => '\Tilmeld\Entities\User', 'skip_ac' => true],
          $selector
      );
      if (isset($test->guid)) {
        return ['result' => false, 'message' => 'That username is taken.'];
      }

      return ['result' => true, 'message' => (isset($this->guid) ? 'Username is valid.' : 'Username is available!')];
    } else {
      if (empty($this->username)) {
        return ['result' => false, 'message' => 'Please specify an email.'];
      }
      if (Tilmeld::$config['max_username_length'] > 0 && strlen($this->username) > Tilmeld::$config['max_username_length']) {
        return ['result' => false, 'message' => 'Emails must not exceed '.Tilmeld::$config['max_username_length'].' characters.'];
      }

      return $this->checkEmail();
    }
  }

  /**
   * Check that an email is unique.
   *
   * @return array An associative array with a boolean 'result' entry and a 'message' entry.
   */
  public function checkEmail() {
    if (empty($this->email)) {
      return ['result' => false, 'message' => 'Please specify an email.'];
    }
    if (!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $this->email)) {
      return ['result' => false, 'message' => 'Email must be a correctly formatted address.'];
    }
    $selector = ['&',
        'ilike' => ['email', str_replace(['%', '_'], ['\%', '\_'], $this->email)]
      ];
    if (isset($this->guid)) {
      $selector['!guid'] = $this->guid;
    }
    $test = \Nymph\Nymph::getEntity(
        ['class' => '\Tilmeld\Entities\User', 'skip_ac' => true],
        $selector
    );
    if (isset($test->guid)) {
      return ['result' => false, 'message' => 'That email address is already registered.'];
    }

    return ['result' => true, 'message' => (isset($this->guid) ? 'Email is valid.' : 'Email address is valid!')];
  }

  /**
   * Check that a phone number is unique.
   *
   * @return array An associative array with a boolean 'result' entry and a 'message' entry.
   */
  public function checkPhone() {
    if (empty($this->phone)) {
      return ['result' => false, 'message' => 'Please specify a phone number.'];
    }

    $strip_to_digits = preg_replace('/\D/', '', $this->phone);
    if (!preg_match('/\d{10}/', $strip_to_digits)) {
      return ['result' => false, 'message' => 'Phone must contain 10 digits, but formatting does not matter.'];
    }
    $selector = ['&',
        'strict' => ['phone', $strip_to_digits]
      ];
    if (isset($this->guid)) {
      $selector['!guid'] = $this->guid;
    }
    $test = \Nymph\Nymph::getEntity(
        ['class' => '\Tilmeld\Entities\User', 'skip_ac' => true],
        $selector
    );
    if (isset($test->guid)) {
      return ['result' => false, 'message' => 'Phone number is in use.'];
    }

    return ['result' => true, 'message' => (isset($this->guid) ? 'Phone number is valid.' : 'Phone number is valid!')];
  }

  public function register($data) {
    if (!Tilmeld::$config['allow_registration']) {
      return ['result' => false, 'message' => 'Registration is not allowed.'];
    }
    if (isset($this->guid)) {
      return ['result' => false, 'message' => 'This is already a registered user.'];
    }
    if (empty($data['password'])) {
      return ['result' => false, 'message' => 'Password is a required field.'];
    }
    $unCheck = $this->checkUsername();
    if (!$unCheck['result']) {
      return $unCheck;
    }

    $this->password($data['password']);
    if (in_array('name', Tilmeld::$config['reg_fields'])) {
      $this->name = $this->nameFirst.(!empty($this->nameMiddle) ? ' '.$this->nameMiddle : '').(!empty($this->nameLast) ? ' '.$this->nameLast : '');
    }
    if (Tilmeld::$config['email_usernames']) {
      $this->email = $this->username;
    }

    $this->group = \Nymph\Nymph::getEntity(array('class' => '\Tilmeld\Entities\Group'), array('&', 'data' => array('defaultPrimary', true)));
    if (!isset($this->group->guid)) {
      unset($this->group);
    }
    if (Tilmeld::$config['verify_email'] && Tilmeld::$config['unverified_access']) {
      $this->groups = (array) \Nymph\Nymph::getEntities(array('class' => '\Tilmeld\Entities\Group'), array('&', 'data' => array('unverifiedSecondary', true)));
    } else {
      $this->groups = (array) \Nymph\Nymph::getEntities(array('class' => '\Tilmeld\Entities\Group'), array('&', 'data' => array('defaultSecondary', true)));
    }

    if (Tilmeld::$config['verify_email']) {
      // The user will be enabled after verifying their e-mail address.
      if (!Tilmeld::$config['unverified_access']) {
        $this->enabled = false;
      }
    } else {
      $this->enabled = true;
    }

    // If create_admin is true and there are no other users, grant "system/admin".
    if (Tilmeld::$config['create_admin']) {
      $otherUsers = \Nymph\Nymph::getEntities(array('class' => '\Tilmeld\Entities\User', 'skip_ac' => true, 'limit' => 1));
      // Make sure it's not just null, cause that means an error.
      if ($otherUsers === array()) {
        $this->grant('system/admin');
        $this->enabled = true;
      }
    }

    if ($this->save()) {
      // Send the new user registered email.
      $macros = array(
        'user_username' => htmlspecialchars($this->username),
        'user_name' => htmlspecialchars($this->name),
        'user_first_name' => htmlspecialchars($this->nameFirst),
        'user_last_name' => htmlspecialchars($this->nameLast),
        'user_email' => htmlspecialchars($this->email),
        'user_phone' => htmlspecialchars(\uMailPHP\Mail::formatPhone($this->phone)),
        'user_timezone' => htmlspecialchars($this->timezone),
        'user_address' => $this->addressType == 'us' ? htmlspecialchars("{$this->addressStreet} {$this->addressStreet2}").'<br />'.htmlspecialchars("{$this->addressCity}, {$this->addressState} {$this->addressZip}") : '<pre>'.htmlspecialchars($this->addressInternational).'</pre>'
      );
      $mail = new \uMailPHP\Mail('\Tilmeld\Entities\Mail\UserRegistered', null, $macros);
      $mail->send();
      if (Tilmeld::$config['verify_email'] && !Tilmeld::$config['unverified_access']) {
        $message = "Almost there. An email has been sent to {$this->email} with a verification link for you to finish registration.";
      } elseif (Tilmeld::$config['verify_email'] && Tilmeld::$config['unverified_access']) {
        Tilmeld::login($this);
        $message = "You're now logged in! An email has been sent to {$this->email} with a verification link for you to finish registration.";
      } else {
        Tilmeld::login($this);
        $message = 'You\'re now registered and logged in!';
      }
      return ['result' => true, 'message' => $message];
    } else {
      return ['result' => false, 'message' => 'Error registering user.'];
    }
  }

  /**
   * Log a user out of the system.
   * @return array An associative array with a boolean 'result' entry and a 'message' entry.
   */
  public function logout() {
    Tilmeld::logout();
    return ['result' => true, 'message' => 'You have been logged out.'];
  }

  public function login($data) {
    if (!isset($this->guid)) {
      return ['result' => false, 'message' => 'This is not a registered user.'];
    }
    if (!$this->enabled) {
      return ['result' => false, 'message' => 'This user is disabled.'];
    }
    if ($this->gatekeeper()) {
      return ['result' => true, 'message' => 'You are already logged in.'];
    }
    if (!$this->checkPassword($data['password'])) {
      return ['result' => false, 'message' => 'Incorrect login/password.'];
    }

    // Authentication was successful, attempt to login.
    if (!Tilmeld::login($this)) {
      return ['result' => false, 'message' => 'Incorrect login/password.'];
    }

    // Login was successful.
    return ['result' => true];
  }

  /**
   * Recover account details.
   *
   * @return array An associative array with a boolean 'result' entry and a 'message' entry.
   */
  public function recover() {
    if (!Tilmeld::$config['pw_recovery']) {
      return ['result' => false, 'message' => 'Account recovery is not allowed.'];
    }

    if (!isset($this->guid) || !$this->enabled) {
      return ['result' => false, 'message' => 'Requested account is not accessible.'];
    }

    // Create a unique secret.
    $this->recoverSecret = uniqid('', true);
    $this->recoverSecretTime = time();
    if (!$this->save()) {
      return ['result' => false, 'message' => 'Couldn\'t save user secret.'];
    }

    // Send the recovery email.
    $link = htmlspecialchars(Tilmeld::$config['setup_url'].(strpos(Tilmeld::$config['setup_url'], '?') ? '&' : '?').'action=recover&id='.$this->guid.'&secret='.$this->recoverSecret);
    $macros = array(
      'recover_link' => $link,
      'minutes' => htmlspecialchars(Tilmeld::$config['pw_recovery_minutes']),
      'to_phone' => htmlspecialchars(\uMailPHP\Mail::formatPhone($this->phone)),
      'to_timezone' => htmlspecialchars($this->timezone),
      'to_address' => $this->addressType == 'us' ? htmlspecialchars("{$this->addressStreet} {$this->addressStreet2}").'<br />'.htmlspecialchars("{$this->addressCity}, {$this->addressState} {$this->addressZip}") : '<pre>'.htmlspecialchars($this->addressInternational).'</pre>'
    );
    $mail = new \uMailPHP\Mail('\Tilmeld\Entities\Mail\RecoverAccount', $this, $macros);
    if ($mail->send()) {
      return ['result' => true, 'message' => 'We\'ve sent an email to your registered address. Please check your email to continue with account recovery.'];
    } else {
      return ['result' => false, 'message' => 'Couldn\'t send recovery email.'];
    }
  }

  public static function getClientConfig() {
    $timezones = \DateTimeZone::listIdentifiers();
    sort($timezones);
    return (object) [
      'reg_fields' => Tilmeld::$config['reg_fields'],
      'email_usernames' => Tilmeld::$config['email_usernames'],
      'allow_registration' => Tilmeld::$config['allow_registration'],
      'pw_recovery' => Tilmeld::$config['pw_recovery'],
      'timezones' => $timezones,
    ];
  }
}
