<?php namespace Tilmeld;
/**
 * Tilmeld class.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

/**
 * com_user main class.
 *
 * Provides an Nymph based user and group manager.
 *
 * @package Tilmeld
 */
class Tilmeld {
	/**
	 * A copy of the Tilmeld config.
	 *
	 * @var stdClass
	 * @access public
	 */
	public static $config;
	/**
	 * Gatekeeper ability cache.
	 *
	 * Gatekeeper will cache users' abilities that it calculates, so it can
	 * check faster if that user has been checked before.
	 *
	 * @var array
	 * @access public
	 */
	public static $gatekeeperCache = [];

	/**
	 * Activate the SAWASC system.
	 * @return bool True if SAWASC could be activated, false otherwise.
	 */
	public static function activateSawasc() {
		if (!Tilmeld::$config->sawasc['value']) {
			return false;
		}
		if (Tilmeld::$config->pw_method['value'] == 'salt') {
			pines_notice('SAWASC is not compatible with the Salt password storage method.');
			return false;
		}
		// Check that a challenge block was created within 10 minutes.
		if (!isset($_SESSION['sawasc']['ServerCB']) || $_SESSION['sawasc']['timestamp'] < time() - 600) {
			// If not, generate one.
			self::session('write');
			$_SESSION['sawasc'] = [
				'ServerCB' => uniqid('', true),
				'timestamp' => time(),
				'algo' => Tilmeld::$config->sawasc_hash['value']
			];
			self::session('close');
		}
		return true;
	}

	/**
	 * Check an entity's permissions for the currently logged in user.
	 *
	 * This will check the variable "ac" (Access Control) of the entity. It
	 * should be an object that contains the following properties:
	 *
	 * - user
	 * - group
	 * - other
	 *
	 * The property "user" refers to the entity's owner, "group" refers to all
	 * users in the entity's group and all ancestor groups, and "other" refers
	 * to any user who doesn't fit these descriptions.
	 *
	 * Each variable should be either 0, 1, 2, or 3. If it is 0, the user has no
	 * access to the entity. If it is 1, the user has read access to the entity.
	 * If it is 2, the user has read and write access to the entity. If it is 3,
	 * the user has read, write, and delete access to the entity.
	 *
	 * "ac" defaults to:
	 *
	 * - user = 3
	 * - group = 3
	 * - other = 0
	 *
	 * The following conditions will result in different checks, which determine
	 * whether the check passes:
	 *
	 * - No user is logged in. (Always true, should be managed with abilities.)
	 * - The entity has no "user" and no "group". (Always true.)
	 * - The user has the "system/all" ability. (Always true.)
	 * - The entity is the user. (Always true.)
	 * - It is the user's primary group. (Always true.)
	 * - The entity is a user or group. (Always true.)
	 * - Its "user" is the user. (It is owned by the user.) (Check user AC.)
	 * - Its "group" is the user's primary group. (Check group AC.)
	 * - Its "group" is one of the user's secondary groups. (Check group AC.)
	 * - Its "group" is a descendant of one of the user's groups. (Check group
	 *   AC.)
	 * - None of the above. (Check other AC.)
	 *
	 * @param object &$entity The entity to check.
	 * @param int $type The lowest level of permission to consider a pass. 1 is read, 2 is write, 3 is delete.
	 * @return bool Whether the current user has at least $type permission for the entity.
	 */
	public static function checkPermissions(&$entity, $type = 1) {
		if ((object) $entity !== $entity) {
			return false;
		}
		if ((object) $_SESSION['user'] !== $_SESSION['user']) {
			return true;
		}
		if (function_exists('gatekeeper') && gatekeeper('system/all')) {
			return true;
		}
		if (is_a($entity, '\Tilmeld\User') || is_a($entity, '\Tilmeld\Group')) {
			return true;
		}
		if (!isset($entity->user->guid) && !isset($entity->group->guid)) {
			return true;
		}

		// Load access control, since we need it now...
		if ((object) $entity->ac === $entity->ac) {
			$ac = $entity->ac;
		} else {
			$ac = (object) ['user' => 3, 'group' => 3, 'other' => 0];
		}

		if (is_callable([$entity->user, 'is']) && $entity->user->is($_SESSION['user'])) {
			return ($ac->user >= $type);
		}
		if (is_callable([$entity->group, 'is']) && ($entity->group->is($_SESSION['user']->group) || $entity->group->inArray($_SESSION['user']->groups) || $entity->group->inArray($_SESSION['descendants'])) ) {
			return ($ac->group >= $type);
		}
		return ($ac->other >= $type);
	}

	/**
	 * Check that a username is valid.
	 *
	 * The ID of a user can be given so that user is excluded when checking if
	 * the name is already in use.
	 *
	 * @param string $username The username to check.
	 * @param int $id The GUID of the user for which the name is being checked.
	 * @return array An associative array with a boolean 'result' entry and a 'message' entry.
	 */
	public static function checkUsername($username, $id = null) {
		if (!Tilmeld::$config->email_usernames['value']) {
			if (empty($username)) {
				return ['result' => false, 'message' => 'Please specify a username.'];
			}
			if (Tilmeld::$config->max_username_length['value'] > 0 && strlen($username) > Tilmeld::$config->max_username_length['value']) {
				return ['result' => false, 'message' => "Usernames must not exceed {Tilmeld::$config->max_username_length['value']} characters."];
			}
			if (array_diff(str_split($username), str_split(Tilmeld::$config->valid_chars['value']))) {
				return ['result' => false, 'message' => Tilmeld::$config->valid_chars_notice['value']];
			}
			if (!preg_match(Tilmeld::$config->valid_regex['value'], $username)) {
				return ['result' => false, 'message' => Tilmeld::$config->valid_regex_notice['value']];
			}
			$selector = ['&',
					'match' => ['username', '/^'.preg_quote($username, '/').'$/i']
				];
			if (isset($id) && $id > 0) {
				$selector['!guid'] = $id;
			}
			$test = \Nymph\Nymph::getEntity(
					['class' => '\Tilmeld\User', 'skip_ac' => true],
					$selector
				);
			if (isset($test->guid)) {
				return ['result' => false, 'message' => 'That username is taken.'];
			}

			return ['result' => true, 'message' => (isset($id) ? 'Username is valid.' : 'Username is available!')];
		} else {
			if (empty($username)) {
				return ['result' => false, 'message' => 'Please specify an email.'];
			}
			if (Tilmeld::$config->max_username_length['value'] > 0 && strlen($username) > Tilmeld::$config->max_username_length['value']) {
				return ['result' => false, 'message' => "Emails must not exceed {Tilmeld::$config->max_username_length['value']} characters."];
			}
			if (!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $username)) {
				return ['result' => false, 'message' => 'Email must be a correctly formatted address.'];
			}
			$selector = ['&',
					'match' => ['email', '/^'.preg_quote($username, '/').'$/i']
				];
			if (isset($id) && $id > 0) {
				$selector['!guid'] = $id;
			}
			$test = \Nymph\Nymph::getEntity(
					['class' => '\Tilmeld\User', 'skip_ac' => true],
					$selector
				);
			if (isset($test->guid)) {
				return ['result' => false, 'message' => 'That email address is already registered.'];
			}

			return ['result' => true, 'message' => (isset($id) ? 'Email is valid.' : 'Email address is valid!')];
		}
	}

	/**
	 * Check that an email is unique.
	 *
	 * The ID of a user can be given so that user is excluded when checking if
	 * the email is already in use.
	 *
	 * Wrote this mainly for quick ajax testing of the email for user sign up on
	 * an application.
	 *
	 * @param string $email The email to check.
	 * @param int $id The GUID of the user for which the email is being checked.
	 * @return array An associative array with a boolean 'result' entry and a 'message' entry.
	 */
	public static function checkEmail($email, $id = null) {
		if (empty($email)) {
			return ['result' => false, 'message' => 'Please specify an email.'];
		}
		if (!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email)) {
			return ['result' => false, 'message' => 'Email must be a correctly formatted address.'];
		}
		$selector = ['&',
				'match' => ['email', '/^'.preg_quote($email, '/').'$/i']
			];
		if (isset($id) && $id > 0) {
			$selector['!guid'] = $id;
		}
		$test = \Nymph\Nymph::getEntity(
				['class' => '\Tilmeld\User', 'skip_ac' => true],
				$selector
			);
		if (isset($test->guid)) {
			return ['result' => false, 'message' => 'That email address is already registered.'];
		}

		return ['result' => true, 'message' => (isset($id) ? 'Email is valid.' : 'Email address is valid!')];
	}

	/**
	 * Check that a phone number is unique.
	 *
	 * The ID of a user can be given so that user is excluded when checking if
	 * the phone is already in use.
	 *
	 * Wrote this mainly for quick ajax testing of the phone for user sign up on
	 * an application.
	 *
	 * @param string $phone The phone to check.
	 * @param int $id The GUID of the user for which the phone is being checked.
	 * @return array An associative array with a boolean 'result' entry and a 'message' entry.
	 */
	public static function checkPhone($phone, $id = null) {
		if (empty($phone)) {
			return ['result' => false, 'message' => 'Please specify a phone number.'];
		}

		$strip_to_digits = preg_replace('/\D/', '', $phone);
		if (!preg_match('/\d{10}/', $strip_to_digits)) {
			return ['result' => false, 'message' => 'Phone must contain 10 digits, but formatting does not matter.'];
		}
		$selector = [];
		$or = ['|',
				'data' => [
					['phone_cell', $strip_to_digits],
					['phone', $strip_to_digits]
				]
			];
		if (isset($id) && $id > 0) {
			$selector = ['&', '!guid' => $id];
		}
		$test = \Nymph\Nymph::getEntity(
				['class' => '\Tilmeld\User', 'skip_ac' => true],
				$selector, $or
			);
		if (isset($test->guid)) {
			return ['result' => false, 'message' => 'Phone number is in use.'];
		}

		return ['result' => true, 'message' => (isset($id) ? 'Phone number is valid.' : 'Phone number is valid!')];
	}

	/**
	 * Fill the $_SESSION['user'] variable with the logged in user's data.
	 *
	 * Also sets the default timezone to the user's timezone.
	 *
	 * This must be called at the i11 position in the init script processing.
	 */
	public static function fillSession() {
		self::session('write');
		if ((object) $_SESSION['user'] === $_SESSION['user']) {
			$tmp_user = \Nymph\Nymph::getEntity(
					['class' => '\Tilmeld\User'],
					['&',
						'guid' => [$_SESSION['user']->guid],
						'gt' => ['mdate', $_SESSION['user']->mdate]
					]
				);
			if (!isset($tmp_user)) {
				$_SESSION['user']->clearCache();
				date_default_timezone_set($_SESSION['user_timezone']);
				self::session('close');
				return;
			}
			unset($_SESSION['user']);
		} else {
			$tmp_user = user::factory($_SESSION['user_id']);
		}
		$_SESSION['user_timezone'] = $tmp_user->getTimezone();
		date_default_timezone_set($_SESSION['user_timezone']);
		if (isset($tmp_user->group)) {
			$_SESSION['descendants'] = (array) $tmp_user->group->get_descendants();
		}
		foreach ($tmp_user->groups as $cur_group) {
			$_SESSION['descendants'] = array_merge((array) $_SESSION['descendants'], (array) $cur_group->get_descendants());
		}
		if ($tmp_user->inherit_abilities) {
			$_SESSION['inherited_abilities'] = $tmp_user->abilities;
			foreach ($tmp_user->groups as $cur_group) {
				// Check that any group conditions are met before adding the abilities.
				if ($cur_group->conditions && Tilmeld::$config->conditional_groups['value']) {
					$pass = true;
					foreach ($cur_group->conditions as $cur_type => $cur_value) {
						if (!$_->depend->check($cur_type, $cur_value)) {
							$pass = false;
							break;
						}
					}
					if (!$pass) {
						continue;
					}
				}
				// Any conditions are met, so add this group's abilities.
				$_SESSION['inherited_abilities'] = array_merge($_SESSION['inherited_abilities'], $cur_group->abilities);
			}
			if (isset($tmp_user->group)) {
				// Check that any group conditions are met before adding the abilities.
				$pass = true;
				if ($tmp_user->group->conditions && Tilmeld::$config->conditional_groups['value']) {
					foreach ($tmp_user->group->conditions as $cur_type => $cur_value) {
						if (!$_->depend->check($cur_type, $cur_value)) {
							$pass = false;
							break;
						}
					}
				}
				// If all conditions are met, add this group's abilities.
				if ($pass) {
					$_SESSION['inherited_abilities'] = array_merge($_SESSION['inherited_abilities'], $tmp_user->group->abilities);
				}
			}
		}
		$_SESSION['user'] = $tmp_user;
		self::session('close');
	}

	/**
	 * Check to see if a user has an ability.
	 *
	 */
	/**
	 * Check to see if a user has an ability.
	 *
	 * This function will check both user and group abilities, if the user is
	 * marked to inherit the abilities of its group.
	 *
	 * If $ability and $user are null, it will check to see if a user is
	 * currently logged in.
	 *
	 * If the user has the "system/all" ability, this function will return true.
	 *
	 * @param string $ability The ability.
	 * @param user $user The user to check. If none is given, the current user is used.
	 * @return bool True or false.
	 */
	public static function gatekeeper($ability = null, $user = null) {
		if (!isset($user)) {
			// If the user is logged in, their abilities are already set up. We
			// just need to add them to the user's.
			if ((object) $_SESSION['user'] === $_SESSION['user']) {
				if ( !isset($ability) || empty($ability) ) {
					return true;
				}
				$user =& $_SESSION['user'];
				// Check the cache to see if we've already checked this user.
				if (isset(self::$gatekeeperCache[$_SESSION['user_id']])) {
					$abilities =& self::$gatekeeperCache[$_SESSION['user_id']];
				} else {
					$abilities = $user->abilities;
					if (isset($_SESSION['inherited_abilities'])) {
						$abilities = array_merge($abilities, $_SESSION['inherited_abilities']);
					}
					self::$gatekeeperCache[$_SESSION['user_id']] = $abilities;
				}
			}
		} else {
			// If the user isn't logged in, their abilities need to be set up.
			// Check the cache to see if we've already checked this user.
			if (isset(self::$gatekeeperCache[$user->guid])) {
				$abilities =& self::$gatekeeperCache[$user->guid];
			} else {
				$abilities = $user->abilities;
				// TODO: Decide if group conditions should be checked if the user is not logged in.
				if ($user->inherit_abilities) {
					foreach ($user->groups as &$cur_group) {
						$abilities = array_merge($abilities, $cur_group->abilities);
					}
					unset($cur_group);
					if (isset($user->group)) {
						$abilities = array_merge($abilities, $user->group->abilities);
					}
				}
				self::$gatekeeperCache[$user->guid] = $abilities;
			}
		}
		if (!isset($user) || ((array) $abilities !== $abilities)) {
			return false;
		}
		return (in_array($ability, $abilities) || in_array('system/all', $abilities));
	}

	/**
	 * Sort an array of groups hierarchically.
	 *
	 * An additional property of the groups can be used to sort them under their
	 * parents.
	 *
	 * @param array &$array The array of groups.
	 * @param string|null $property The name of the property to sort groups by. Null for no additional sorting.
	 * @param bool $case_sensitive Sort case sensitively.
	 * @param bool $reverse Reverse the sort order.
	 */
	public static function groupSort(&$array, $property = null, $case_sensitive = false, $reverse = false) {
		\Nymph\Nymph::hsort($array, $property, 'parent', $case_sensitive, $reverse);
	}

	/**
	 * Creates and attaches a module which lists groups.
	 *
	 * @param bool $enabled Show enabled groups if true, disabled if false.
	 * @return module The module.
	 */
	public static function listGroups($enabled = true) {
		$module = new module('com_user', 'list_groups', 'content');

		$module->enabled = $enabled;
		if ($enabled) {
			$module->groups = \Nymph\Nymph::getEntities(['class' => '\Tilmeld\Group'], ['&', 'tag' => 'enabled']);
		} else {
			$module->groups = \Nymph\Nymph::getEntities(['class' => '\Tilmeld\Group'], ['!&', 'tag' => 'enabled']);
		}

		if (empty($module->groups)) {
			pines_notice('There are no'.($enabled ? ' enabled' : ' disabled').' groups.');
		}

		return $module;
	}

	/**
	 * Creates and attaches a module which lists users.
	 *
	 * @param bool $enabled Show enabled users if true, disabled if false.
	 * @return module The module.
	 */
	public static function listUsers($enabled = true) {
		$module = new module('com_user', 'list_users', 'content');

		$module->enabled = $enabled;
		if ($enabled) {
			$module->users = \Nymph\Nymph::getEntities(['class' => '\Tilmeld\User'], ['&', 'tag' => 'enabled']);
		} else {
			$module->users = \Nymph\Nymph::getEntities(['class' => '\Tilmeld\User'], ['!&', 'tag' => 'enabled']);
		}

		if (empty($module->users)) {
			pines_notice('There are no'.($enabled ? ' enabled' : ' disabled').' users.');
		}

		return $module;
	}

	/**
	 * Logs the given user into the system.
	 *
	 * @param user $user The user.
	 * @return bool True on success, false on failure.
	 */
	public static function login($user) {
		if ( isset($user->guid) && $user->hasTag('enabled') && self::gatekeeper('com_user/login', $user) ) {
			// Destroy session data.
			self::logout();
			self::session('write');
			$_SESSION['user_id'] = $user->guid;
			self::fillSession();
			self::session('close');
			return true;
		}
		return false;
	}

	/**
	 * Logs the current user out of the system.
	 */
	public static function logout() {
		self::session('write');
		unset($_SESSION['user_id']);
		unset($_SESSION['user']);
		// We're changing users, so clear the gatekeeper cache.
		self::$gatekeeperCache = [];
		self::session('destroy');
	}

	/**
	 * Creates and attaches a module which lets the user log in.
	 *
	 * @param string $position The position in which to place the module.
	 * @param string $url An optional url to redirect to after login.
	 * @return module The new module.
	 */
	public static function printLogin($position = 'content', $url = null) {
		$module = new module('com_user', 'modules/login', $position);
		$module->url = $url;
		if (isset($_REQUEST['url'])) {
			$module->url = $_REQUEST['url'];
		}
		return $module;
	}

	/**
	 * Open, close, or destroy sessions.
	 *
	 * Using this method, you can access an existing session for reading or
	 * writing, and close or destroy it.
	 *
	 * Providing a method to open a session for reading allows asynchronous
	 * calls to the app to work efficiently. PHP will not block during page
	 * requests, so one page taking forever to load doesn't grind a user's whole
	 * session to a halt.
	 *
	 * This method should be the only method sessions are accessed in 2be.
	 * This will allow maximum compatibility between components.
	 *
	 * $option can be one of the following:
	 *
	 * - "read" - Open the session for reading.
	 * - "write" - Open the session for writing. Remember to close it when you
	 *   no longer need write access.
	 * - "close" - Close the session for writing. The session is still readable
	 *   afterward.
	 * - "destroy" - Unset and destroy the session.
	 *
	 * @param string $option The type of access or action requested.
	 */
	public function session($option = 'read') {
		switch ($option) {
			case 'read':
			default:
				if (isset($_SESSION['p_session_access'])) {
					return;
				}
				if ( session_start() ) {
					$_SESSION['p_session_access'] = true;
					@session_write_close();
				}
				break;
			case 'write':
				session_start();
				$_SESSION['p_session_access'] = true;
				break;
			case 'close':
				@session_write_close();
				break;
			case 'destroy':
				@session_unset();
				@session_destroy();
				break;
		}
	}
}

Tilmeld::$config = \SciActive\RequirePHP::_('TilmeldConfig');
