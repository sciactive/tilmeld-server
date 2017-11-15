<?php namespace Tilmeld\Entities;

/**
 * AbleObject class.
 *
 * Entities which support abilities, such as users and groups.
 *
 * @package Tilmeld
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
class AbleObject extends \Nymph\Entity {
  /**
   * Grant an ability.
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
