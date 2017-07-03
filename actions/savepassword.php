<?php
/**
 * Save the current user's new password.
 *
 * @package Components\user
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $_ core */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/self') )
  punt_user(null, pines_url());

if (empty($_REQUEST['new1']) || $_REQUEST['new1'] != $_REQUEST['new2'] || !$_SESSION['tilmeld_user']->check_password($_REQUEST['current'])) {
  pines_notice('Invalid password submitted.');
  $_SESSION['tilmeld_user']->print_form_password();
  return;
}

Tilmeld::session('write');
$_SESSION['tilmeld_user']->password($_REQUEST['new1']);
if ($_SESSION['tilmeld_user']->save())
  pines_notice('Changed password successfully.');
else
  pines_error('Error saving new password.');
Tilmeld::session('close');

pines_redirect(pines_url('com_user', 'editself'));