<?php namespace Tilmeld\Entities;

use Tilmeld\Tilmeld;
use Nymph\Nymph;

/**
 * Group class.
 *
 * Note: When delete() is called all descendants of this group will also be
 * deleted.
 *
 * @package Tilmeld
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 * @property int $guid The GUID of the group.
 * @property string $groupname The group's groupname.
 * @property string $name The group's name.
 * @property string $email The group's email address.
 * @property string $phone The group's telephone number.
 * @property string $addressType The group's address type. "us" or
 *                               "international".
 * @property string $addressStreet The group's address line 1 for US addresses.
 * @property string $addressStreet2 The group's address line 2 for US addresses.
 * @property string $addressCity The group's city for US addresses.
 * @property string $addressState The group's state abbreviation for US
 *                                addresses.
 * @property string $addressZip The group's ZIP code for US addresses.
 * @property string $addressInternational The group's full address for
 *                                        international addresses.
 * @property Group $parent The group's parent.
 * @property User|null $user If generate_primary is on, this will be the user
*                            who generated this group.
 */
class Group extends AbleObject {
  const ETYPE = 'tilmeld_group';
  const DEFAULT_PRIVATE_DATA = [
    'email',
    'phone',
    'addressType',
    'addressStreet',
    'addressStreet2',
    'addressCity',
    'addressState',
    'addressZip',
    'addressInternational',
    'abilities',
    'user',
  ];
  const DEFAULT_WHITELIST_DATA = [];
  protected $tags = [];
  public $clientEnabledMethods = [
    'checkGroupname',
    'checkEmail',
    'getAvatar',
    'getChildren',
    'getDescendants',
    'getLevel',
    'isDescendant',
  ];
  public static $clientEnabledStaticMethods = [
    'getPrimaryGroups',
    'getSecondaryGroups',
  ];
  protected $privateData = Group::DEFAULT_PRIVATE_DATA;
  public static $searchRestrictedData = Group::DEFAULT_PRIVATE_DATA;
  protected $whitelistData = Group::DEFAULT_WHITELIST_DATA;
  protected $whitelistTags = [];

  /**
   * This is explicitly used only during the registration proccess.
   *
   * @var bool
   * @access private
   */
  private $skipAcWhenSaving = false;

  /**
   * Load a group.
   *
   * @param int $id The ID of the group to load, 0 for a new group.
   * @param bool $skipUpdateDataProtectionOnNewEntity Used to load the session.
   */
  public function __construct($id = 0) {
    if ((is_int($id) && $id > 0) || is_string($id)) {
      if (is_int($id)) {
        $entity = Nymph::getEntity(
            ['class' => get_class($this)],
            ['&', 'guid' => $id]
        );
      } else {
        $entity = Nymph::getEntity(
            ['class' => get_class($this)],
            ['&', 'strict' => ['groupname', $id]]
        );
      }
      if (isset($entity)) {
        $this->guid = $entity->guid;
        $this->tags = $entity->tags;
        $this->putData($entity->getData(), $entity->getSData());
        return;
      }
    }
    // Defaults.
    $this->enabled = true;
    $this->abilities = [];
    $this->addressType = 'us';
    $this->updateDataProtection();
  }

  /**
   * Get all the groups that can be assigned as primary groups.
   * @param string|null $search A search query. If null, all will be returned.
   *                            Uses ilike on name and groupname.
   * @return array An array of the assignable primary groups.
   */
  public static function getPrimaryGroups($search = null) {
    return self::getAssignableGroups(
        $search,
        Tilmeld::$config['highest_primary']
    );
  }

  /**
   * Get all the groups that can be assigned as secondary groups.
   * @param string|null $search A search query. If null, all will be returned.
   *                            Uses ilike on name and groupname.
   * @return array An array of the assignable secondary groups.
   */
  public static function getSecondaryGroups($search = null) {
    return self::getAssignableGroups(
        $search,
        Tilmeld::$config['highest_secondary']
    );
  }

  private static function getAssignableGroups($search, $highestParent) {
    $assignableGroups = [];
    if ($search !== null) {
      $assignableGroups = Nymph::getEntities(
          ['class' => '\Tilmeld\Entities\Group'],
          ['&',
            'equal' => ['enabled', true]
          ],
          ['|',
            'ilike' => [
              ['name', $search],
              ['groupname', $search]
            ],
          ]
      );
      if ($highestParent != 0) {
        $assignableGroups = array_values(
            array_filter(
                $assignableGroups,
                function ($curGroup) use ($highestParent) {
                  while (isset($curGroup->parent) && $curGroup->parent->guid) {
                    if ($curGroup->parent->guid === $highestParent) {
                      return true;
                    }
                    $curGroup = $curGroup->parent;
                  }
                  return false;
                }
            )
        );
      }
    } else {
      if ($highestParent == 0) {
        $assignableGroups = Nymph::getEntities(
            ['class' => '\Tilmeld\Entities\Group'],
            ['&',
              'equal' => ['enabled', true]
            ]
        );
      } else {
        if ($highestParent > 0) {
          $highestParent = Group::factory($highestParent);
          if (isset($highestParent->guid)) {
            $assignableGroups = $highestParent->getDescendants();
          }
        }
      }
    }
    return $assignableGroups;
  }

  public function getAvatar() {
    $proto = $_SERVER['HTTPS'] ? 'https' : 'http';
    if (!isset($this->email) || empty($this->email)) {
      return $proto.'://secure.gravatar.com/avatar/?d=mm&s=40';
    }
    return $proto.'://secure.gravatar.com/avatar/'.
      md5(strtolower(trim($this->email))).'?d=identicon&s=40';
  }

  public function putData($data, $sdata = []) {
    $return = parent::putData($data, $sdata);
    $this->updateDataProtection();
    return $return;
  }

  /**
   * Update the data protection arrays for a user.
   *
   * @param \Tilmeld\Entities\User|null $user User to update protection for. If
   *                                          null, will use the currently
   *                                          logged in user.
   */
  public function updateDataProtection($user = null) {
    if (!isset($user)) {
      $user = User::current();
    }

    $this->privateData = self::DEFAULT_PRIVATE_DATA;
    $this->whitelistData = self::DEFAULT_WHITELIST_DATA;

    if (Tilmeld::$config['email_usernames']) {
      $this->privateData[] = 'groupname';
    }
    if ($user !== null && $user->gatekeeper('tilmeld/admin')) {
      // Users who can edit groups can see their data.
      $this->privateData = [];
      $this->whitelistData = false;
      return;
    }
    if (isset($this->user) && isset($user) && $this->user->is($user)) {
      // Users can see their group's data.
      $this->privateData = [];
    }
  }

  /**
   * Check whether the group is a descendant of a group.
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
    // Check to see if the group is a descendant of the given group.
    if (!isset($this->parent)) {
      return false;
    }
    if ($this->parent->is($group)) {
      return true;
    }
    if ($this->parent->isDescendant($group)) {
      return true;
    }
    return false;
  }

  /**
   * Gets an array of the group's child groups.
   *
   * @return array An array of groups.
   */
  public function getChildren() {
    $return = (array) Nymph::getEntities(
        ['class' => '\Tilmeld\Entities\Group'],
        ['&',
          'equal' => ['enabled', true],
          'ref' => ['parent', $this]
        ]
    );
    return $return;
  }

  /**
   * Gets an array of the group's descendant groups.
   *
   * @param bool $andSelf Include this group in the returned array.
   * @return array An array of groups.
   */
  public function getDescendants($andSelf = false) {
    $return = [];
    $entities = Nymph::getEntities(
        ['class' => '\Tilmeld\Entities\Group'],
        ['&',
          'equal' => ['enabled', true],
          'ref' => ['parent', $this]
        ]
    );
    foreach ($entities as $entity) {
      $childArray = $entity->getDescendants(true);
      $return = array_merge($return, $childArray);
    }
    $hooked = $this;
    if (class_exists('\SciActive\Hook')) {
      $class = get_class();
      \SciActive\Hook::hookObject($hooked, $class.'->', false);
    }
    if ($andSelf) {
      $return[] = $hooked;
    }
    return $return;
  }

  /**
   * Get the number of parents the group has.
   *
   * If the group is a top level group, this will return 0. If it is a child
   * of a top level group, this will return 1. If it is a grandchild of a top
   * level group, this will return 2, and so on.
   *
   * @return int The level of the group.
   */
  public function getLevel() {
    $group = $this;
    $level = 0;
    while (isset($group->parent) && $group->parent->enabled) {
      $level++;
      $group = $group->parent;
    }
    return $level;
  }

  /**
   * Gets an array of users in the group.
   *
   * @param bool $descendants Include users in all descendant groups too.
   * @return array An array of users.
   */
  public function getUsers($descendants = false) {
    if ($descendants) {
      $groups = $this->getDescendants();
      $or = ['|',
          'ref' => [
            ['group', $groups],
            ['groups', $groups]
          ]
        ];
    } else {
      $or = null;
    }
    $groups[] = $this;
    $return = Nymph::getEntities(
        ['class' => '\Tilmeld\Entities\User'],
        ['&',
          'equal' => ['enabled', true]
        ],
        $or
    );
    return $return;
  }

  /**
   * Check that a groupname is valid.
   *
   * @return array An associative array with a boolean 'result' entry and a
   *               'message' entry.
   */
  public function checkGroupname() {
    // Groupnames can either be constrained by username validation, or be an
    // email address.
    if (Tilmeld::$config['email_usernames']
        && $this->groupname === $this->email
      ) {
      return $this->checkEmail();
    }
    if (empty($this->groupname)) {
      return ['result' => false, 'message' => 'Please specify a groupname.'];
    }
    if (Tilmeld::$config['max_username_length'] > 0
        && strlen($this->groupname) > Tilmeld::$config['max_username_length']
      ) {
      return [
        'result' => false,
        'message' => 'Groupnames must not exceed '.
          Tilmeld::$config['max_username_length'].' characters.'
      ];
    }
    if (array_diff(
        str_split($this->groupname),
        str_split(Tilmeld::$config['valid_chars'])
    )) {
      return [
        'result' => false,
        'message' => Tilmeld::$config['valid_chars_notice']
      ];
    }
    if (!preg_match(Tilmeld::$config['valid_regex'], $this->groupname)) {
      return [
        'result' => false,
        'message' => Tilmeld::$config['valid_regex_notice']
      ];
    }
    $selector = ['&',
      'ilike' => [
        'groupname',
        str_replace(
            ['\\', '%', '_'],
            ['\\\\\\\\', '\%', '\_'],
            $this->groupname
        )
      ]
    ];
    if (isset($this->guid)) {
      $selector['!guid'] = $this->guid;
    }
    $test = Nymph::getEntity(
        ['class' => '\Tilmeld\Entities\Group', 'skip_ac' => true],
        $selector
    );
    if (isset($test->guid)) {
      return ['result' => false, 'message' => 'That groupname is taken.'];
    }

    return [
      'result' => true,
      'message' => (
        isset($this->guid) ? 'Groupname is valid.' : 'Groupname is available!'
      )
    ];
  }

  /**
   * Check that an email is unique.
   *
   * @return array An associative array with a boolean 'result' entry and a
   *               'message' entry.
   */
  public function checkEmail() {
    if ($this->email === '') {
      return ['result' => true, 'message' => ''];
    }
    if (empty($this->email)) {
      return ['result' => false, 'message' => 'Please specify a valid email.'];
    }
    if (!preg_match(
        '/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i',
        $this->email
    )) {
      return [
        'result' => false,
        'message' => 'Email must be a correctly formatted address.'
      ];
    }
    $selector = ['&',
      'ilike' => [
        'email',
        str_replace(['\\', '%', '_'], ['\\\\\\\\', '\%', '\_'], $this->email)
      ]
    ];
    if (isset($this->guid)) {
      $selector['!guid'] = $this->guid;
    }
    $test = Nymph::getEntity(
        ['class' => '\Tilmeld\Entities\Group', 'skip_ac' => true],
        $selector
    );
    if (isset($test->guid)) {
      return [
        'result' => false,
        'message' => 'That email address is already registered.'
      ];
    }

    return [
      'result' => true,
      'message' => (
        isset($this->guid) ? 'Email is valid.' : 'Email address is valid!'
      )
    ];
  }

  public function save() {
    if (!isset($this->groupname)) {
      return false;
    }

    // Formatting.
    $this->groupname = trim($this->groupname);
    $this->email = trim($this->email);
    $this->name = trim($this->name);
    $this->phone = trim($this->phone);

    // Verification.
    $unCheck = $this->checkGroupname();
    if (!$unCheck['result']) {
      throw new \Tilmeld\Exceptions\BadUsernameException($unCheck['message']);
    }
    if (!(Tilmeld::$config['email_usernames']
        && $this->groupname === $this->email)
      ) {
      $emCheck = $this->checkEmail();
      if (!$emCheck['result']) {
        throw new \Tilmeld\Exceptions\BadEmailException($emCheck['message']);
      }
    }

    // Validate group parent. Make sure it's not a descendant of this group.
    if (isset($this->parent) &&
        (
          !isset($this->parent->guid) ||
          $this->is($this->parent) ||
          $this->parent->isDescendant($this)
        )
      ) {
      throw new \Tilmeld\Exceptions\BadDataException(
          'Group parent can\'t be itself or descendant of itself.'
      );
    }

    try {
      Tilmeld::$config['validator_group']->assert($this->getValidatable());
      // phpcs:ignore Generic.Files.LineLength.TooLong
    } catch (\Respect\Validation\Exceptions\NestedValidationException $exception) {
      throw new \Tilmeld\Exceptions\BadDataException(
          $exception->getFullMessage()
      );
    }

    // Only one default primary group is allowed.
    if ($this->defaultPrimary) {
      $currentPrimary = Nymph::getEntity(
          ['class' => '\Tilmeld\Entities\Group'],
          ['&', 'equal' => ['defaultPrimary', true]]
      );
      if (isset($currentPrimary) && !$this->is($currentPrimary)) {
        $currentPrimary->defaultPrimary = false;
        if (!$currentPrimary->save()) {
          // phpcs:ignore Generic.Files.LineLength.TooLong
          throw new \Tilmeld\Exceptions\CouldNotChangeDefaultPrimaryGroupException(
              "Could not change new user primary group from ".
                "{$currentPrimary->groupname}."
          );
        }
      }
    }

    return parent::save();
  }

  /*
   * This should *never* be accessible on the client.
   */
  public function saveSkipAC() {
    $this->skipAcWhenSaving = true;
    return $this->save();
  }

  public function tilmeldSaveSkipAC() {
    if ($this->skipAcWhenSaving) {
      $this->skipAcWhenSaving = false;
      return true;
    }
    return false;
  }

  public function delete() {
    if (!Tilmeld::gatekeeper('tilmeld/admin')) {
      return false;
    }
    $entities = Nymph::getEntities(
        ['class' => '\Tilmeld\Entities\Group'],
        ['&',
          'ref' => ['parent', $this]
        ]
    );
    foreach ($entities as $curGroup) {
      if (!$curGroup->delete()) {
        return false;
      }
    }
    return parent::delete();
  }
}
