<?php
/**
 * Tilmeld's configuration defaults.
 *
 * @package Tilmeld
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

use Respect\Validation\Validator as v;

return [
  /*
   * Setup URL
   * The URL where the setup utility is accessible. This is also used for
   * email address verification.
   */
  'setup_url' => 'http://localhost/nymph/examples/examples/tilmeld/setup.php',
  /*
   * Create Admin
   * Allow the creation of an admin user. When a user is created, if there are
   * no other users in the system, he will be granted all abilities.
   */
  'create_admin' => true,
  /*
   * Use Email Address as Username
   * Instead of a "username", a user logs in and is referred to by their email
   * address. Enabling this after many users have been created can be messy.
   * Make sure they all have email addresses first.
   */
  'email_usernames' => true,
  /*
   * Allow User Registration
   * Allow users to register.
   */
  'allow_registration' => true,
  /*
   * Enable User Search
   * Whether frontend can search users. (Probably not a good idea if privacy is
   * a concern.)
   */
  'enable_user_search' => false,
  /*
   * Enable Group Search
   * Whether frontend can search groups. (Probably not a good idea if privacy is
   * a concern. Same risks as user search if generate_primary is true.)
   */
  'enable_group_search' => false,
  /*
   * User Account Fields
   * These will be the available fields for users. (Some fields, like username,
   * can't be excluded.)
   */
  'user_fields' => ['name', 'email', 'phone', 'timezone', 'address'],
  /*
   * Visible Registration Fields
   * These fields will be available for the user to fill in when they register.
   */
  'reg_fields' => ['name', 'email'],
  /*
   * Verify User Email Addresses
   * Verify users' email addresses upon registration/email change before
   * allowing them to log in/change it.
   */
  'verify_email' => true,
  /*
   * Verify Redirect URL
   * After the user verifies their address, redirect them to this URL.
   */
  'verify_redirect' => 'http://localhost/',
  /*
   * Unverified User Access
   * Unverified users will be able to log in, but will only have the "unverified
   * users" secondary group(s) until they verify their email. If set to false,
   * their account will instead be disabled until they verify.
   */
  'unverified_access' => true,
  /*
   * Rate Limit User Email Changes
   * Don't let users change their email address more often than this. You can
   * enter one value and one unit of time, such as "2 weeks". Leave blank to
   * disable rate limiting.
   */
  'email_rate_limit' => '1 day',
  /*
   * Allow Account Recovery
   * Allow users to recover their username and/or password through their
   * registered email.
   */
  'pw_recovery' => true,
  /*
   * Recovery Request Time Limit
   * How long a recovery request is valid.
   */
  'pw_recovery_time_limit' => '12 hours',
  /*
   * Password Storage Method
   * Method used to store passwords. Salt is more secure if the database is
   * compromised. Plain: store the password in plaintext. Digest: store the
   * password's digest. Salt: store the password's digest using a complex,
   * unique salt.
   *
   * Digests are SHA-256, so a salt probably isn't necessary, but who knows.
   *
   * Options are: "plain", "digest", "salt"
   */
  'pw_method' => 'salt',
  /*
   * Generate a Primary Group
   * Whether to create a new primary group for every user who registers. This
   * can be useful for providing access to entities the user creates.
   *
   * In the case this is set, the default primary group, rather than being
   * assigned to the user, is assigned as the parent of the generated group.
   */
  'generate_primary' => true,
  /*
   * Highest Assignable Primary Group Parent
   * The GUID of the group above the highest groups allowed to be assigned as
   * primary groups. Zero means all groups, and -1 means no groups.
   */
  'highest_primary' => 0,
  /*
   * Highest Assignable Secondary Group Parent
   * The GUID of the group above the highest groups allowed to be assigned as
   * secondary groups. Zero means all groups, and -1 means no groups.
   */
  'highest_secondary' => 0,
  /*
   * Valid Characters
   * Only these characters can be used when creating usernames and groupnames.
   * (Doesn't apply to emails as usernames.)
   */
  'valid_chars' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-.',
  /*
   * Valid Characters Notice
   * When a user enters an invalid name, this message will be displayed.
   */
  'valid_chars_notice' => 'Usernames and groupnames can only contain letters, numbers, underscore, dash, and period.',
  /*
   * Valid Regex
   * Usernames and groupnames must match this regular expression. (Doesn't apply
   * to emails as usernames.) By default, this ensures that the name begins and
   * ends with an alphanumeric. (To allow anything, use .* inside the slashes.)
   */
  'valid_regex' => '/^[a-zA-Z0-9].*[a-zA-Z0-9]$/',
  /*
   * Valid Regex Notice
   * When a user enters a name that doesn't match the regex, this message will
   * be displayed.
   */
  'valid_regex_notice' => 'Usernames and groupnames must begin and end with a letter or number.',
  /*
   * Username Max Length
   * The maximum length for usernames. 0 for unlimited.
   */
  'max_username_length' => 128,
  /*
   * Group Validator
   * The validator used to check groups before saving.
   */
  'validator_group' => v::notEmpty()
    ->attribute('groupname', v::stringType()->notBlank()->length(1, null))
    ->attribute('enabled', v::boolType())
    ->attribute('email', v::optional(v::email()), false)
    ->attribute('name', v::stringType()->notBlank()->prnt()->length(1, 256))
    ->attribute('phone', v::optional(v::phone()), false)
    ->attribute('addressType', v::optional(v::stringType()->in(['us','international'])), false)
    ->attribute('addressStreet', v::optional(v::stringType()->prnt()->length(1, 256)), false)
    ->attribute('addressStreet2', v::optional(v::stringType()->prnt()->length(1, 256)), false)
    ->attribute('addressCity', v::optional(v::stringType()->prnt()->length(1, 256)), false)
    ->attribute('addressState', v::optional(v::stringType()->alpha()->uppercase()->length(2, 2)), false)
    ->attribute('addressZip', v::optional(v::postalCode('US')), false)
    ->attribute('addressInternational', v::optional(v::stringType()->prnt()->length(1, 1024)), false)
    ->attribute('parent', v::when(v::nullType(), v::alwaysValid(), v::oneOf(v::instance('\Tilmeld\Entities\Group'), v::instance('\SciActive\HookOverride_Tilmeld_Entities_Group'))), false)
    ->attribute('user', v::when(v::nullType(), v::alwaysValid(), v::oneOf(v::instance('\Tilmeld\Entities\User'), v::instance('\SciActive\HookOverride_Tilmeld_Entities_User'))), false)
    ->attribute('abilities', v::arrayType()->each(v::stringType()->notBlank()->prnt()->length(1, 256)))
    ->attribute('defaultPrimary', v::when(v::nullType(), v::alwaysValid(), v::boolType()), false)
    ->attribute('defaultSecondary', v::when(v::nullType(), v::alwaysValid(), v::boolType()), false)
    ->setName('group object'),
  /*
   * User Validator
   * The validator used to check users before saving.
   */
  'validator_user' => v::notEmpty()
    ->attribute('username', v::stringType()->notBlank()->length(1, null))
    ->attribute('enabled', v::boolType())
    ->attribute('email', v::optional(v::email()), false)
    ->attribute('nameFirst', v::stringType()->notBlank()->prnt()->length(1, 256))
    ->attribute('nameMiddle', v::optional(v::stringType()->notBlank()->prnt()->length(1, 256)), false)
    ->attribute('nameLast', v::optional(v::stringType()->notBlank()->prnt()->length(1, 256)), false)
    ->attribute('name', v::stringType()->notBlank()->prnt()->length(1, 256))
    ->attribute('phone', v::optional(v::phone()), false)
    ->attribute('timezone', v::optional(v::in(\DateTimeZone::listIdentifiers())), false)
    ->attribute('addressType', v::optional(v::stringType()->in(['us','international'])), false)
    ->attribute('addressStreet', v::optional(v::stringType()->prnt()->length(1, 256)), false)
    ->attribute('addressStreet2', v::optional(v::stringType()->prnt()->length(1, 256)), false)
    ->attribute('addressCity', v::optional(v::stringType()->prnt()->length(1, 256)), false)
    ->attribute('addressState', v::optional(v::stringType()->alpha()->uppercase()->length(2, 2)), false)
    ->attribute('addressZip', v::optional(v::postalCode('US')), false)
    ->attribute('addressInternational', v::optional(v::stringType()->prnt()->length(1, 1024)), false)
    ->attribute('group', v::when(v::nullType(), v::alwaysValid(), v::oneOf(v::instance('\Tilmeld\Entities\Group'), v::instance('\SciActive\HookOverride_Tilmeld_Entities_Group'))))
    ->attribute('groups', v::arrayType()->each(v::oneOf(v::instance('\Tilmeld\Entities\Group'), v::instance('\SciActive\HookOverride_Tilmeld_Entities_Group'))))
    ->attribute('abilities', v::arrayType()->each(v::stringType()->notBlank()->prnt()->length(1, 256)))
    ->attribute('inheritAbilities', v::boolType())
    ->attribute('password', v::stringType()->notBlank()->length(1, 1024))
    ->setName('user object'),
];
