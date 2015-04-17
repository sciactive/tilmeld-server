<?php namespace Tilmeld;
/**
 * Group class.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

/**
 * Group of users.
 *
 * Note: When delete() is called all descendants of this group will also be
 * deleted.
 *
 * @package Tilmeld
 * @property int $guid The GUID of the group.
 * @property string $groupname The group's groupname.
 * @property string $name The group's name.
 * @property string $email The group's email address.
 * @property string $phone The group's telephone number.
 * @property string $address_type The group's address type. "us" or "international".
 * @property string $address_1 The group's address line 1 for US addresses.
 * @property string $address_2 The group's address line 2 for US addresses.
 * @property string $city The group's city for US addresses.
 * @property string $state The group's state abbreviation for US addresses.
 * @property string $zip The group's ZIP code for US addresses.
 * @property string $address_international The group's full address for international addresses.
 * @property Group $parent The group's parent.
 */
class Group extends AbleObject {
	const ETYPE = 'group';
	protected $tags = [];
	public $clientEnabledMethods = [
		'getChildren',
		'getDescendants',
		'getLevel',
		'isDescendant',
	];
	protected $privateData = [
		'email',
		'phone',
		'address_type',
		'address_1',
		'address_2',
		'city',
		'state',
		'zip',
		'address_international',
		'abilities',
	];
	protected $whitelistData = [];
	protected $whitelistTags = [];

	/**
	 * Load a group.
	 *
	 * @param int $id The ID of the group to load, 0 for a new group.
	 */
	public function __construct($id = 0) {
		if ($id > 0 || (string) $id === $id) {
			if ((int) $id === $id) {
				$entity = \Nymph\Nymph::getEntity(['class' => get_class($this)], ['&', 'guid' => $id]);
			} else {
				$entity = \Nymph\Nymph::getEntity(['class' => get_class($this)], ['&', 'data' => ['groupname', $id]]);
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
		$this->address_type = 'us';
	}

	public function info($type) {
		switch ($type) {
			case 'name':
				return $this->name;
			case 'type':
				return 'group';
			case 'types':
				return 'groups';
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

	public function putData($data, $sdata = []) {
		$return = parent::putData($data, $sdata);
		$this->updateDataProtection();
		return $return;
	}

	public function updateDataProtection() {
		if (Tilmeld::$config->email_usernames['value']) {
			$this->privateData[] = 'groupname';
		}
		if (User::current(true)->gatekeeper('tilmeld/editgroups')) {
			// Users who can edit groups can see their data.
			$this->privateData = [];
			$this->whitelistData = false;
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

	public function delete() {
		if (!User::current(true)->gatekeeper('tilmeld/editgroups')) {
			return false;
		}
		$entities = \Nymph\Nymph::getEntities(
				['class' => '\Tilmeld\Group'],
				['&',
					'ref' => ['parent', $this]
				]
			);
		foreach ($entities as $cur_group) {
			if (!$cur_group->delete()) {
				return false;
			}
		}
		return parent::delete();
	}

	public function save() {
		if (!isset($this->groupname)) {
			return false;
		}
		return parent::save();
	}

	/**
	 * Gets an array of the group's child groups.
	 *
	 * @return array An array of groups.
	 */
	public function getChildren() {
		$return = (array) \Nymph\Nymph::getEntities(
				['class' => '\Tilmeld\Group'],
				['&',
					'data' => ['enabled', true],
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
		$entities = \Nymph\Nymph::getEntities(
				['class' => '\Tilmeld\Group'],
				['&',
					'data' => ['enabled', true],
					'ref' => ['parent', $this]
				]
			);
		foreach ($entities as $entity) {
			$child_array = $entity->getDescendants(true);
			$return = array_merge($return, $child_array);
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
		$return = \Nymph\Nymph::getEntities(
				['class' => '\Tilmeld\User'],
				['&',
					'data' => ['enabled', true]
				],
				$or
			);
		return $return;
	}

	/**
	 * Print a form to edit the group.
	 *
	 * @return module The form's module.
	 */
	public function printForm() {
		$module = new module('com_user', 'form_group', 'content');
		$module->entity = $this;
		$module->display_username = gatekeeper('com_user/usernames');
		$module->display_enable = gatekeeper('com_user/enabling');
		$module->display_default = gatekeeper('com_user/defaultgroups');
		$module->display_abilities = gatekeeper('com_user/abilities');
		$module->sections = ['system'];
		$module->group_array = \Nymph\Nymph::getEntities(['class' => '\Tilmeld\Group'], ['&', 'data' => ['enabled', true]]);
		foreach ($_->components as $cur_component) {
			$module->sections[] = $cur_component;
		}

		return $module;
	}
}