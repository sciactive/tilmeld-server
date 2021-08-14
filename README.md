# Tilmeld {#mainpage}

Nymph user and group management with access controls.

## Deprecation Notice

The PHP implementation of Nymph/Tilmeld has been deprecated. It will no longer have any new features added. Instead, a new version of Nymph running on Node.js, written entirely in TypeScript will replace the PHP implementation. You can find it over at the [Nymph.js repo](https://github.com/sciactive/nymphjs).

## Installation

### Automatic Setup

The fastest way to start building a Nymph app is with the [Nymph App Template](https://github.com/hperrin/nymph-template).

### Manual Installation

```sh
composer require sciactive/tilmeld-server
```

## How It Works

Tilmeld uses Nymph entities to store users and groups. It allows users to register and log in using the Nymph REST endpoint.

Tilmeld methods are available on the `Tilmeld\Tilmeld` class. (They are all static methods.)

### Users

User accounts can be created either in the setup app or by registering through the `register` function in PHP or the `$register` function in JS. There is a TilmeldLogin component in the `tilmeld-components` package that will build you a login/register form. The first user account registered in Tilmeld will be granted admin priveleges with the `system/admin` ability.

Users are available as the `Tilmeld\Entities\User` class.

### Groups

Groups are available as the `Tilmeld\Entities\Group` class.

#### Primary Groups

Users can have only one primary group. It becomes the group of any entities they create. By default, Tilmeld will create a new primary group for every user.

#### Secondary Groups

Secondary groups are used to grant users additional abilities or give access to entities.

### Access Controls

Tilmeld filters all calls to Nymph to allow users to only see and modify the entities they have access to. When a user creates an entity, their user becomes the `user` property of that entity, and their primary group becomes the `group` property. By default, entites will allow read/write/delete access to their user, read access to their group, and no access to other users.

You can use these constants for access control:

- `Tilmeld::FULL_ACCESS` - Read/Edit/Save/Change AC/Delete access.
- `Tilmeld::WRITE_ACCESS` - Read/Edit/Save access.
- `Tilmeld::READ_ACCESS` - Read access.
- `Tilmeld::NO_ACCESS` - No access.

The following properties are used on entities to control who has access:

- `$entity->user` - The `User` who owns the entity.
- `$entity->group` - The `Group` who owns the entity.
- `$entity->acUser` - What access control level the owner user has. Defaults to `Tilmeld::FULL_ACCESS`.
- `$entity->acGroup` - What access control level the owner group has. Defaults to `Tilmeld::READ_ACCESS`.
- `$entity->acOther` - What access control level everyone else has. Defaults to `Tilmeld::NO_ACCESS`.
- `$entity->acRead` - An array of users/groups who are granted `Tilmeld::READ_ACCESS`.
- `$entity->acWrite` - An array of users/groups who are granted `Tilmeld::WRITE_ACCESS`.
- `$entity->acFull` - An array of users/groups who are granted `Tilmeld::FULL_ACCESS`.

### Abilities

Abilities can be granted to users and/or their groups. When you call `gatekeeper`, it will check for the given ability.

The `system/admin` ability is special, and will cause `gatekeeper` to always return true for users with this ability. It will also let the user see, modify, and delete all entities, as if they had `Tilmeld::FULL_ACCESS`.

The `tilmeld/admin` ability allows the user to see the setup app and modify all users/groups except ones with the `system/admin` ability. Changes to a user's email by a Tilmeld admin do not require verification. A Tilmeld admin can't grant `system/admin` to a user or group, but they can assign groups, so don't grant a group the `system/admin` ability.

### Generated Primary Groups

Tilmeld, by default, is configured to generate a primary group for every new user. When the user is changed, that information is propagated to the group.

## API Docs

See the full API docs at https://tilmeld.org/api/server/latest
