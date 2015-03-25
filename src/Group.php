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
	const etype = 'group';
	protected $tags = ['enabled'];

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
		$this->abilities = [];
		$this->conditions = [];
		$this->address_type = 'us';
		$this->attributes = [];
	}

	public function info($type) {
		switch ($type) {
			case 'name':
				return "$this->name [$this->groupname]";
			case 'type':
				return 'group';
			case 'types':
				return 'groups';
			default:
				return parent::info($type);
		}
		return null;
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
		$entities = \Nymph\Nymph::getEntities(
				['class' => '\Tilmeld\Group'],
				['&',
					'ref' => ['parent', $this]
				]
			);
		foreach ($entities as $cur_group) {
			if ( !$cur_group->delete() ) {
				return false;
			}
		}
		if (!parent::delete()) {
			return false;
		}
		return true;
	}

	/**
	 * Disable the group.
	 */
	public function disable() {
		$this->removeTag('enabled');
	}

	/**
	 * Enable the group.
	 */
	public function enable() {
		$this->addTag('enabled');
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
	public function get_children() {
		$return = (array) \Nymph\Nymph::getEntities(
				['class' => '\Tilmeld\Group'],
				['&',
					'tag' => 'enabled',
					'ref' => ['parent', $this]
				]
			);
		return $return;
	}

	/**
	 * Gets an array of the group's descendant groups.
	 *
	 * @param bool $and_self Include this group in the returned array.
	 * @return array An array of groups.
	 */
	public function get_descendants($and_self = false) {
		$return = [];
		$entities = \Nymph\Nymph::getEntities(
				['class' => '\Tilmeld\Group'],
				['&',
					'tag' => 'enabled',
					'ref' => ['parent', $this]
				]
			);
		foreach ($entities as $entity) {
			$child_array = $entity->get_descendants(true);
			$return = array_merge($return, $child_array);
		}
		$hooked = $this;
		$class = get_class();
		\SciActive\Hook::hookObject($hooked, $class.'->', false);
		if ($and_self) {
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
	public function get_level() {
		$group = $this;
		$level = 0;
		while (isset($group->parent) && $group->parent->hasTag('enabled')) {
			$level++;
			$group = $group->parent;
		}
		return $level;
	}

	/**
	 * Find the location of the group's current logo image.
	 *
	 * @param bool $full Return a full URL, instead of a relative one.
	 * @return string The URL of the logo image.
	 */
	public function get_logo($full = false) {
		if (isset($this->logo)) {
			return $full ? $_->uploader->url($_->uploader->real($this->logo), true) : $this->logo;
		}
		if (isset($this->parent) && $this->parent->hasTag('enabled')) {
			return $this->parent->get_logo($full);
		}
		return ($full ? $_->config->full_location : $_->config->location)."{$_->config->upload_location}logos/default_logo.png";
	}

	/**
	 * Gets an array of users in the group.
	 *
	 * Some user managers may return only enabled users.
	 *
	 * @param bool $descendants Include users in all descendant groups too.
	 * @return array An array of users.
	 */
	public function get_users($descendants = false) {
		if ($descendants) {
			$groups = $this->get_descendants();
		} else {
			$groups = [];
		}
		$groups[] = $this;
		$return = \Nymph\Nymph::getEntities(
				['class' => '\Tilmeld\User'],
				['&',
					'tag' => ['enabled']
				],
				['|',
					'ref' => [
						['group', $groups],
						['groups', $groups]
					]
				]
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
		$module->display_conditions = gatekeeper('com_user/conditions');
		$module->sections = ['system'];
		$module->group_array = \Nymph\Nymph::getEntities(['class' => '\Tilmeld\Group'], ['&', 'tag' => 'enabled']);
		foreach ($_->components as $cur_component) {
			$module->sections[] = $cur_component;
		}

		return $module;
	}
}