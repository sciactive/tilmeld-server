<?php
/**
 * Group entity helper.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

$module = new module('com_entityhelper', 'default_helper');
$module->render = $this->render;
$module->entity = $this->entity;
echo $module->render();

if ($this->render == 'body' && gatekeeper('com_user/listgroups')) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Properties</h3>
	<table class="table table-bordered" style="clear:both;">
		<tbody>
			<tr>
				<td style="font-weight:bold;" colspan="2">GUID</td>
				<td><?php e($this->entity->guid); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;" colspan="2">Groupname</td>
				<td><?php e($this->entity->groupname); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;" colspan="2">Display Name</td>
				<td><?php e($this->entity->name); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;" colspan="2">Enabled</td>
				<td><?php echo $this->entity->hasTag('enabled') ? 'Yes' : 'No'; ?></td>
			</tr>
			<?php if (!empty($this->entity->email)) { ?>
			<tr>
				<td style="font-weight:bold;" colspan="2">Email</td>
				<td><a href="mailto:<?php e($this->entity->email); ?>"><?php e($this->entity->email); ?></a></td>
			</tr>
			<?php } if (!empty($this->entity->timezone)) { ?>
			<tr>
				<td style="font-weight:bold;" colspan="2">Timezone</td>
				<td><?php e($this->entity->timezone); ?></td>
			</tr>
			<?php } if ($this->entity->parent->guid) { ?>
			<tr>
				<td style="font-weight:bold;" colspan="2">Parent</td>
				<td><a data-entity="<?php e($this->entity->parent->guid); ?>" data-entity-context="group"><?php e($this->entity->parent->info('name')); ?></a></td>
			</tr>
			<?php } ?>
			<tr>
				<td style="font-weight:bold;" rowspan="2">Members</td>
				<td style="font-weight:bold;">Primary</td>
				<td>
					<?php
					$user_array = \Nymph\Nymph::getEntities(
							['class' => '\Tilmeld\User', 'limit' => 51],
							['&',
								'tag' => 'enabled',
								'ref' => ['group', $this->entity]
							]
						);
					$count = count($user_array);
					if ($count < 51) { ?>
					<ul>
						<?php
						$names = [];
						$i = 0;
						foreach ($user_array as $cur_user) {
							$i++;
							if ($i === 11)
								$names[] = '<li style="list-style: none outside none;"><a href="javascript:void(0);" onclick="$(this).closest(\'ul\').children().show(); $(this).parent().hide();">Show all users.</a></li>';
							$names[] = '<li'.($i >= 11 ? ' style="display: none;"' : '').'><a data-entity="'.h($cur_user->guid).'" data-entity-context="user">'.h($cur_user->info('name')).'</a></li>';
						}
						echo implode("\n", $names);
						?>
					</ul>
					<?php } else {
						echo 'Over 50 users';
					} ?>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Secondary</td>
				<td>
					<?php
					$user_array = \Nymph\Nymph::getEntities(
							['class' => '\Tilmeld\User', 'limit' => 51],
							['&',
								'tag' => 'enabled',
								'ref' => ['groups', $this->entity]
							]
						);
					$count = count($user_array);
					if ($count < 51) { ?>
					<ul>
						<?php
						$names = [];
						$i = 0;
						foreach ($user_array as $cur_user) {
							$i++;
							if ($i === 11)
								$names[] = '<li style="list-style: none outside none;"><a href="javascript:void(0);" onclick="$(this).closest(\'ul\').children().show(); $(this).parent().hide();">Show all users.</a></li>';
							$names[] = '<li'.($i >= 11 ? ' style="display: none;"' : '').'><a data-entity="'.h($cur_user->guid).'" data-entity-context="user">'.h($cur_user->info('name')).'</a></li>';
						}
						echo implode("\n", $names);
						?>
					</ul>
					<?php } else {
						echo 'Over 50 users';
					} ?>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;" colspan="2">Default Primary Group</td>
				<td><?php echo $this->entity->default_primary ? 'Yes' : 'No'; ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;" colspan="2">Default Secondary Group</td>
				<td><?php echo $this->entity->default_secondary ? 'Yes' : 'No'; ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;" colspan="2">Unverified Secondary Group</td>
				<td><?php echo $this->entity->unverified_secondary ? 'Yes' : 'No'; ?></td>
			</tr>
		</tbody>
	</table>
</div>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Logo <small><?php echo (isset($this->entity->logo)) ? 'Assigned' : 'Inherited'; ?></small></h3>
	<div style="clear: both; padding-left: .5em;">
		<span class="thumbnail" style="display: inline-block; max-width: 90%;">
			<img src="<?php e($this->entity->get_logo()); ?>" alt="Group Logo" style="max-width: 100%;">
		</span>
	</div>
</div>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Address</h3>
	<address>
		<?php if ($this->entity->address_type == 'us') {
			e($this->entity->address_1).'<br />';
			if (!empty($this->entity->address_2))
				e($this->entity->address_2).'<br />';
			e($this->entity->city).', ';
			e($this->entity->state).' ';
			e($this->entity->zip);
		} else {
			echo '<pre>'.h($this->entity->address_international).'</pre>';
		} ?>
	</address>
</div>
<?php if ($this->entity->attributes) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Attributes</h3>
	<table class="table table-bordered" style="clear:both;">
		<thead>
			<tr><th>Name</th><th>Value</th></tr>
		</thead>
		<tbody>
			<?php foreach ($this->entity->attributes as $cur_attribute) { ?>
			<tr><td><?php e($cur_attribute['name']); ?></td><td><?php e($cur_attribute['value']); ?></td></tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php } }