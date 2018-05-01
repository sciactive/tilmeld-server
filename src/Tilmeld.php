<?php namespace Tilmeld;

use SciActive\Hook;
use Nymph\Nymph;
use Tilmeld\Entities\User;
use Tilmeld\Entities\Group;

/**
 * Tilmeld main class.
 *
 * Provides an Nymph based user and group manager.
 *
 * @package Tilmeld
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
class Tilmeld {
  const VERSION = '1.0.0-beta.1';

  const NO_ACCESS = 0;
  const READ_ACCESS = 1;
  const WRITE_ACCESS = 2;
  const DELETE_ACCESS = 4;

  /**
   * The Tilmeld config array.
   *
   * @var array
   * @access public
   */
  public static $config;

  /**
   * The currently logged in user.
   *
   * @var \Tilmeld\Entities\User|null
   * @access public
   */
  public static $currentUser = null;

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
    if (!isset(self::$currentUser)) {
      return false;
    }
    return self::$currentUser->gatekeeper($ability);
  }

  /**
   * Apply configuration to Tilmeld.
   *
   * $config should be an associative array of Tilmeld configuration. Use the
   * following form:
   *
   * [
   *   'setup_url' => 'http://example.com/tilmeld/',
   *   'create_admin' => false
   * ]
   *
   * @param array $config An associative array of Tilmeld's configuration.
   */
  public static function configure($config = []) {
    $defaults = include dirname(__DIR__).'/conf/defaults.php';
    self::$config = array_replace($defaults, $config);

    // Set up access control hooks when Nymph is called.
    if (!isset(Nymph::$driver)) {
      throw new Exception('Tilmeld can\'t be configured before Nymph.');
    }
    HookMethods::setup();
    self::authenticate();
  }

  /**
   * Add selectors to a list of options and selectors which will limit results
   * to only entities the currently logged in user has access to.
   */
  public static function addAccessControlSelectors(&$optionsAndSelectors, $user = null) {
    if (!isset($user)) {
      $user = self::$currentUser;
    }

    if (isset($user) && $user->gatekeeper('system/admin')) {
      // The user is a system admin, so they can see everything.
      return;
    }

    if (!isset($optionsAndSelectors[0])) {
      throw new Exception('No options in argument.');
    } elseif (isset($optionsAndSelectors[0]['class'])
        && (
          $optionsAndSelectors[0]['class'] === '\Tilmeld\Entities\User'
          || $optionsAndSelectors[0]['class'] === '\Tilmeld\Entities\Group'
          || $optionsAndSelectors[0]['class'] === 'Tilmeld\Entities\User'
          || $optionsAndSelectors[0]['class'] === 'Tilmeld\Entities\Group'
        )
      ) {
      // They are requesting a user/group. Always accessible for reading.
      return;
    }

    if ($user === null) {
      $optionsAndSelectors[] = ['|',
        // Other access control is sufficient.
        'gte' => ['acOther', Tilmeld::READ_ACCESS],
        // The user and group are not set.
        ['&',
          '!isset' => ['user', 'group']
        ]
      ];
    } else {
      $selector = ['|',
        // Other access control is sufficient.
        'gte' => ['acOther', Tilmeld::READ_ACCESS],
        // The user and group are not set.
        ['&',
          '!isset' => ['user', 'group']
        ],
        // It is owned by the user.
        ['&',
          'ref' => ['user', $user],
          'gte' => ['acUser', Tilmeld::READ_ACCESS]
        ]
      ];
      $groupRefs = [];
      if (isset($user->group) && isset($user->group->guid)) {
        // It belongs to the user's primary group.
        $groupRefs[] = ['group', $user->group];
      }
      foreach ($user->groups as $curSecondaryGroup) {
        if (isset($curSecondaryGroup) && isset($curSecondaryGroup->guid)) {
          // It belongs to the user's secondary group.
          $groupRefs[] = ['group', $curSecondaryGroup];
        }
      }
      foreach ($user->getDescendantGroups() as $curDescendantGroup) {
        if (isset($curDescendantGroup) && isset($curDescendantGroup->guid)) {
          // It belongs to the user's secondary group.
          $groupRefs[] = ['group', $curDescendantGroup];
        }
      }
      // All the group refs.
      if (!empty($groupRefs)) {
        $selector[] = ['&',
          'gte' => ['acGroup', Tilmeld::READ_ACCESS],
          ['|',
            'ref' => $groupRefs
          ]
        ];
      }
      $optionsAndSelectors[] = $selector;
    }
  }

  /**
   * Check an entity's permissions for the currently logged in user.
   *
   * This will check the AC (Access Control) properties of the entity. These
   * include the following properties:
   *
   * - acUser
   * - acGroup
   * - acOther
   *
   * The property "acUser" refers to the entity's owner, "acGroup" refers to
   * all users in the entity's group and all ancestor groups, and "acOther"
   * refers to any user who doesn't fit these descriptions.
   *
   * Each property should be either NO_ACCESS, READ_ACCESS, WRITE_ACCESS, or
   * DELETE_ACCESS. If it is NO_ACCESS, the user has no access to the entity. If
   * it is READ_ACCESS, the user has read access to the entity. If it is
   * WRITE_ACCESS, the user has read and write access to the entity. If it is
   * DELETE_ACCESS, the user has read, write, and delete access to the entity.
   *
   * AC properties defaults to:
   *
   * - acUser = Tilmeld::DELETE_ACCESS
   * - acGroup = Tilmeld::READ_ACCESS
   * - acOther = Tilmeld::NO_ACCESS
   *
   * The following conditions will result in different checks, which determine
   * whether the check passes:
   *
   * - The user has the "system/admin" ability. (Always true.)
   * - It is a user or group. (Always true for read or Tilmeld admins.)
   * - The entity has no "user" and no "group". (Always true.)
   * - No user is logged in. (Check other AC.)
   * - The entity is the user. (Always true.)
   * - It is the user's primary group. (Always true for read.)
   * - Its "user" is the user. (It is owned by the user.) (Check user AC.)
   * - Its "group" is the user's primary group. (Check group AC.)
   * - Its "group" is one of the user's secondary groups. (Check group AC.)
   * - Its "group" is a descendant of one of the user's groups. (Check group
   *   AC.)
   * - None of the above. (Check other AC.)
   *
   * @param object &$entity The entity to check.
   * @param int $type The lowest level of permission to consider a pass. One of
   *                  Tilmeld::READ_ACCESS, Tilmeld::WRITE_ACCESS, or
   *                  Tilmeld::DELETE_ACCESS.
   * @return bool Whether the current user has at least $type permission for the
   *              entity.
   */
  public static function checkPermissions(
      &$entity,
      $type = Tilmeld::READ_ACCESS
  ) {
    $currentUserOrNull = self::$currentUser;
    $currentUserOrEmpty = User::current(true);

    if (!is_object($entity) || !is_callable([$entity, 'is'])) {
      return false;
    }
    if ($currentUserOrEmpty->gatekeeper('system/admin')) {
      return true;
    }
    if ((
          is_a($entity, '\Tilmeld\Entities\User')
          || is_a($entity, '\Tilmeld\Entities\Group')
          || is_a($entity, '\SciActive\HookOverride_Tilmeld_Entities_User')
          || is_a($entity, '\SciActive\HookOverride_Tilmeld_Entities_Group')
        )
        && (
          $type === Tilmeld::READ_ACCESS
          || Tilmeld::gatekeeper('tilmeld/admin')
        )
      ) {
      return true;
    }
    if ((!isset($entity->user) || !isset($entity->user->guid))
        && (!isset($entity->group) || !isset($entity->group->guid))
      ) {
      return true;
    }

    // Load access control, since we need it now...
    $acUser = $entity->acUser ?? Tilmeld::DELETE_ACCESS;
    $acGroup = $entity->acGroup ?? Tilmeld::READ_ACCESS;
    $acOther = $entity->acOther ?? Tilmeld::NO_ACCESS;

    if ($currentUserOrNull === null) {
      return ($acOther >= $type);
    }
    if ($currentUserOrEmpty->is($entity)) {
      return true;
    }
    if (isset($currentUserOrEmpty->group)
        && is_callable([$currentUserOrEmpty->group, 'is'])
        && $currentUserOrEmpty->group->is($entity)
        && $type === Tilmeld::READ_ACCESS
      ) {
      return true;
    }
    if (is_callable([$entity->user, 'is'])
        && $entity->user->is($currentUserOrNull)
      ) {
      return ($acUser >= $type);
    }
    if (is_callable([$entity->group, 'is'])
        && (
          $entity->group->is($currentUserOrEmpty->group) ||
          $entity->group->inArray($currentUserOrEmpty->groups) ||
          $entity->group->inArray($currentUserOrEmpty->getDescendantGroups())
        )
      ) {
      return ($acGroup >= $type);
    }
    return ($acOther >= $type);
  }

  /**
   * Fill session user data.
   *
   * Also sets the default timezone to the user's timezone.
   *
   * @param \Tilmeld\Entities\User $user The user.
   */
  public static function fillSession($user) {
    self::$currentUser = $user;
    date_default_timezone_set($user->getTimezone());
    self::$currentUser->updateDataProtection();
  }

  /**
   * Check for a TILMELDAUTH cookie, and, if set, authenticate from it.
   *
   * @return bool True if a user was authenticated, false on any failure.
   */
  public static function authenticate() {
    if (empty($_COOKIE['TILMELDAUTH'])) {
      return false;
    }

    $extract = self::$config['jwt_extract']($_COOKIE['TILMELDAUTH']);
    if (!$extract) {
      self::logout();
      return false;
    }
    $guid = $extract['guid'];
    $expire = $extract['expire'];

    $user = Nymph::getEntity(
        ['class' => '\Tilmeld\Entities\User'],
        ['&',
          'guid' => $guid
        ]
    );
    if (!$user || !$user->guid || !$user->enabled) {
      self::logout();
      return false;
    }

    if ($expire < time() + self::$config['jwt_renew']) {
      // If the user is less than renew time from needing a new token, give them
      // a new one.
      self::login($user);
    } else {
      self::fillSession($user);
    }
    return true;
  }

  /**
   * Logs the given user into the system.
   *
   * @param \Tilmeld\Entities\User $user The user.
   * @return bool True on success, false on failure.
   */
  public static function login($user) {
    if (isset($user->guid) && $user->enabled) {
      $token = self::$config['jwt_builder']($user);
      setcookie(
          'TILMELDAUTH',
          $token,
          time() + self::$config['jwt_expire'],
          '', // Use current path.
          '', // Use current domain.
          substr(self::$config['setup_url'], 0, 5) === 'https', // Secure if the user setup URL is.
          TRUE // Don't allow JS access
      );
      self::fillSession($user);
      return true;
    }
    return false;
  }

  /**
   * Logs the current user out of the system.
   */
  public static function logout() {
    self::$currentUser = null;
    setcookie('TILMELDAUTH', '');
  }

  /**
   * Sort an array of groups hierarchically.
   *
   * An additional property of the groups can be used to sort them under their
   * parents.
   *
   * @param array &$array The array of groups.
   * @param string|null $property The name of the property to sort groups by.
   *                              Null for no additional sorting.
   * @param bool $caseSensitive Sort case sensitively.
   * @param bool $reverse Reverse the sort order.
   */
  public static function groupSort(
      &$array,
      $property = null,
      $caseSensitive = false,
      $reverse = false
  ) {
    Nymph::hsort($array, $property, 'parent', $caseSensitive, $reverse);
  }
}
