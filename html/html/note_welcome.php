<?php
/**
 * Displays a welcome note to the user.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

$this->title = 'Welcome to '.h($_->config->system_name);
$this->note = 'You are now registered and logged in.';
?>
<div>
	<?php e(\Tilmeld\Tilmeld::$config->reg_message_welcome['value']); ?>
</div>