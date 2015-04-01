<?php namespace Tilmeld;
/**
 * User class.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

/**
 * A user.
 *
 * @package Tilmeld
 * @property int $guid The GUID of the user.
 * @property string $username The user's username.
 * @property string $name_first The user's first name.
 * @property string $name_middle The user's middle name.
 * @property string $name_last The user's last name.
 * @property string $name The user's full name.
 * @property string $email The user's email address.
 * @property string $verify_email Used to save the current email to send verification if it changes.
 * @property string $phone The user's telephone number.
 * @property string $address_type The user's address type. "us" or "international".
 * @property string $address_1 The user's address line 1 for US addresses.
 * @property string $address_2 The user's address line 2 for US addresses.
 * @property string $city The user's city for US addresses.
 * @property string $state The user's state abbreviation for US addresses.
 * @property string $zip The user's ZIP code for US addresses.
 * @property string $address_international The user's full address for international addresses.
 * @property \Tilmeld\Group $group The user's primary group.
 * @property array $groups The user's secondary groups.
 * @property bool $inherit_abilities Whether the user should inherit the abilities of his groups.
 */
class User extends AbleObject {
	const etype = 'user';
	protected $tags = [];
	public $clientEnabledMethods = [
		'checkUsername',
		'checkEmail',
		'checkPhone',
		'register',
		'logout',
		'login',
		'gatekeeper',
		'recover',
	];
	protected $privateData = [
		'email',
		'verify_email',
		'phone',
		'address_type',
		'address_1',
		'address_2',
		'city',
		'state',
		'zip',
		'address_international',
		'group',
		'groups',
		'abilities',
		'inherit_abilities',
		'timezone',
		'secret',
		'secret_time',
		'password',
		'salt',
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
				if (isset($this->secret)) {
					$this->verify_email = $this->email;
				}
				return;
			}
		}
		// Defaults.
		$this->abilities = [];
		$this->groups = [];
		$this->inherit_abilities = true;
		$this->address_type = 'us';
	}

	public static function current($returnObjectIfNotExist = false) {
		if (!isset($_SESSION['tilmeld_user'])) {
			Tilmeld::session();
		}
		if (!isset($_SESSION['tilmeld_user'])) {
			return $returnObjectIfNotExist ? User::factory() : null;
		}
		return $_SESSION['tilmeld_user'];
	}

	public function putData($data, $sdata = []) {
		$return = parent::putData($data, $sdata);
		$this->updateDataProtection();
		return $return;
	}

	public function updateDataProtection() {
		if (Tilmeld::$config->email_usernames['value']) {
			$this->privateData[] = 'username';
		}
		if (User::current(true)->gatekeeper('tilmeld/editusers')) {
			// Users who can edit other users can see most of their data.
			$this->privateData = [
				'secret',
				'secret_time',
				'password',
				'salt'
			];
			$this->whitelistData = false;
			$this->whitelistTags = ['enabled'];
			return;
		}
		if ($this->is(User::current())) {
			// Users can see their own data, and edit some of it.
			$this->whitelistData[] = 'username';
			if (in_array('name', Tilmeld::$config->user_fields['value'])) {
				$this->whitelistData[] = 'name_first';
				$this->whitelistData[] = 'name_middle';
				$this->whitelistData[] = 'name_last';
				$this->whitelistData[] = 'name';
			}
			if (in_array('email', Tilmeld::$config->user_fields['value'])) {
				$this->whitelistData[] = 'email';
			}
			if (in_array('phone', Tilmeld::$config->user_fields['value'])) {
				$this->whitelistData[] = 'phone';
			}
			if (in_array('timezone', Tilmeld::$config->user_fields['value'])) {
				$this->whitelistData[] = 'timezone';
			}
			if (in_array('address', Tilmeld::$config->user_fields['value'])) {
				$this->whitelistData[] = 'address_type';
				$this->whitelistData[] = 'address_1';
				$this->whitelistData[] = 'address_2';
				$this->whitelistData[] = 'city';
				$this->whitelistData[] = 'state';
				$this->whitelistData[] = 'zip';
				$this->whitelistData[] = 'address_international';
			}
			$this->privateData = [
				'verify_email',
				'secret',
				'secret_time',
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
		if (Tilmeld::$config->email_usernames['value'] && $name == 'username') {
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
		if (Tilmeld::$config->email_usernames['value'] && $name == 'username') {
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
		if (Tilmeld::$config->email_usernames['value'] && ($name == 'username' || $name == 'email')) {
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
		if (Tilmeld::$config->email_usernames['value'] && ($name == 'username' || $name == 'email')) {
			parent::__unset('username');
			return parent::__unset('email');
		}
		return parent::__unset($name);
	}

	public function info($type) {
		switch ($type) {
			case 'name':
				return $this->name;
			case 'type':
				return 'user';
			case 'types':
				return 'users';
			case 'avatar':
				$proto = $_SERVER['HTTPS'] ? 'https' : 'http';
				if (!isset($this->email) || empty($this->email)) {
					return $proto.'://secure.gravatar.com/avatar/?d=mm&s=40';
				}
				return $proto.'://secure.gravatar.com/avatar/'.md5(strtolower(trim($this->email))).'?d=identicon&s=40';
			default:
				return parent::info($type);
		}
		return null;
	}

	public function jsonSerialize($clientClassName = true) {
		$object = parent::jsonSerialize($clientClassName);
		$object->info['avatar'] = $this->info('avatar');
		return $object;
	}

	public function delete() {
		if (!User::current(true)->gatekeeper('tilmeld/editusers')) {
			return false;
		}
		return parent::delete();
	}

	/**
	 * Disable the user.
	 */
	public function disable() {
		$this->removeTag('enabled');
	}

	/**
	 * Enable the user.
	 */
	public function enable() {
		$this->addTag('enabled');
	}

	public function save() {
		if (!isset($this->username)) {
			return false;
		}
		if (isset($this->guid) && isset($this->secret) && !empty($this->verify_email) && $this->verify_email != $this->email) {
			$send_verification = true;
		}
		$return = parent::save();
		if ($return && $send_verification) {
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
	 * If the user has the "system/all" ability, this function will return true.
	 *
	 * @param string $ability The ability.
	 * @return bool True or false.
	 */
	public function gatekeeper($ability = null) {
		if (!isset($ability)) {
			return $this->is(User::current());
		}
		// Check the cache to see if we've already checked this user.
		if ($this->gatekeeperCache) {
			$abilities =& $this->gatekeeperCache;
		} else {
			$abilities = $this->abilities;
			if ($this->inherit_abilities) {
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
		return (in_array($ability, $abilities) || in_array('system/all', $abilities));
	}

	public function clearCache() {
		$return = parent::clearCache();
		$this->gatekeeperCache = [];
		return $return;
	}

	/**
	 * Send the user an email verification link.
	 *
	 * The user must be a new user, with a GUID and a secret.
	 *
	 * @param string $url The URL that the user is taken to after verification.
	 * @return bool True on success, false on failure.
	 */
	public function sendEmailVerification($url = '') {
		if (!isset($this->guid) || !isset($this->secret)) {
			return false;
		}
		$link = htmlspecialchars(Tilmeld::$config->setup_url['value'].(strpos(Tilmeld::$config->setup_url['value'], '?') ? '&' : '?').'action=verifyuser&type=register&id='.$this->guid.'&secret='.$this->secret);
		$macros = [
			'verify_link' => $link,
			'to_phone' => htmlspecialchars(\µMailPHP\Mail::formatPhone($this->phone)),
			'to_timezone' => htmlspecialchars($this->timezone),
			'to_address' => $this->address_type == 'us' ? htmlspecialchars("{$this->address_1} {$this->address_2}").'<br />'.htmlspecialchars("{$this->city}, {$this->state} {$this->zip}") : '<pre>'.htmlspecialchars($this->address_international).'</pre>'
		];
		$mail = new \µMailPHP\Mail('\Tilmeld\Mail\VerifyEmail', $this, $macros);
		return $mail->send();
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
		$highest_parent = Tilmeld::$config->highest_primary['value'];
		if ($highest_parent == 0) {
			$module->group_array_primary = \Nymph\Nymph::getEntities(['class' => '\Tilmeld\Group'], ['&', 'tag' => 'enabled']);
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
		$highest_parent = Tilmeld::$config->highest_secondary['value'];
		if ($highest_parent == 0) {
			$module->group_array_secondary = \Nymph\Nymph::getEntities(['class' => '\Tilmeld\Group'], ['&', 'tag' => 'enabled']);
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
		foreach ($_->components as $cur_component) {
			$module->sections[] = $cur_component;
		}

		return $module;
	}

	/**
	 * Print a form to change the user's password.
	 *
	 * @return module The form's module.
	 */
	public function printFormPassword() {
		$module = new module('com_user', 'form_password', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a registration form for the user to fill out.
	 *
	 * @return module The form's module.
	 */
	public function printRegister() {
		$module = new module('com_user', 'form_register', 'content');
		$module->entity = $this;
		foreach ($_->components as $cur_component) {
			$module->sections[] = $cur_component;
		}

		return $module;
	}

	/**
	 * Add the user to a (secondary) group.
	 *
	 * @param \Tilmeld\Group $group The group.
	 * @return mixed True if the user is already in the group. The resulting array of groups if the user was not.
	 */
	public function addGroup($group) {
		if ( !$group->inArray((array) $this->groups) ) {
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
		if ($pass && $cur_type != Tilmeld::$config->pw_method['value']) {
			switch (Tilmeld::$config->pw_method['value']) {
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
	 * Check the given client hash and server challenge using SAWASC.
	 *
	 * @param string $ClientHash The hash provided by the client.
	 * @param string $ServerCB The challenge block generated by the server.
	 * @param string $algo Hash algorithm. Check hash_algos().
	 * @return bool True if the hashes match, otherwise false.
	 */
	public function checkSawasc($ClientHash, $ServerCB, $algo) {
		if ($this->salt == '7d5bc9dc81c200444e53d1d10ecc420a') {
			$input = $this->password;
		} else {
			$input = md5($this->password.'7d5bc9dc81c200444e53d1d10ecc420a');
		}
		$ServerComb = $ServerCB.$input;
		$ServerHash = hash($algo, $ServerComb);
		return ($ClientHash === $ServerHash);
	}

	/**
	 * Remove the user from a (secondary) group.
	 *
	 * @param \Tilmeld\Group $group The group.
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
		switch (Tilmeld::$config->pw_method['value']) {
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
		foreach((array) $this->groups as $cur_group) {
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
		if (!Tilmeld::$config->email_usernames['value']) {
			if (empty($this->username)) {
				return ['result' => false, 'message' => 'Please specify a username.'];
			}
			if (Tilmeld::$config->max_username_length['value'] > 0 && strlen($this->username) > Tilmeld::$config->max_username_length['value']) {
				return ['result' => false, 'message' => 'Usernames must not exceed '.Tilmeld::$config->max_username_length['value'].' characters.'];
			}
			if (array_diff(str_split($this->username), str_split(Tilmeld::$config->valid_chars['value']))) {
				return ['result' => false, 'message' => Tilmeld::$config->valid_chars_notice['value']];
			}
			if (!preg_match(Tilmeld::$config->valid_regex['value'], $this->username)) {
				return ['result' => false, 'message' => Tilmeld::$config->valid_regex_notice['value']];
			}
			$selector = ['&',
					'match' => ['username', '/^'.preg_quote($this->username, '/').'$/i']
				];
			if (isset($this->guid)) {
				$selector['!guid'] = $this->guid;
			}
			$test = \Nymph\Nymph::getEntity(
					['class' => '\Tilmeld\User', 'skip_ac' => true],
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
			if (Tilmeld::$config->max_username_length['value'] > 0 && strlen($this->username) > Tilmeld::$config->max_username_length['value']) {
				return ['result' => false, 'message' => 'Emails must not exceed '.Tilmeld::$config->max_username_length['value'].' characters.'];
			}
			if (!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $this->username)) {
				return ['result' => false, 'message' => 'Email must be a correctly formatted address.'];
			}
			$selector = ['&',
					'match' => ['email', '/^'.preg_quote($this->username, '/').'$/i']
				];
			if (isset($this->guid)) {
				$selector['!guid'] = $this->guid;
			}
			$test = \Nymph\Nymph::getEntity(
					['class' => '\Tilmeld\User', 'skip_ac' => true],
					$selector
				);
			if (isset($test->guid)) {
				return ['result' => false, 'message' => 'That email address is already registered.'];
			}

			return ['result' => true, 'message' => (isset($this->guid) ? 'Email is valid.' : 'Email address is valid!')];
		}
	}

	/**
	 * Check that an email is unique.
	 *
	 * Wrote this mainly for quick ajax testing of the email for user sign up on
	 * an application.
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
				'match' => ['email', '/^'.preg_quote($this->email, '/').'$/i']
			];
		if (isset($this->guid)) {
			$selector['!guid'] = $this->guid;
		}
		$test = \Nymph\Nymph::getEntity(
				['class' => '\Tilmeld\User', 'skip_ac' => true],
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
	 * Wrote this mainly for quick ajax testing of the phone for user sign up on
	 * an application.
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
				['class' => '\Tilmeld\User', 'skip_ac' => true],
				$selector
			);
		if (isset($test->guid)) {
			return ['result' => false, 'message' => 'Phone number is in use.'];
		}

		return ['result' => true, 'message' => (isset($this->guid) ? 'Phone number is valid.' : 'Phone number is valid!')];
	}

	public function register($data) {
		if (!Tilmeld::$config->allow_registration['value']) {
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
		if (in_array('name', Tilmeld::$config->reg_fields['value'])) {
			$this->name = $this->name_first.(!empty($this->name_middle) ? ' '.$this->name_middle : '').(!empty($this->name_last) ? ' '.$this->name_last : '');
		}
		if (Tilmeld::$config->email_usernames['value']) {
			$this->email = $this->username;
		}

		$this->group = \Nymph\Nymph::getEntity(array('class' => '\Tilmeld\Group', 'skip_ac' => true), array('&', 'data' => array('default_primary', true)));
		if (!isset($this->group->guid)) {
			unset($this->group);
		}
		if (Tilmeld::$config->verify_email['value'] && Tilmeld::$config->unverified_access['value']) {
			$this->groups = (array) \Nymph\Nymph::getEntities(array('class' => '\Tilmeld\Group', 'skip_ac' => true), array('&', 'data' => array('unverified_secondary', true)));
		} else {
			$this->groups = (array) \Nymph\Nymph::getEntities(array('class' => '\Tilmeld\Group', 'skip_ac' => true), array('&', 'data' => array('default_secondary', true)));
		}

		if (Tilmeld::$config->verify_email['value']) {
			// The user will be enabled after verifying their e-mail address.
			if (!Tilmeld::$config->unverified_access['value']) {
				$this->disable();
			}
			$this->secret = uniqid('', true);
		} else {
			$this->enable();
		}

		// If create_admin is true and there are no other users, grant "system/all".
		if (Tilmeld::$config->create_admin['value']) {
			$otherUsers = \Nymph\Nymph::getEntities(array('class' => '\Tilmeld\User', 'skip_ac' => true, 'limit' => 1));
			// Make sure it's not just null, cause that means an error.
			if ($otherUsers === array()) {
				$this->grant('system/all');
				$this->enable();
			}
		}

		if ($this->save()) {
			// Send the new user registered email.
			$macros = array(
				'user_username' => htmlspecialchars($this->username),
				'user_name' => htmlspecialchars($this->name),
				'user_first_name' => htmlspecialchars($this->name_first),
				'user_last_name' => htmlspecialchars($this->name_last),
				'user_email' => htmlspecialchars($this->email),
				'user_phone' => htmlspecialchars(\µMailPHP\Mail::formatPhone($this->phone)),
				'user_timezone' => htmlspecialchars($this->timezone),
				'user_address' => $this->address_type == 'us' ? htmlspecialchars("{$this->address_1} {$this->address_2}").'<br />'.htmlspecialchars("{$this->city}, {$this->state} {$this->zip}") : '<pre>'.htmlspecialchars($this->address_international).'</pre>'
			);
			$mail = new \µMailPHP\Mail('\Tilmeld\Mail\UserRegistered', null, $macros);
			$mail->send();
			if (Tilmeld::$config->verify_email['value']) {
				// Send the verification email.
				if ($this->sendEmailVerification()) {
					if (Tilmeld::$config->unverified_access['value']) {
						Tilmeld::login($this);
					}
					return ['result' => true, 'message' => "Almost there. An email has been sent to {$this->email} with a verification link for you to finish registration."];
				} else {
					return ['result' => false, 'message' => 'Couldn\'t send registration email.'];
				}
			} else {
				Tilmeld::login($this);
				return ['result' => true, 'message' => 'You\'re now registered and logged in!'];
			}
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
		if (!$this->hasTag('enabled')) {
			return ['result' => false, 'message' => 'This user is disabled.'];
		}
		if ($this->gatekeeper()) {
			return ['result' => true, 'message' => 'You are already logged in.'];
		}
		// Check that a challenge block was created within 10 minutes.
		if (
				(Tilmeld::$config->sawasc['value'] && Tilmeld::$config->pw_method['value'] !== 'salt') &&
				(!isset($_SESSION['sawasc']['ServerCB']) || $_SESSION['sawasc']['timestamp'] < time() - 600)
			) {
			return ['result' => false, 'message' => 'Your login request session has expired, please try again.'];
		}
		if (Tilmeld::$config->sawasc['value'] && Tilmeld::$config->pw_method['value'] != 'salt') {
			Tilmeld::session('write');
			if (!$this->checkSawasc($data['ClientHash'], $_SESSION['sawasc']['ServerCB'], $_SESSION['sawasc']['algo'])) {
				unset($_SESSION['sawasc']);
				Tilmeld::session('close');
				return ['result' => false, 'message' => 'Incorrect login/password.'];
			}
			unset($_SESSION['sawasc']);
			Tilmeld::session('close');
		} else {
			if (!$this->checkPassword($data['password'])) {
				return ['result' => false, 'message' => 'Incorrect login/password.'];
			}
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
		if (!Tilmeld::$config->pw_recovery['value']) {
			return ['result' => false, 'message' => 'Account recovery is not allowed.'];
		}

		if (!isset($this->guid) || !$this->hasTag('enabled')) {
			return ['result' => false, 'message' => 'Requested account is not accessible.'];
		}

		// Create a unique secret.
		$this->secret = uniqid('', true);
		$this->secret_time = time();
		if (!$this->save()) {
			return ['result' => false, 'message' => 'Couldn\'t save user secret.'];
		}

		// Send the recovery email.
		$link = htmlspecialchars(Tilmeld::$config->setup_url['value'].(strpos(Tilmeld::$config->setup_url['value'], '?') ? '&' : '?').'action=recover&id='.$this->guid.'&secret='.$this->secret);
		$macros = array(
			'recover_link' => $link,
			'minutes' => htmlspecialchars(Tilmeld::$config->pw_recovery_minutes['value']),
			'to_phone' => htmlspecialchars(\µMailPHP\Mail::formatPhone($this->phone)),
			'to_timezone' => htmlspecialchars($this->timezone),
			'to_address' => $this->address_type == 'us' ? htmlspecialchars("{$this->address_1} {$this->address_2}").'<br />'.htmlspecialchars("{$this->city}, {$this->state} {$this->zip}") : '<pre>'.htmlspecialchars($this->address_international).'</pre>'
		);
		$mail = new \µMailPHP\Mail('\Tilmeld\Mail\RecoverAccount', $this, $macros);
		if ($mail->send()) {
			return ['result' => true, 'message' => 'We\'ve sent an email to your registered address. Please check your email to continue with account recovery.'];
		} else {
			return ['result' => false, 'message' => 'Couldn\'t send recovery email.'];
		}
	}
}
