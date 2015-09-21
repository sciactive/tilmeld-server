<?php namespace Tilmeld;
/**
 * Tilmeld class.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/lgpl.html
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
	 * Check to see if the current user has an ability.
	 *
	 * If $ability is null, it will check to see if a user is currently logged
	 * in.
	 *
	 * @param string $ability The ability.
	 * @return bool True or false.
	 */
	public static function gatekeeper($ability = null) {
		/*******
		 * TODO: REMOVE THIS DEV CODE!!!
		 */
		return true;
		/*******
		 * YOU BETTER REMOVE IT!
		 */
		if (User::current() === null) {
			return false;
		}
		return User::current(true)->gatekeeper($ability);
	}

	/**
	 * Apply configuration to Tilmeld.
	 *
	 * $config should be an associative array of Tilmeld configuration. Use the
	 * following form:
	 *
	 * [
	 *     'setup_url' => 'http://example.com/tilmeld/',
	 *     'create_admin' => false
	 * ]
	 *
	 * @param array $config An associative array of Tilmeld's configuration.
	 */
	public static function configure($config = []) {
		\SciActive\RequirePHP::_('TilmeldConfig', [], function() use ($config){
			$defaults = include dirname(__DIR__).'/conf/defaults.php';
			$tilmeldConfig = [];
			foreach ($defaults as $curName => $curOption) {
				if ((array) $curOption === $curOption && isset($curOption['value'])) {
					$tilmeldConfig[$curName] = $curOption['value'];
				} else {
					$tilmeldConfig[$curName] = [];
					foreach ($curOption as $curSubName => $curSubOption) {
						$tilmeldConfig[$curName][$curSubName] = $curSubOption['value'];
					}
				}
			}
			return array_replace_recursive($tilmeldConfig, $config);
		});
		self::$config = \SciActive\RequirePHP::_('TilmeldConfig');
	}

	/**
	 * Activate the SAWASC system.
	 * @return bool True if SAWASC could be activated, false otherwise.
	 */
	public static function activateSawasc() {
		if (!self::$config['sawasc']) {
			return false;
		}
		if (self::$config['pw_method'] == 'salt') {
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
				'algo' => self::$config['sawasc_hash']
			];
			self::session('close');
		}
		return true;
	}

	/**
	 * Check an entity's permissions for the currently logged in user.
	 *
	 * This will check the AC (Access Control) properties of the entity. These
	 * include the following properties:
	 *
	 * - ac_user
	 * - ac_group
	 * - ac_other
	 *
	 * The property "ac_user" refers to the entity's owner, "ac_group" refers to
	 * all users in the entity's group and all ancestor groups, and "ac_other"
	 * refers to any user who doesn't fit these descriptions.
	 *
	 * Each variable should be either 0, 1, 2, or 3. If it is 0, the user has no
	 * access to the entity. If it is 1, the user has read access to the entity.
	 * If it is 2, the user has read and write access to the entity. If it is 3,
	 * the user has read, write, and delete access to the entity.
	 *
	 * AC properties defaults to:
	 *
	 * - ac_user = 3
	 * - ac_group = 3
	 * - ac_other = 0
	 *
	 * The following conditions will result in different checks, which determine
	 * whether the check passes:
	 *
	 * - No user is logged in. (Check other AC.)
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
		if (User::current(true)->gatekeeper('system/all')) {
			return true;
		}
		if (is_a($entity, '\Tilmeld\User') || is_a($entity, '\Tilmeld\Group')) {
			return true;
		}
		if (
				(!isset($entity->user) || !isset($entity->user->guid)) &&
				(!isset($entity->group) || !isset($entity->group->guid))
			) {
			return true;
		}

		// Load access control, since we need it now...
		$ac_user = isset($entity->ac_user) ? $entity->ac_user : 3;
		$ac_group = isset($entity->ac_group) ? $entity->ac_group : 3;
		$ac_other = isset($entity->ac_other) ? $entity->ac_other : 0;

		if (User::current() !== null) {
			return ($ac_other >= $type);
		}
		if (is_callable([$entity->user, 'is']) && $entity->user->is(User::current())) {
			return ($ac_user >= $type);
		}
		if (
				is_callable([$entity->group, 'is']) &&
				(
					$entity->group->is(User::current(true)->group) ||
					$entity->group->inArray(User::current(true)->groups) ||
					$entity->group->inArray($_SESSION['tilmeld_descendants'])
				)
			) {
			return ($ac_group >= $type);
		}
		return ($ac_other >= $type);
	}

	/**
	 * Fill the $_SESSION['tilmeld_user'] variable with the logged in user's data.
	 *
	 * Also sets the default timezone to the user's timezone.
	 *
	 * This must be called at the i11 position in the init script processing.
	 */
	public static function fillSession() {
		self::session('write');
		if (
				(object) $_SESSION['tilmeld_user'] === $_SESSION['tilmeld_user'] &&
				$_SESSION['tilmeld_user']->guid === $_SESSION['tilmeld_user_id']
			) {
			$tmp_user = \Nymph\Nymph::getEntity(
					['class' => '\Tilmeld\User'],
					['&',
						'guid' => [$_SESSION['tilmeld_user']->guid],
						'gt' => ['mdate', $_SESSION['tilmeld_user']->mdate]
					]
				);
			if (!isset($tmp_user)) {
				$_SESSION['tilmeld_user']->clearCache();
				date_default_timezone_set($_SESSION['tilmeld_user_timezone']);
				self::session('close');
				return;
			}
			unset($_SESSION['tilmeld_user']);
		} else {
			$tmp_user = User::factory($_SESSION['tilmeld_user_id']);
		}
		$_SESSION['tilmeld_user_timezone'] = $tmp_user->getTimezone();
		date_default_timezone_set($_SESSION['tilmeld_user_timezone']);
		if (isset($tmp_user->group)) {
			$_SESSION['tilmeld_descendants'] = (array) $tmp_user->group->getDescendants();
		}
		foreach ($tmp_user->groups as $cur_group) {
			$_SESSION['tilmeld_descendants'] = array_merge((array) $_SESSION['tilmeld_descendants'], (array) $cur_group->getDescendants());
		}
		if ($tmp_user->inheritAbilities) {
			$_SESSION['tilmeld_inherited_abilities'] = $tmp_user->abilities;
			foreach ($tmp_user->groups as $cur_group) {
				$_SESSION['tilmeld_inherited_abilities'] = array_merge($_SESSION['tilmeld_inherited_abilities'], $cur_group->abilities);
			}
			if (isset($tmp_user->group)) {
				$_SESSION['tilmeld_inherited_abilities'] = array_merge($_SESSION['tilmeld_inherited_abilities'], $tmp_user->group->abilities);
			}
		}
		$_SESSION['tilmeld_user'] = $tmp_user;
		self::session('close');
	}

	/**
	 * Sort an array of groups hierarchically.
	 *
	 * An additional property of the groups can be used to sort them under their
	 * parents.
	 *
	 * @param array &$array The array of groups.
	 * @param string|null $property The name of the property to sort groups by. Null for no additional sorting.
	 * @param bool $caseSensitive Sort case sensitively.
	 * @param bool $reverse Reverse the sort order.
	 */
	public static function groupSort(&$array, $property = null, $caseSensitive = false, $reverse = false) {
		\Nymph\Nymph::hsort($array, $property, 'parent', $caseSensitive, $reverse);
	}

	/**
	 * Logs the given user into the system.
	 *
	 * @param \Tilmeld\User $user The user.
	 * @return bool True on success, false on failure.
	 */
	public static function login($user) {
		if (isset($user->guid) && $user->enabled) {
			// Destroy session data.
			self::logout();
			self::session('write');
			$_SESSION['tilmeld_user_id'] = $user->guid;
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
		unset($_SESSION['tilmeld_user_id']);
		unset($_SESSION['tilmeld_user']);
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
	public static function session($option = 'read') {
		switch ($option) {
			case 'read':
			default:
				if (isset($_SESSION['tilmeld_session_access'])) {
					return;
				}
				if (session_start()) {
					$_SESSION['tilmeld_session_access'] = true;
					@session_write_close();
				}
				break;
			case 'write':
				session_start();
				$_SESSION['tilmeld_session_access'] = true;
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
