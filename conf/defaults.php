<?php
/**
 * com_user's configuration defaults.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

return (object) [
	'create_admin' => [
		'cname' => 'Create Admin',
		'description' => 'Allow the creation of an admin user. When a user is created, if there are no other users in the system, he will be granted all abilities.',
		'value' => true,
	],
	'email_usernames' => [
		'cname' => 'Use Email Address as Username',
		'description' => 'Instead of a "username", a user logs in and is referred to by their email address. Enabling this after many users have been created can be messy. Make sure they all have email addresses first.',
		'value' => false,
	],
	'allow_registration' => [
		'cname' => 'Allow User Registration',
		'description' => 'Allow users to register.',
		'value' => true,
		'peruser' => true,
	],
	'one_step_registration' => [
		'cname' => 'One Step Registration',
		'description' => 'Allow users to register in one step.',
		'value' => false,
		'peruser' => true,
	],
	'user_search_limit' => [
		'cname' => 'User Search Limit',
		'description' => 'Limit the user search to this many results.',
		'value' => 20,
		'peruser' => true,
	],
	'checkUsername' => [
		'cname' => 'Check Usernames',
		'description' => 'Notify immediately if a requested username is available. (This can technically be used to determine if a user exists on the system.]',
		'value' => true,
		'peruser' => true,
	],
	'check_email' => [
		'cname' => 'Check Emails',
		'description' => 'Notify immediately if a requested email is available. (This can technically be used to determine if a user exists on the system.]',
		'value' => true,
		'peruser' => true,
	],
	'check_phone' => [
		'cname' => 'Check Phone Numbers',
		'description' => 'Notify immediately if a requested phone number is available. (This can technically be used to determine if a user exists on the system.]',
		'value' => true,
		'peruser' => true,
	],
	'user_fields' => [
		'cname' => 'User Account Fields',
		'description' => 'These will be the available fields for users. (Some fields, like username, can\'t be excluded.]',
		'value' => ['name', 'email', 'phone', 'fax', 'timezone', 'pin', 'address', 'additional_addresses', 'attributes'],
		'options' => [
			'Name' => 'name',
			'Email' => 'email',
			'Phone Number' => 'phone',
			'Fax Number' => 'fax',
			'Timezone' => 'timezone',
			'PIN Code' => 'pin',
			'Address' => 'address',
			'Additional Addresses' => 'additional_addresses',
			'Attributes' => 'attributes',
		],
		'peruser' => true,
	],
	'reg_fields' => [
		'cname' => 'Visible Registration Fields',
		'description' => 'These fields will be available for the user to fill in when they register.',
		'value' => ['name', 'email', 'phone', 'fax', 'timezone', 'address'],
		'options' => [
			'Name' => 'name',
			'Email' => 'email',
			'Phone Number' => 'phone',
			'Fax Number' => 'fax',
			'Timezone' => 'timezone',
			'Address' => 'address',
		],
		'peruser' => true,
	],
	'reg_message_welcome' => [
		'cname' => 'Registration Welcome Message',
		'description' => 'This message will be displayed to the user after they register.',
		'value' => 'You can begin using the system with the menu near the top of the page.',
		'peruser' => true,
	],
	'verify_email' => [
		'cname' => 'Verify User Email Addresses',
		'description' => 'Verify users\' email addresses upon registration/email change before allowing them to log in/change it.',
		'value' => false,
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
		'value' => '3 days',
		'peruser' => true,
	],
	'default_domain' => [
		'cname' => 'Default Login Domain',
		'description' => 'When using email address as username, the domain name listed here will be automatically appended to short logins. For example, you could put "sciactive.com" to be able to sign in with "hunter" instead of "hperrin@gmail.com".',
		'value' => '',
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
	'pw_empty' => [
		'cname' => 'Allow Empty Passwords',
		'description' => 'Allow users to have empty passwords.',
		'value' => false,
	],
	'pw_method' => [
		'cname' => 'Password Storage Method',
		'description' => "Method used to store passwords. Salt is more secure if the database is compromised, but can't be used with SAWASC.\n\nPlain: store the password in plaintext.\nDigest: store the password's digest using a simple salt.\nSalt: store the password's digest using a complex, unique salt.",
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
	'login_menu' => [
		'cname' => 'Show Login in Menu',
		'description' => 'Show a login button in the menu.',
		'value' => false,
		'peruser' => true,
	],
	'login_menu_path' => [
		'cname' => 'Login Menu Path',
		'description' => 'The path of the login button in the menu.',
		'value' => 'main_menu/~login',
		'peruser' => true,
	],
	'login_menu_text' => [
		'cname' => 'Login Menu Text',
		'description' => 'The text of the login button in the menu.',
		'value' => 'Log In',
		'peruser' => true,
	],
	'referral_codes' => [
		'cname' => 'Enable Referral Codes',
		'description' => 'Enable users to enter referral codes.',
		'value' => false,
	],
	'conditional_groups' => [
		'cname' => 'Conditional Groups',
		'description' => 'Allow groups to only provide abilities if conditions are met.',
		'value' => true,
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
	'min_pin_length' => [
		'cname' => 'User PIN Min Length',
		'description' => 'The minimum length for user PINs. 0 for no minimum.',
		'value' => 5,
	]
];