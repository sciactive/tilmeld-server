<?php
/**
 * com_user's configuration defaults.
 *
 * @package Tilmeld
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

return (object) [
  'setup_url' => [
    'cname' => 'Setup URL',
    'description' => 'The URL where the setup utility is accessible. This is also used for account verification and password recovery.',
    'value' => 'http://localhost/tilmeld/',
  ],
  'create_admin' => [
    'cname' => 'Create Admin',
    'description' => 'Allow the creation of an admin user. When a user is created, if there are no other users in the system, he will be granted all abilities.',
    'value' => true,
  ],
  'email_usernames' => [
    'cname' => 'Use Email Address as Username',
    'description' => 'Instead of a "username", a user logs in and is referred to by their email address. Enabling this after many users have been created can be messy. Make sure they all have email addresses first.',
    'value' => true,
  ],
  'allow_registration' => [
    'cname' => 'Allow User Registration',
    'description' => 'Allow users to register.',
    'value' => true,
    'peruser' => true,
  ],
  'user_search_limit' => [
    'cname' => 'User Search Limit',
    'description' => 'Limit the user search to this many results.',
    'value' => 20,
    'peruser' => true,
  ],
  'user_fields' => [
    'cname' => 'User Account Fields',
    'description' => 'These will be the available fields for users. (Some fields, like username, can\'t be excluded.]',
    'value' => ['name', 'email', 'phone', 'timezone', 'address'],
    'options' => [
      'Name' => 'name',
      'Email' => 'email',
      'Phone Number' => 'phone',
      'Timezone' => 'timezone',
      'Address' => 'address',
    ],
    'peruser' => true,
  ],
  'reg_fields' => [
    'cname' => 'Visible Registration Fields',
    'description' => 'These fields will be available for the user to fill in when they register.',
    'value' => ['name', 'email'],
    'options' => [
      'Name' => 'name',
      'Email' => 'email',
      'Phone Number' => 'phone',
      'Timezone' => 'timezone',
      'Address' => 'address',
    ],
    'peruser' => true,
  ],
  'verify_email' => [
    'cname' => 'Verify User Email Addresses',
    'description' => 'Verify users\' email addresses upon registration/email change before allowing them to log in/change it.',
    'value' => true,
    'peruser' => true,
  ],
  'unverified_access' => [
    'cname' => 'Unverified User Access',
    'description' => 'Unverified users will be able to log in, but will only have the "unverified users" secondary group(s] until they verify their email. If set to false, their account will instead be disabled until they verify.',
    'value' => false,
    'peruser' => true,
  ],
  'email_rate_limit' => [
    'cname' => 'Rate Limit User Email Changes',
    'description' => 'Don\'t let users change their email address more often than this. You can enter one value and one unit of time, such as "2 weeks". Leave blank to disable rate limiting.',
    'value' => '1 day',
    'peruser' => true,
  ],
  'pw_recovery' => [
    'cname' => 'Allow Account Recovery',
    'description' => 'Allow users to recover their username and/or password through their registered email.',
    'value' => true,
    'peruser' => true,
  ],
  'pw_recovery_minutes' => [
    'cname' => 'Recovery Request Duration',
    'description' => 'How many minutes a recovery request is valid.',
    'value' => 240,
    'peruser' => true,
  ],
  'pw_method' => [
    'cname' => 'Password Storage Method',
    'description' => "Method used to store passwords. Salt is more secure if the database is compromised, but can't be used with SAWASC. Plain: store the password in plaintext. Digest: store the password's digest using a simple salt. Salt: store the password's digest using a complex, unique salt.",
    'value' => 'digest',
    'options' => [
      'Plain' => 'plain',
      'Digest' => 'digest',
      'Salt' => 'salt'
    ],
  ],
  'sawasc' => [
    'cname' => 'Enable SAWASC',
    'description' => 'SAWASC secures user authentication. If you do not host your site using SSL/TLS, you should enable this. However, it is not compatible with the "Salt" password storage method. See http://sawasc.sciactive.com/ for more information.',
    'value' => false,
  ],
  'sawasc_hash' => [
    'cname' => 'SAWASC Hash Function',
    'description' => 'Hash function to use during SAWASC authentication. If you don\'t know what this means, just leave it as the default.',
    'value' => 'whirlpool',
    'options' => [
      'md5',
      'whirlpool',
    ],
  ],
  'highest_primary' => [
    'cname' => 'Highest Assignable Primary Group Parent',
    'description' => 'The GUID of the group above the highest groups allowed to be assigned as primary groups. Zero means all groups, and -1 means no groups.',
    'value' => 0,
    'peruser' => true,
  ],
  'highest_secondary' => [
    'cname' => 'Highest Assignable Secondary Group Parent',
    'description' => 'The GUID of the group above the highest groups allowed to be assigned as secondary groups. Zero means all groups, and -1 means no groups.',
    'value' => 0,
    'peruser' => true,
  ],
  'valid_chars' => [
    'cname' => 'Valid Characters',
    'description' => 'Only these characters can be used when creating usernames and groupnames. (Doesn\'t apply to emails as usernames.]',
    'value' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-.',
  ],
  'valid_chars_notice' => [
    'cname' => 'Valid Characters Notice',
    'description' => 'When a user enters an invalid name, this message will be displayed.',
    'value' => 'Usernames and groupnames can only contain letters, numbers, underscore, dash, and period.',
    'peruser' => true,
  ],
  'valid_regex' => [
    'cname' => 'Valid Regex',
    'description' => 'Usernames and groupnames must match this regular expression. (Doesn\'t apply to emails as usernames.] By default, this ensures that the name begins and ends with an alphanumeric. (To allow anything, use "/.*/"]',
    'value' => '/^[a-zA-Z0-9].*[a-zA-Z0-9]$/',
  ],
  'valid_regex_notice' => [
    'cname' => 'Valid Regex Notice',
    'description' => 'When a user enters a name that doesn\'t match the regex, this message will be displayed.',
    'value' => 'Usernames and groupnames must begin and end with a letter or number.',
    'peruser' => true,
  ],
  'max_username_length' => [
    'cname' => 'Username Max Length',
    'description' => 'The maximum length for usernames. 0 for unlimited.',
    'value' => 0,
  ],
  'max_groupname_length' => [
    'cname' => 'Groupname Max Length',
    'description' => 'The maximum length for groupnames. 0 for unlimited.',
    'value' => 0,
  ],
];