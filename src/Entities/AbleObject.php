<?php namespace Tilmeld\Entities;

/**
 * AbleObject trait.
 *
 * Used in entities which support abilities, such as users and groups.
 *
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @see http://tilmeld.org/
 */
trait AbleObject {
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
      return $this->abilities = array_values(
        array_diff($this->abilities, [$ability])
      );
    }
    return true;
  }
}
