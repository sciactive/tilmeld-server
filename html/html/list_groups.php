<?php
/**
 * Lists groups and provides functions to manipulate them.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

$this->title = ($this->enabled ? '' : 'Disabled ').'Groups';
$_->com_pgrid->load();
if (isset($_SESSION['tilmeld_user']) && is_array($_SESSION['tilmeld_user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['tilmeld_user']->pgrid_saved_states['com_user/list_groups']);

// Build an array of parents, so we can include the parent class on their rows.
$parents = [];
foreach($this->groups as $cur_group) {
	if (isset($cur_group->parent) && !in_array($cur_group->parent, $parents)) {
		array_push($parents, $cur_group->parent->guid);
	}
}
?>
<script type="text/javascript">
	$_(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_user/newgroup')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-user-group-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_user', 'editgroup')); ?>},
				<?php } if (gatekeeper('com_user/editgroup')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-user-group-properties', double_click: true, url: <?php echo json_encode(pines_url('com_user', 'editgroup', ['id' => '__title__'])); ?>},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_user/deletegroup')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-user-group-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_user', 'deletegroup', ['id' => '__title__'])); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } if ($this->enabled) { ?>
				{type: 'button', text: 'Disabled', extra_class: 'picon picon-vcs-removed', selection_optional: true, url: <?php echo json_encode(pines_url('com_user', 'listgroups', ['enabled' => 'false'])); ?>},
				<?php } else { ?>
				{type: 'button', text: 'Enabled', extra_class: 'picon picon-vcs-normal', selection_optional: true, url: <?php echo json_encode(pines_url('com_user', 'listgroups')); ?>},
				<?php } ?>
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					$_.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'groups',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 2,
			pgrid_sort_ord: 'asc',
			pgrid_child_prefix: "ch_",
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_user/list_groups", state: cur_state});
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
			<th>Groupname</th>
			<th>Display Name</th>
			<th>Email</th>
			<th>Timezone</th>
			<th>Members</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->groups as $group) { ?>
		<tr title="<?php e($group->guid); ?>" class="<?php
		if (in_array($group->guid, $parents))
			echo "parent ";
		if (isset($group->parent) && $group->parent->inArray($this->groups))
			e("child ch_{$group->parent->guid}");
		?>">
			<td><?php e($group->guid); ?></td>
			<td><a data-entity="<?php e($group->guid); ?>" data-entity-context="group"><?php e($group->groupname); ?></a></td>
			<td><?php e($group->name); ?></td>
			<td><a href="mailto:<?php e($group->email); ?>"><?php e($group->email); ?></a></td>
			<td><?php e($group->timezone); ?></td>
			<td><?php
			$user_array = \Nymph\Nymph::getEntities(
					['class' => '\Tilmeld\User', 'limit' => 51],
					['&',
						'tag' => 'enabled'
					],
					['|',
						'ref' => [
							['group', $group],
							['groups', $group]
						]
					]
				);
			$count = count($user_array);
			if ($count < 15) {
				$user_list = '';
				foreach ($user_array as $cur_user) {
					$user_list .= (empty($user_list) ? '' : ', ').'<a data-entity="'.h($cur_user->guid).'" data-entity-context="user">'.h($cur_user->username).'</a>';
				}
				echo $user_list;
			} elseif ($count === 51) {
				echo 'Over 50 users';
			} else {
				echo count($user_array).' users';
			}
			?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>