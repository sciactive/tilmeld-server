<?php
namespace Tilmeld;

/**
 * Check for the skip access control option and add AC selectors.
 *
 * @param array &$array An array of arguments. (The options array and the selectors.)
 * @param mixed $name Unused.
 * @param mixed &$object Unused.
 * @param mixed &$function Unused.
 * @param array &$data The callback data array.
 */
function TilmeldGetEntitiesHook(&$array, $name, &$object, &$function, &$data) {
  if (isset($array[0]['skip_ac']) && $array[0]['skip_ac']) {
    $data['Tilmeld_skip_ac'] = true;
  } else {
    // Add access control selectors
    Tilmeld::addAccessControlSelectors($array);
  }
}

/**
 * Filter entities being deleted for user permissions.
 *
 * @param array &$array An array of an entity or guid.
 */
function TilmeldCheckPermissionsDeleteHook(&$array) {
  $entity = $array[0];
  if ((int) $entity === $entity) {
    $entity = \Nymph\Nymph::getEntity($array[0]);
  }
  if ((object) $entity !== $entity) {
    $array = false;
    return;
  }
  // Test for permissions.
  if (!Tilmeld::checkPermissions($entity, Tilmeld::DELETE_ACCESS)) {
    $array = false;
  }
}

/**
 * Filter entities being returned for user permissions.
 *
 * @param array &$array An array of either an entity or another array of entities.
 * @param mixed $name Unused.
 * @param mixed &$object Unused.
 * @param mixed &$function Unused.
 * @param array &$data The callback data array.
 */
function TilmeldCheckPermissionsReturnHook(&$array, $name, &$object, &$function, &$data) {
  // TODO(hperrin): Is this necessary, after adding AC selectors?
  // if (isset($data['Tilmeld_skip_ac']) && $data['Tilmeld_skip_ac']) {
  //   return;
  // }
  // if (is_array($array[0])) {
  //   $is_array = true;
  //   $entities = &$array[0];
  // } else {
  //   $is_array = false;
  //   $entities = &$array;
  // }
  // foreach ($entities as $key => &$curEntity) {
  //   // Test for permissions.
  //   if (!Tilmeld::checkPermissions($curEntity, Tilmeld::READ_ACCESS)) {
  //     unset($entities[$key]);
  //   }
  // }
  // unset($curEntity);
}

/**
 * Filter entities being saved for user permissions.
 *
 * @param array &$array An array of an entity.
 */
function TilmeldCheckPermissionsSaveHook(&$array) {
  $entity = $array[0];
  if ((object) $entity !== $entity) {
    $array = false;
    return;
  }
  if (isset($array[1]) && $array[1] === 'skip_ac') {
    unset($array[1]);
    return;
  }
  // Test for permissions.
  if (!Tilmeld::checkPermissions($entity, Tilmeld::WRITE_ACCESS)) {
    $array = false;
  }
}

/**
 * Add the current user's "user", "group", and access control to a new entity.
 *
 * This occurs right before an entity is saved. It only alters the entity if:
 * - There is a user logged in.
 * - The entity is new (doesn't have a GUID.)
 * - The entity is not a user or group.
 *
 * If you want a new entity to have a different user and/or group than the
 * current user, you must first save it to the database, then change the
 * user/group, then save it again.
 *
 * Default access control is
 * - ac_user = Tilmeld::DELETE_ACCESS
 * - ac_group = Tilmeld::READ_ACCESS
 * - ac_other = Tilmeld::NO_ACCESS
 *
 * @param array &$array An array of either an entity or another array of entities.
 */
function TilmeldAddAccessHook(&$array) {
  $user = Entities\User::current();
  if (
      $user !== null
      && !isset($array[0]->guid)
      && !is_a($entity, '\Tilmeld\Entities\User')
      && !is_a($entity, '\Tilmeld\Entities\Group')
      && !is_a($entity, '\SciActive\HookOverride_Tilmeld_Entities_User')
      && !is_a($entity, '\SciActive\HookOverride_Tilmeld_Entities_Group')
    ) {
    $array[0]->user = $user;
    if (isset($user->group) && isset($user->group->guid)) {
  		$array[0]->group = $user->group;
    }
    if (!isset($array[0]->ac_user)) {
      $array[0]->ac_user = Tilmeld::DELETE_ACCESS;
    }
    if (!isset($array[0]->ac_group)) {
      $array[0]->ac_group = Tilmeld::READ_ACCESS;
    }
    if (!isset($array[0]->ac_other)) {
      $array[0]->ac_other = Tilmeld::NO_ACCESS;
    }
  }
}

foreach (array('Nymph->getEntity', 'Nymph->getEntities') as $curHook) {
  \SciActive\Hook::addCallback($curHook, -10, '\Tilmeld\TilmeldGetEntitiesHook');
  // \SciActive\Hook::addCallback($curHook, 10, '\Tilmeld\TilmeldCheckPermissionsReturnHook');
}
unset($curHook);

\SciActive\Hook::addCallback('Nymph->saveEntity', -100, '\Tilmeld\TilmeldAddAccessHook');
\SciActive\Hook::addCallback('Nymph->saveEntity', -99, '\Tilmeld\TilmeldCheckPermissionsSaveHook');

foreach (array('Nymph->deleteEntity', 'Nymph->deleteEntityById') as $curHook) {
  \SciActive\Hook::addCallback($curHook, -99, '\Tilmeld\TilmeldCheckPermissionsDeleteHook');
}
unset($curHook);
