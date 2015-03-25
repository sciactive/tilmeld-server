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
 * @property string $phone The user's telephone number.
 * @property string $address_type The user's address type. "us" or "international".
 * @property string $address_1 The user's address line 1 for US addresses.
 * @property string $address_2 The user's address line 2 for US addresses.
 * @property string $city The user's city for US addresses.
 * @property string $state The user's state abbreviation for US addresses.
 * @property string $zip The user's ZIP code for US addresses.
 * @property string $address_international The user's full address for international addresses.
 * @property Group $group The user's primary group.
 * @property array $groups The user's secondary groups.
 * @property bool $inherit_abilities Whether the user should inherit the abilities of his groups.
 */
class User extends AbleObject {
	const etype = 'user';
	protected $tags = ['enabled'];

	/**
	 * Used to save the current email to resend verification if it changes.
	 * @access protected
	 * @var string $verify_email
	 */
	protected $verify_email = '';

	/**
	 * Load a user.
	 *
	 * @param int|string $id The ID or username of the user to load, 0 for a new user.
	 */
	public function __construct($id = 0) {
		if ($id > 0 || (string) $id === $id) {
			if ((int) $id === $id) {
				$entity = \Nymph\Nymph::getEntity(['class' => get_class($this)], ['&', 'guid' => $id]);
			} else {
				$entity = \Nymph\Nymph::getEntity(['class' => get_class($this)], ['&', 'strict' => [(Tilmeld::$config->email_usernames['value'] ? 'email' : 'username'), (string) $id]]);
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
		$this->addresses = [];
		$this->attributes = [];
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
				return "$this->name [$this->username]";
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
			if ($this->sendEmailVerification()) {
				pines_notice('New verification email sent to the new email address.');
			} else {
				pines_error('Couldn\'t send verification email to new email address.');
			}
		}
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
		$params = ['id' => $this->guid, 'type' => 'register', 'secret' => $this->secret];
		if (!empty($url)) {
			$params['url'] = $url;
		}
		$link = h(pines_url('com_user', 'verifyuser', $params, true));
		$macros = [
			'verify_link' => $link,
			'to_phone' => h(format_phone($this->phone)),
			'to_fax' => h(format_phone($this->fax)),
			'to_timezone' => h($this->timezone),
			'to_address' => $this->address_type == 'us' ? h("{$this->address_1} {$this->address_2}").'<br />'.h("{$this->city}, {$this->state} {$this->zip}") : '<pre>'.h($this->address_international).'</pre>'
		];
		$mail = new \µMailPHP\Mail('\Tilmeld\MailVerifyEmail', $this, $macros);
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
				$module->group_array_primary = $highest_parent->get_descendants();
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
				$module->group_array_secondary = $highest_parent->get_descendants();
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
	 * @param group $group The group.
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
	 * @param group $group The group.
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
}
