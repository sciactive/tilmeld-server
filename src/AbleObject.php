<?php namespace Tilmeld;
/**
 * able_object class.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/lgpl.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

/**
 * Entities which support abilities, such as users and groups.
 *
 * @package Tilmeld
 */
class AbleObject extends \Nymph\Entity {
  /**
   * Grant an ability.
   *
   * Abilities should be named following this form!!
   *
   *	 com_componentname/abilityname
   *
   * If it is a system ability (ie. not part of a component, substitute
   * "com_componentname" with "system". The system ability "all" means the
   * user has every ability available.
   *
   * @param string $ability The ability.
   */
  public function grant($ability) {
    if (!in_array($ability, $this->abilities)) {
      return $this->abilities = array_merge([$ability], $this->abilities);
    }
    return true;
  }

  /**
   * Revoke an ability.
   *
   * @param string $ability The ability.
   */
  public function revoke($ability) {
    if (in_array($ability, $this->abilities)) {
      return $this->abilities = array_values(array_diff($this->abilities, [$ability]));
    }
    return true;
  }
}