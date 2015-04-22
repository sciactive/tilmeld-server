<?php
/**
 * Lists users and provides functions to manipulate them.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

$this->title = ($this->enabled ? '' : 'Disabled ').'Users';
$_->com_pgrid->load();
if (isset($_SESSION['tilmeld_user']) && is_array($_SESSION['tilmeld_user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['tilmeld_user']->pgrid_saved_states['com_user/list_users']);
?>
<script type="text/javascript">
	$_(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_user/newuser')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-list-add-user', selection_optional: true, url: <?php echo json_encode(pines_url('com_user', 'edituser')); ?>},
				<?php } if (gatekeeper('com_user/edituser')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-user-properties', double_click: true, url: <?php echo json_encode(pines_url('com_user', 'edituser', ['id' => '__title__'])); ?>},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_user/deleteuser')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-list-remove-user', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_user', 'deleteuser', ['id' => '__title__'])); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } if ($this->enabled) { ?>
				{type: 'button', text: 'Disabled', extra_class: 'picon picon-vcs-removed', selection_optional: true, url: <?php echo json_encode(pines_url('com_user', 'listusers', ['enabled' => 'false'])); ?>},
				<?php } else { ?>
				{type: 'button', text: 'Enabled', extra_class: 'picon picon-vcs-normal', selection_optional: true, url: <?php echo json_encode(pines_url('com_user', 'listusers')); ?>},
				<?php } ?>
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					$_.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'users',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 2,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_user/list_users", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>GUID</th>
			<th><?php echo \Tilmeld\Tilmeld::$config['email_usernames'] ? 'Email' : 'Username'; ?></th>
			<?php if (in_array('name', \Tilmeld\Tilmeld::$config['user_fields'])) { ?>
			<th>Real Name</th>
			<?php } if (!\Tilmeld\Tilmeld::$config['email_usernames'] && in_array('email', \Tilmeld\Tilmeld::$config['user_fields'])) { ?>
			<th>Email</th>
			<?php } if (in_array('timezone', \Tilmeld\Tilmeld::$config['user_fields'])) { ?>
			<th>Timezone</th>
			<?php } ?>
			<th>Primary Group</th>
			<th>Groups</th>
			<th>Inherit Abilities</th>
			<?php if (\Tilmeld\Tilmeld::$config['referral_codes']) { ?>
			<th>Referral Code</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->users as $user) { ?>
		<tr title="<?php e($user->guid); ?>">
			<td><?php e($user->guid); ?></td>
			<td><a data-entity="<?php e($user->guid); ?>" data-entity-context="user"><?php e($user->username); ?></a></td>
			<?php if (in_array('name', \Tilmeld\Tilmeld::$config['user_fields'])) { ?>
			<td><?php e($user->name); ?></td>
			<?php } if (!\Tilmeld\Tilmeld::$config['email_usernames'] && in_array('email', \Tilmeld\Tilmeld::$config['user_fields'])) { ?>
			<td><a href="mailto:<?php e($user->email); ?>"><?php e($user->email); ?></a></td>
			<?php } if (in_array('timezone', \Tilmeld\Tilmeld::$config['user_fields'])) { ?>
			<td><?php e($user->getTimezone()).(empty($user->timezone) ? ' (I)' : ' (A)'); ?></td>
			<?php } ?>
			<td><a data-entity="<?php e($user->group->guid); ?>" data-entity-context="group"><?php e($user->group->groupname); ?></a></td>
			<td><?php
			if (count($user->groups) < 15) {
				$group_list = '';
				foreach ($user->groups as $cur_group) {
					$group_list .= (empty($group_list) ? '' : ', ').'<a data-entity="'.h($cur_group->guid).'" data-entity-context="group">'.h($cur_group->groupname).'</a>';
				}
				echo $group_list;
			} else {
				echo count($user->groups).' groups';
			}
			?></td>
			<td><?php echo $user->inheritAbilities ? 'Yes' : 'No'; ?></td>
			<?php if (\Tilmeld\Tilmeld::$config['referral_codes']) { ?>
			<td><?php e($user->referral_code); ?></td>
			<?php } ?>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php if (in_array('timezone', \Tilmeld\Tilmeld::$config['user_fields'])) { ?>
<small>Note: Under timezones (I) means inherited and (A) means assigned.</small>
<?php }