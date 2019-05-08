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
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @see http://tilmeld.org/
 */
class Tilmeld {
  const VERSION = '1.0.0-beta.32';

  const NO_ACCESS = 0;
  const READ_ACCESS = 1;
  const WRITE_ACCESS = 2;
  const FULL_ACCESS = 4;

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
   * The server's timezone.
   *
   * @var string|null
   * @access private
   */
  private static $serverTimezone;

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
   * to only entities the current user has access to.
   *
   * @param array &$optionsAndSelectors The options and selectors of the query.
   */
  public static function addAccessControlSelectors(&$optionsAndSelectors) {
    $user = self::$currentUser;

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
          '!isset' => [
            'user',
            'group'
          ]
        ],
        // It is owned by the user.
        ['&',
          'ref' => ['user', $user],
          'gte' => ['acUser', Tilmeld::READ_ACCESS]
        ],
        // The user is listed in acRead, acWrite, or acFull.
        'ref' => [
          ['acRead', $user],
          ['acWrite', $user],
          ['acFull', $user]
        ]
      ];
      $groupRefs = [];
      $acRefs = [];
      if (isset($user->group) && isset($user->group->guid)) {
        // It belongs to the user's primary group.
        $groupRefs[] = ['group', $user->group];
        // User's primary group is listed in acRead, acWrite, or acFull.
        $acRefs[] = ['acRead', $user->group];
        $acRefs[] = ['acWrite', $user->group];
        $acRefs[] = ['acFull', $user->group];
      }
      foreach ($user->groups as $curSecondaryGroup) {
        if (isset($curSecondaryGroup) && isset($curSecondaryGroup->guid)) {
          // It belongs to the user's secondary group.
          $groupRefs[] = ['group', $curSecondaryGroup];
          // User's secondary group is listed in acRead, acWrite, or acFull.
          $acRefs[] = ['acRead', $curSecondaryGroup];
          $acRefs[] = ['acWrite', $curSecondaryGroup];
          $acRefs[] = ['acFull', $curSecondaryGroup];
        }
      }
      foreach ($user->getDescendantGroups() as $curDescendantGroup) {
        if (isset($curDescendantGroup) && isset($curDescendantGroup->guid)) {
          // It belongs to the user's secondary group.
          $groupRefs[] = ['group', $curDescendantGroup];
          // User's secondary group is listed in acRead, acWrite, or acFull.
          $acRefs[] = ['acRead', $curDescendantGroup];
          $acRefs[] = ['acWrite', $curDescendantGroup];
          $acRefs[] = ['acFull', $curDescendantGroup];
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
      // All the acRead, acWrite, and acFull refs.
      if (!empty($acRefs)) {
        $selector[] = ['|',
          'ref' => $acRefs
        ];
      }
      $optionsAndSelectors[] = $selector;
    }
  }

  /**
   * Check an entity's permissions for a user.
   *
   * This will check the AC (Access Control) properties of the entity. These
   * include the following properties:
   *
   * - acUser
   * - acGroup
   * - acOther
   * - acRead
   * - acWrite
   * - acFull
   *
   * "acUser" refers to the entity's owner, "acGroup" refers to all users in the
   * entity's group and all ancestor groups, and "acOther" refers to any user
   * who doesn't fit these descriptions.
   *
   * Each of these properties should be either NO_ACCESS, READ_ACCESS,
   * WRITE_ACCESS, or FULL_ACCESS.
   *
   * - NO_ACCESS - the user has no access to the entity.
   * - READ_ACCESS, the user has read access to the entity.
   * - WRITE_ACCESS, the user has read and write access to the entity, but can't
   *   delete it, change its access controls, or change its ownership.
   * - FULL_ACCESS, the user has read, write, and delete access to the entity,
   *   as well as being able to manage its access controls and ownership.
   *
   * These properties defaults to:
   *
   * - acUser = Tilmeld::FULL_ACCESS
   * - acGroup = Tilmeld::READ_ACCESS
   * - acOther = Tilmeld::NO_ACCESS
   *
   * "acRead", "acWrite", and "acFull" are arrays of users and/or groups that
   * also have those permissions.
   *
   * Only users with FULL_ACCESS have the ability to change any of the ac*,
   * user, and group properties.
   *
   * The following conditions will result in different checks, which determine
   * whether the check passes:
   *
   * - The user has the "system/admin" ability. (Always true.)
   * - It is a user or group. (True for READ_ACCESS or Tilmeld admins.)
   * - The entity has no "user" and no "group". (Always true.)
   * - No user is logged in. (Check other AC.)
   * - The entity is the user. (Always true.)
   * - It is the user's primary group. (True for READ_ACCESS.)
   * - The user or its groups are listed in "acRead". (True for READ_ACCESS.)
   * - The user or its groups are listed in "acWrite". (True for READ_ACCESS and
   *   WRITE_ACCESS.)
   * - The user or its groups are listed in "acFull". (Always true.)
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
   *                  Tilmeld::FULL_ACCESS.
   * @param \Tilmeld\Entities\User|null $user The user to check permissions for.
   *                                          If null, uses the current user. If
   *                                          false, checks for public access.
   * @return bool Whether the current user has at least $type permission for the
   *              entity.
   */
  public static function checkPermissions(
      &$entity,
      $type = Tilmeld::READ_ACCESS,
      $user = null
  ) {
    // Only works for entities.
    if (!is_object($entity) || !is_callable([$entity, 'is'])) {
      return false;
    }

    // Calculate the user.
    if ($user === null) {
      $userOrNull = self::$currentUser;
      $userOrEmpty = User::current(true);
    } elseif ($user === false) {
      $userOrNull = null;
      $userOrEmpty = User::factory();
    } else {
      $userOrNull = $userOrEmpty = $user;
    }

    if ($userOrEmpty->gatekeeper('system/admin')) {
      return true;
    }

    // Users and groups are always readable. Editable by Tilmeld admins.
    if ((
          is_a($entity, '\Tilmeld\Entities\User')
          || is_a($entity, '\Tilmeld\Entities\Group')
          || is_a($entity, '\SciActive\HookOverride_Tilmeld_Entities_User')
          || is_a($entity, '\SciActive\HookOverride_Tilmeld_Entities_Group')
        )
        && (
          $type === Tilmeld::READ_ACCESS
          || $userOrEmpty->gatekeeper('tilmeld/admin')
        )
      ) {
      return true;
    }

    // Entities with no owners are always editable.
    if (!isset($entity->user) && !isset($entity->group)) {
      return true;
    }

    // Load access control, since we need it now...
    $acUser = $entity->acUser ?? Tilmeld::FULL_ACCESS;
    $acGroup = $entity->acGroup ?? Tilmeld::READ_ACCESS;
    $acOther = $entity->acOther ?? Tilmeld::NO_ACCESS;

    if ($userOrNull === null) {
      return ($acOther >= $type);
    }

    // Check if the entity is the user.
    if ($userOrEmpty->is($entity)) {
      return true;
    }

    // Check if the entity is the user's group. Always readable.
    if (isset($userOrEmpty->group)
        && is_callable([$userOrEmpty->group, 'is'])
        && $userOrEmpty->group->is($entity)
        && $type === Tilmeld::READ_ACCESS
      ) {
      return true;
    }

    // Calculate all the groups the user belongs to.
    $allGroups = isset($userOrEmpty->group) ? [$userOrEmpty->group] : [];
    $allGroups = array_merge($allGroups, $userOrEmpty->groups);
    $allGroups = array_merge($allGroups, $userOrEmpty->getDescendantGroups());

    // Check access ac properties.
    $checks = [
      ['type' => Tilmeld::FULL_ACCESS, 'array' => (array) $entity->acFull],
      ['type' => Tilmeld::WRITE_ACCESS, 'array' => (array) $entity->acWrite],
      ['type' => Tilmeld::READ_ACCESS, 'array' => (array) $entity->acRead]
    ];
    foreach ($checks as $curCheck) {
      if ($type <= $curCheck['type']) {
        if ($userOrEmpty->inArray($curCheck['array'])) {
          return true;
        }
        foreach ($allGroups as $curGroup) {
          if (is_callable([$curGroup, 'inArray'])
              && $curGroup->inArray($curCheck['array'])
            ) {
            return true;
          }
        }
      }
    }

    // Check ownership ac properties.
    if (is_callable([$entity->user, 'is'])
        && $entity->user->is($userOrNull)
      ) {
      return ($acUser >= $type);
    }
    if (is_callable([$entity->group, 'is'])
        && $entity->group->inArray($allGroups)
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
    if (!isset(self::$serverTimezone)) {
      self::$serverTimezone = date_default_timezone_get();
    }
    // Read groups right now, since gatekeeper needs them, so
    // udpateDataProtection will fail to read them (since it runs gatekeeper).
    isset($user->group);
    isset($user->groups);
    self::$currentUser = $user;
    date_default_timezone_set(self::$currentUser->getTimezone());
    // Now update the data protection on the user and all the groups.
    self::$currentUser->updateDataProtection();
    if (isset(self::$currentUser->group)) {
      self::$currentUser->group->updateDataProtection();
    }
    foreach (self::$currentUser->groups as $group) {
      $group->updateDataProtection();
    }
  }

  /**
   * Clear session user data.
   *
   * Also sets the default timezone to the server default.
   */
  public static function clearSession() {
    $user = self::$currentUser;
    self::$currentUser = null;
    if (isset(self::$serverTimezone)) {
      date_default_timezone_set(self::$serverTimezone);
    }
    if ($user) {
      $user->updateDataProtection();
    }
  }

  /**
   * Validate and extract the user from a token.
   *
   * @param string $token The authentication token.
   * @return \Tilmeld\Entities\User|bool The user on success, false on failure.
   */
  public static function extractToken($token) {
    $extract = self::$config['jwt_extract']($token);
    if (!$extract) {
      return false;
    }
    $guid = $extract['guid'];

    $user = Nymph::getEntity(
        ['class' => '\Tilmeld\Entities\User'],
        ['&',
          'guid' => $guid
        ]
    );
    if (!$user || !$user->guid || !$user->enabled) {
      return false;
    }

    return $user;
  }

  /**
   * Check for a TILMELDAUTH cookie, and, if set, authenticate from it.
   *
   * @return bool True if a user was authenticated, false on any failure.
   */
  public static function authenticate() {
    // If a client does't support cookies, they can use the X-TILMELDAUTH header
    // to provide the auth token.
    if (!empty($_SERVER['HTTP_X_TILMELDAUTH']) && empty($_COOKIE['TILMELDAUTH'])) {
      $fromAuthHeader = true;
      $authToken = $_SERVER['HTTP_X_TILMELDAUTH'];
    } elseif (!empty($_COOKIE['TILMELDAUTH'])) {
      $fromAuthHeader = false;
      $authToken = $_COOKIE['TILMELDAUTH'];
    } else {
      return false;
    }
    $setupUrlParts = parse_url(self::$config['setup_url']);
    $setupHost = $setupUrlParts['host'].
      (array_key_exists('port', $setupUrlParts) ? ':'.$setupUrlParts['port'] : '');
    if ($_SERVER['HTTP_HOST'] === $setupHost
        && $_SERVER['REQUEST_URI'] === $setupUrlParts['path']
      ) {
      // The request is for the setup app, so don't check for the XSRF token.
      $extract = self::$config['jwt_extract']($authToken);
    } else {
      // The request is for something else, so check for a valid XSRF token.
      if (empty($_SERVER['HTTP_X_XSRF_TOKEN'])) {
        return false;
      }

      $extract = self::$config['jwt_extract'](
          $authToken,
          $_SERVER['HTTP_X_XSRF_TOKEN']
      );
    }

    if (!$extract) {
      self::logout();
      return false;
    }
    $guid = $extract['guid'];
    $expire = $extract['expire'];

    $user = User::factory($guid);
    if (!$user || !$user->guid || !$user->enabled) {
      self::logout();
      return false;
    }

    if ($expire < time() + self::$config['jwt_renew']) {
      // If the user is less than renew time from needing a new token, give them
      // a new one.
      self::login($user, $fromAuthHeader);
    } else {
      self::fillSession($user);
    }
    return true;
  }

  /**
   * Logs the given user into the system.
   *
   * @param \Tilmeld\Entities\User $user The user.
   * @param bool $alwaysSendAuthHeader When true, a custom header with the auth
   *                                   token will be sent.
   * @return bool True on success, false on failure.
   */
  public static function login($user, $sendAuthHeader) {
    if (isset($user->guid) && $user->enabled) {
      $token = self::$config['jwt_builder']($user);
      $appUrlParts = parse_url(self::$config['app_url']);
      setcookie(
          'TILMELDAUTH',
          $token,
          time() + self::$config['jwt_expire'],
          $appUrlParts['path'],
          $appUrlParts['host'],
          $appUrlParts['scheme'] === 'https',
          false // Allow JS access (for CSRF protection).
      );
      if ($sendAuthHeader) {
        header("X-TILMELDAUTH: $token");
      }
      self::fillSession($user);
      return true;
    }
    return false;
  }

  /**
   * Logs the current user out of the system.
   */
  public static function logout() {
    self::clearSession();
    $appUrlParts = parse_url(self::$config['app_url']);
    setcookie(
        'TILMELDAUTH',
        '',
        null,
        $appUrlParts['path'],
        $appUrlParts['host']
    );
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
