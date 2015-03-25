<?php
/**
 * Log a user out of the system.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $_ core */
defined('P_RUN') or die('Direct access prohibited');

Tilmeld::logout();
pines_notice('You have been logged out.');
pines_redirect(pines_url());
