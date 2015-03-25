<?php
/**
 * Save changes to a group.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $_ core */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_user/editgroup') )
		punt_user(null, pines_url('com_user', 'listgroups'));
	$group = group::factory((int) $_REQUEST['id']);
	if (!isset($group->guid)) {
		pines_error('Requested group id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_user/newgroup') )
		punt_user(null, pines_url('com_user', 'listgroups'));
	$group = group::factory();
}

if (gatekeeper('com_user/usernames'))
	$group->groupname = $_REQUEST['groupname'];
$group->name = $_REQUEST['name'];
if (gatekeeper('com_user/enabling')) {
	if ($_REQUEST['enabled'] == 'ON')
		$group->addTag('enabled');
	else
		$group->removeTag('enabled');
}
$group->email = $_REQUEST['email'];
if ($group->email && $_REQUEST['mailing_list'] != 'ON' && !$_->com_mailer->unsubscribe_query($group->email)) {
	if (!$_->com_mailer->unsubscribe_add($group->email))
		pines_error('Your email could not be removed from the mailing list. Please try again, and if the problem persists, contact an administrator.');
} elseif ($group->email && $_REQUEST['mailing_list'] == 'ON' && $_->com_mailer->unsubscribe_query($group->email)) {
	if (!$_->com_mailer->unsubscribe_remove($group->email))
		pines_error('Your email could not be added to the mailing list. Please try again, and if the problem persists, contact an administrator.');
}
$group->phone = preg_replace('/\D/', '', $_REQUEST['phone']);
$group->phone2 = preg_replace('/\D/', '', $_REQUEST['phone2']);
$group->fax = preg_replace('/\D/', '', $_REQUEST['fax']);
$group->timezone = $_REQUEST['timezone'];
if (gatekeeper('com_user/defaultgroups')) {
	$group->default_primary = $_REQUEST['default_primary'] == 'ON';
	$group->default_secondary = $_REQUEST['default_secondary'] == 'ON';
	$group->unverified_secondary = $_REQUEST['unverified_secondary'] == 'ON';
}
// Location
$group->address_type = $_REQUEST['address_type'];
$group->address_1 = $_REQUEST['address_1'];
$group->address_2 = $_REQUEST['address_2'];
$group->city = $_REQUEST['city'];
$group->state = $_REQUEST['state'];
$group->zip = $_REQUEST['zip'];
$group->address_international = $_REQUEST['address_international'];

// Conditions
if ( gatekeeper('com_user/conditions') ) {
	$conditions = (array) json_decode($_REQUEST['conditions']);
	$group->conditions = array();
	foreach ($conditions as $cur_condition) {
		if (!isset($cur_condition->values[0], $cur_condition->values[1]))
			continue;
		$group->conditions[$cur_condition->values[0]] = $cur_condition->values[1];
	}
}

// Attributes
$group->attributes = (array) json_decode($_REQUEST['attributes']);
foreach ($group->attributes as &$cur_attribute) {
	$array = array(
		'name' => $cur_attribute->values[0],
		'value' => $cur_attribute->values[1]
	);
	$cur_attribute = $array;
}
unset($cur_attribute);

//if ( $_REQUEST['no_parent'] == 'ON' ) {
if ( $_REQUEST['parent'] == 'none' ) {
	unset($group->parent);
} else {
	$parent = group::factory((int) $_REQUEST['parent']);
	// Check if the selected parent is a descendant of this group.
	if (!$group->is($parent) && !$parent->is_descendant($group))
		$group->parent = $parent;
}

if ( gatekeeper('com_user/abilities') ) {
	$sections = array('system');
	foreach ($_->components as $cur_component) {
		$sections[] = $cur_component;
	}
	foreach ($sections as $cur_section) {
		if ($cur_section == 'system') {
			$section_abilities = (array) $_->info->abilities;
		} else {
			$section_abilities = (array) $_->info->$cur_section->abilities;
		}
		foreach ($section_abilities as $cur_ability) {
			if ( isset($_REQUEST[$cur_section]) && (array_search($cur_ability[0], $_REQUEST[$cur_section]) !== false) ) {
				$group->grant($cur_section.'/'.$cur_ability[0]);
			} else {
				$group->revoke($cur_section.'/'.$cur_ability[0]);
			}
		}
	}
}

if (empty($group->groupname)) {
	$group->print_form();
	pines_notice('Please specify a groupname.');
	return;
}
if (Tilmeld::$config->max_groupname_length['value'] > 0 && strlen($group->groupname) > Tilmeld::$config->max_groupname_length['value']) {
	$group->print_form();
	pines_notice("Groupnames must not exceed {Tilmeld::$config->max_groupname_length['value']} characters.");
	return;
}
$test = $_->nymph->getEntity(
		array('class' => group, 'skip_ac' => true),
		array('&',
			'tag' => array('com_user', 'group'),
			'match' => array('groupname', '/^'.preg_quote($_REQUEST['groupname'], '/').'$/i')
		)
	);
if (isset($test->guid) && !$group->is($test)) {
	$group->print_form();
	pines_notice('There is already a group with that groupname. Please choose a different groupname.');
	return;
}
if (array_diff(str_split($group->groupname), str_split(Tilmeld::$config->valid_chars['value']))) {
	$group->print_form();
	pines_notice(Tilmeld::$config->valid_chars_notice['value']);
	return;
}
if (!preg_match(Tilmeld::$config->valid_regex['value'], $group->groupname)) {
	$group->print_form();
	pines_notice(Tilmeld::$config->valid_regex_notice['value']);
	return;
}
if (!empty($group->email)) {
	$test = $_->nymph->getEntity(
			array('class' => group, 'skip_ac' => true),
			array('&',
				'tag' => array('com_user', 'group'),
				'match' => array('email', '/^'.preg_quote($group->email, '/').'$/i')
			)
		);
	if (isset($test) && !$group->is($test)) {
		$group->print_form();
		pines_notice('There is already a group with that email address. Please use a different email.');
		return;
	}
}
if (isset($group->parent) && !isset($group->parent->guid)) {
	$group->print_form();
	pines_notice('Parent group is not valid.');
	return;
}
if (gatekeeper('com_user/defaultgroups') && $group->default_primary) {
	$current_primary = $_->nymph->getEntity(array('class' => group), array('&', 'tag' => array('com_user', 'group'), 'data' => array('default_primary', true)));
	if (isset($current_primary) && !$group->is($current_primary)) {
		unset($current_primary->default_primary);
		if ($current_primary->save()) {
			pines_notice("New user primary group changed from {$current_primary->groupname} to {$group->groupname}");
		} else {
			$group->print_form();
			pines_error("Could not change new user primary group from {$current_primary->groupname}.");
			return;
		}
	}
}

if ($_REQUEST['remove_logo'] == 'ON' && isset($group->logo))
	unset($group->logo);

// Logo image upload and resizing.
if (!empty($_REQUEST['image']) && $_->uploader->check($_REQUEST['image'])) {
	$group->logo = $_REQUEST['image'];
	/* How to resize images without overwriting them?
	if (Tilmeld::$config->resize_logos['value']) {
		// if jpeg
		case 'image/jpeg':
			$img_raw = imagecreatefromjpeg($group->logo);
			$currwidth = imagesx($img_raw);
			$currheight = imagesy($img_raw);
			$img_resized = imagecreate(Tilmeld::$config->logo_width['value'], Tilmeld::$config->logo_height['value']);
			imagecopyresized($img_resized, $img_raw, 0, 0, 0, 0, Tilmeld::$config->logo_width['value'], Tilmeld::$config->logo_height['value'], $currwidth, $currheight);
			imagejpeg($img_resized, $group->logo);
			imagedestroy($img_raw);
			imagedestroy($img_resized);
			break;
		// if png
		case 'image/png':
			$img_raw = imagecreatefrompng($group->logo);
			$currwidth = imagesx($img_raw);
			$currheight = imagesy($img_raw);
			$img_resized = imagecreate(Tilmeld::$config->logo_width['value'], Tilmeld::$config->logo_height['value']);
			imagecopyresized($img_resized, $img_raw, 0, 0, 0, 0, Tilmeld::$config->logo_width['value'], Tilmeld::$config->logo_height['value'], $currwidth, $currheight);
			imagepng($img_resized, $group->logo);
			imagedestroy($img_raw);
			imagedestroy($img_resized);
			break;
		// if gif
		case 'image/gif':
			$img_raw = imagecreatefromgif($group->logo);
			$currwidth = imagesx($img_raw);
			$currheight = imagesy($img_raw);
			$img_resized = imagecreatetruecolor(Tilmeld::$config->logo_width['value'], Tilmeld::$config->logo_height['value']);
			$blank = imagecolortransparent($img_raw);
			// If the image has alpha values (transparency) fill our resized image with blank space.
			if( $blank >= 0 && $blank < imagecolorstotal($img_raw) ) {
				$trans = imagecolorsforindex($img_raw, $blank);
				$trans_color = imagecolorallocate($img_resized, $trans['red'], $trans['green'], $trans['blue']);
				imagefill( $img_resized, 0, 0, $trans_color );
				imagecolortransparent( $img_resized, $trans_color );
			}
			imagecopyresized($img_resized, $img_raw, 0, 0, 0, 0, Tilmeld::$config->logo_width['value'], Tilmeld::$config->logo_height['value'], $currwidth, $currheight);
			imagegif($img_resized, $group->logo);
			imagedestroy($img_raw);
			imagedestroy($img_resized);
			break;
	}
	*/
}

if ($group->save()) {
	pines_notice('Saved group ['.$group->groupname.']');
	pines_log('Saved group ['.$group->groupname.']');
} else {
	pines_error('Error saving group. Do you have permission?');
}

if ($group->hasTag('enabled')) {
	pines_redirect(pines_url('com_user', 'listgroups'));
} else {
	pines_redirect(pines_url('com_user', 'listgroups', array('enabled' => 'false')));
}