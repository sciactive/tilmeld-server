<?php
/**
 * Displays a registration note to the user.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

$this->title = 'New User Registration';
$this->note = 'The next step is to verify the email address you entered.';
?>
<div>
	An email has been sent to <?php e($this->entity->email); ?> with a
	verification link.
	<?php if (\Tilmeld\Tilmeld::$config->unverified_access['value']) { ?>
	You will have limited access until you verify your email address by clicking
	the link.
	<?php } else { ?>
	Please click the link to verify your email address and log in.
	<?php } ?>
</div>