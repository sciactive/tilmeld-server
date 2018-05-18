<?php namespace Tilmeld;

use SciActive\Hook;
use Respect\Validation\Validator as v;

// phpcs:disable Squiz.WhiteSpace.ObjectOperatorSpacing.Before

/**
 * Hook Nymph methods.
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://tilmeld.org/
 */
class HookMethods {
  public static function setup() {
    // Check for the skip access control option and add AC selectors.
    $GetEntitiesHook = function (&$array, $name, &$object, &$function, &$data) {
      if (isset($array[0]['skip_ac']) && $array[0]['skip_ac']) {
        $data['TilmeldSkipAc'] = true;
      } else {
        $user = Tilmeld::$currentUser;
        if (isset($array[0]['source'])
            && (
              $array[0]['source'] === 'client' ||
              $array[0]['source'] === 'pubsub'
            )
          ) {
          if ($array[0]['source'] === 'pubsub') {
            if (isset($array[0]['token'])) {
              $user = Tilmeld::extractToken($array[0]['token']) ?: null;
            } else {
              $user = null;
            }
          }
          if ((!$user || !$user->gatekeeper('tilmeld/admin'))
              && (
                (
                  !Tilmeld::$config['enable_user_search']
                  && (
                    $array[0]['class'] === '\Tilmeld\Entities\User'
                    || $array[0]['class'] === 'Tilmeld\Entities\User'
                  )
                )
                || (
                  !Tilmeld::$config['enable_group_search']
                  && (
                    $array[0]['class'] === '\Tilmeld\Entities\Group'
                    || $array[0]['class'] === 'Tilmeld\Entities\Group'
                  )
                )
              )
              && (
                !isset($array[1])
                || !isset($array[1][0])
                || $array[1][0] !== '&'
                || (
                  !isset($array[1]['guid'])
                  && !isset($array[1]['strict'])
                )
                || (
                  isset($array[1]['guid'])
                  && !is_int($array[1]['guid'])
                )
                || (
                  isset($array[1]['strict'])
                  && (
                    !isset($array[1]['strict'][0])
                    || $array[1]['strict'][0] !== 'username'
                  )
                )
              )
            ) {
            // If the user is not specifically searching for a GUID or username,
            // and they're not allowed to search, it should fail.
            $array = false;
            return;
          }
        }
        // Add access control selectors
        Tilmeld::addAccessControlSelectors($array, $user);
      }
    };

    // Filter entities being deleted for user permissions.
    $CheckPermissionsDeleteHook = function (&$array) {
      $entity = $array[0];
      if (is_int($entity)) {
        $entity = \Nymph\Nymph::getEntity(
            ['class' => $array[1] ?? '\Nymph\Entity'],
            ['&', 'guid' => $array[0]]
        );
      }
      if (!is_object($entity)) {
        $array = false;
        return;
      }
      // Test for permissions.
      if (!Tilmeld::checkPermissions($entity, Tilmeld::FULL_ACCESS)) {
        $array = false;
      }
    };

    // TODO(hperrin): This is unnecessary now with AC selectors. Should it be
    // used for something else?
    // Filter entities being returned for user permissions.
    // $CheckPermissionsReturnHook = function (
    //     &$array,
    //     $name,
    //     &$object,
    //     &$function,
    //     &$data
    // ) {
    //   if (isset($data['TilmeldSkipAc']) && $data['TilmeldSkipAc']) {
    //     return;
    //   }
    //   if (is_array($array[0])) {
    //     $isArray = true;
    //     $entities = &$array[0];
    //   } else {
    //     $isArray = false;
    //     $entities = &$array;
    //   }
    //   foreach ($entities as $key => &$curEntity) {
    //     // Test for permissions.
    //     if (!Tilmeld::checkPermissions($curEntity, Tilmeld::READ_ACCESS)) {
    //       unset($entities[$key]);
    //     }
    //   }
    //   unset($curEntity);
    // };

    // Filter entities being saved for user permissions, and filter any
    // disallowed changes to AC properties.
    $CheckPermissionsSaveAndFilterAcChangesHook = function (&$array) {
      $entity = $array[0];
      if (!is_object($entity)) {
        $array = false;
        return;
      }
      if (is_callable([$array[0], 'tilmeldSaveSkipAC'])
          && $array[0]->tilmeldSaveSkipAC()
        ) {
        return;
      }

      if (isset($entity->guid)) {
        // If the entity is not new, check that the user has full access before
        // allowing a change to ac properties.

        $originalAc = $entity->getOriginalAcValues();
        $newAc = [
          'user' => $entity->user,
          'group' => $entity->group,
          'acUser' => $entity->acUser,
          'acGroup' => $entity->acGroup,
          'acOther' => $entity->acOther,
          'acRead' => $entity->acRead,
          'acWrite' => $entity->acWrite,
          'acFull' => $entity->acFull
        ];

        $setAcProperties = function ($acValues) use ($entity) {
          foreach ($acValues as $name => $value) {
            if (isset($value)) {
              $entity->$name = $value;
            } else {
              unset($entity->$name);
            }
          }
        };

        // Restore original AC properties and check permissions.
        $setAcProperties($originalAc);
        if (Tilmeld::checkPermissions($entity, Tilmeld::FULL_ACCESS)) {
          // Only allow changes to AC properties if the user has full access.
          $setAcProperties($newAc);
        }
      }

      // Test for permissions.
      if (!Tilmeld::checkPermissions($entity, Tilmeld::WRITE_ACCESS)) {
        $array = false;
      }
    };

    /*
     * Add the current user's "user", "group", and access control to new entity.
     *
     * This occurs right before an entity is saved. It only alters the entity
     * if:
     * - There is a user logged in.
     * - The entity is new (doesn't have a GUID.)
     * - The entity is not a user or group.
     *
     * If you want a new entity to have a different user and/or group than the
     * current user, you must first save it to the database, then change the
     * user/group, then save it again.
     *
     * Default access control is
     * - acUser = Tilmeld::FULL_ACCESS
     * - acGroup = Tilmeld::READ_ACCESS
     * - acOther = Tilmeld::NO_ACCESS
     */
    $AddAccessHook = function (&$array) {
      $user = Tilmeld::$currentUser;
      if ($user !== null
          && !isset($array[0]->guid)
          && !is_a($array[0], '\Tilmeld\Entities\User')
          && !is_a($array[0], '\Tilmeld\Entities\Group')
          && !is_a($array[0], '\SciActive\HookOverride_Tilmeld_Entities_User')
          && !is_a($array[0], '\SciActive\HookOverride_Tilmeld_Entities_Group')
        ) {
        $array[0]->user = $user;
        unset($array[0]->group);
        if (isset($user->group) && isset($user->group->guid)) {
          $array[0]->group = $user->group;
        }
        if (!isset($array[0]->acUser)) {
          $array[0]->acUser = Tilmeld::FULL_ACCESS;
        }
        if (!isset($array[0]->acGroup)) {
          $array[0]->acGroup = Tilmeld::READ_ACCESS;
        }
        if (!isset($array[0]->acOther)) {
          $array[0]->acOther = Tilmeld::NO_ACCESS;
        }
        if (!isset($array[0]->acRead)) {
          $array[0]->acRead = [];
        }
        if (!isset($array[0]->acWrite)) {
          $array[0]->acWrite = [];
        }
        if (!isset($array[0]->acFull)) {
          $array[0]->acFull = [];
        }
      }
    };

    $Validate = function (&$array) {
      if (!is_a($array[0], '\Tilmeld\Entities\User')
          && !is_a($array[0], '\Tilmeld\Entities\Group')
          && !is_a($array[0], '\SciActive\HookOverride_Tilmeld_Entities_User')
          && !is_a($array[0], '\SciActive\HookOverride_Tilmeld_Entities_Group')
        ) {
        $ownershipAcPropertyValidator = v::intType()->between(
            Tilmeld::NO_ACCESS,
            Tilmeld::FULL_ACCESS,
            true
        );
        $accessAcPropertyValidator = v::arrayType()->each(
            v::oneOf(
                v::instance('\Tilmeld\Entities\User'),
                v::instance('\Tilmeld\Entities\Group')
            )
        );

        try {
          v::notEmpty()
            ->attribute('user', v::instance('\Tilmeld\Entities\User'), false)
            ->attribute('group', v::instance('\Tilmeld\Entities\Group'), false)
            ->attribute('acUser', $ownershipAcPropertyValidator, false)
            ->attribute('acGroup', $ownershipAcPropertyValidator, false)
            ->attribute('acOther', $ownershipAcPropertyValidator, false)
            ->attribute('acRead', $accessAcPropertyValidator, false)
            ->attribute('acWrite', $accessAcPropertyValidator, false)
            ->attribute('acFull', $accessAcPropertyValidator, false)
            ->setName('entity')
            ->assert($array[0]->getValidatable());
        // phpcs:ignore Generic.Files.LineLength.TooLong
        } catch (\Respect\Validation\Exceptions\NestedValidationException $exception) {
          throw new \Tilmeld\Exceptions\BadDataException(
              $exception->getFullMessage()
          );
        }
      }
    };

    Hook::addCallback('Nymph->getEntity', -10, $GetEntitiesHook);
    Hook::addCallback('Nymph->getEntities', -10, $GetEntitiesHook);
    // Hook::addCallback('Nymph->getEntity', 10, $CheckPermissionsReturnHook);
    // Hook::addCallback('Nymph->getEntities', 10, $CheckPermissionsReturnHook);

    Hook::addCallback('Nymph->saveEntity', -100, $AddAccessHook);
    Hook::addCallback('Nymph->saveEntity', -90, $Validate);
    Hook::addCallback(
        'Nymph->saveEntity',
        -80,
        $CheckPermissionsSaveAndFilterAcChangesHook
    );

    Hook::addCallback('Nymph->deleteEntity', -99, $CheckPermissionsDeleteHook);
    Hook::addCallback(
        'Nymph->deleteEntityById',
        -99,
        $CheckPermissionsDeleteHook
    );
  }
}
