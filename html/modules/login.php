<?php
/**
 * Provides a form for the user to log in.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

if (empty($this->title))
	$this->title = 'Log in to '.h($_->config->system_name);
$this->checkUsername = (\Tilmeld\Tilmeld::$config['allow_registration'] && \Tilmeld\Tilmeld::$config['checkUsername']);
if ($this->checkUsername)
	$_->icons->load();

// Activate SAWASC support.
$this->sawasc = $_->com_user->activateSawasc();

?>
<?php if ($this->checkUsername) { ?>
<style type="text/css">
	#p_muid_username_loading {
		background-position: left;
		background-repeat: no-repeat;
		padding-left: 16px;
		display: none;
	}
	#p_muid_username_message {
		background-position: left;
		background-repeat: no-repeat;
		padding-left: 20px;
		line-height: 16px;
	}
</style>
<?php } if ($this->sawasc || ($this->style != 'compact' && $this->style != 'small')) { ?>
<script type="text/javascript">
	<?php if ($this->sawasc) { ?>
	$_.loadjs("<?php e($_->config->location); ?>components/com_user/includes/hash.js");
	<?php } ?>
	$_(function(){
		<?php if ($this->style != 'compact' && $this->style != 'small') { ?>
		$("input[name=username]", "#p_muid_form").focus();
		<?php } if ($this->sawasc) { ?>
		$("#p_muid_form").submit(function(){
			// SAWASC code
			if ($("input[name=existing]:checked", "#p_muid_form").length)
				return true;
			var password_box = $("input[name=password]", "#p_muid_form");
			var password = password_box.val();
			var ClientComb = <?php echo json_encode($_SESSION['sawasc']['ServerCB']); ?> + md5(password+'7d5bc9dc81c200444e53d1d10ecc420a');
			<?php if ($_SESSION['sawasc']['algo'] == 'whirlpool') { ?>
			var ClientHash = Whirlpool(ClientComb).toLowerCase();
			<?php } else { ?>
			var ClientHash = md5(ClientComb);
			<?php } ?>
			$("input[name=ClientHash]", "#p_muid_form").val(ClientHash);
			password_box.val("");
		});
		<?php } ?>
	});
</script>
<?php } ?>
<div id="p_muid_form" class="clearfix"<?php echo ($this->style == 'compact') ? ' style="display: none; max-height: 500px; overflow-y: auto; overflow-x: hidden;"' : ''; ?>>
	<form class="pf-form" method="post" action="<?php e(pines_url()); ?>">
		<?php if (\Tilmeld\Tilmeld::$config['allow_registration']) { ?>
		<div class="pf-element">
			<script type="text/javascript">
				$_(function(){
					var new_account = false,
						username = $("[name=username]", "#p_muid_form"),
						password = $("[name=password]", "#p_muid_form"),
						password2 = $("[name=password2]", "#p_muid_form");
					$("#p_muid_form").submit(function(){
						if (new_account && password.val() != password2.val()) {
							alert("Your passwords do not match.");
							return false;
						}
						return true;
					});

					<?php if ($this->checkUsername) { ?>
					// Check usernames.
					var un_loading = $("#p_muid_username_loading"),
						un_message = $("#p_muid_username_message");
					username.change(function(){
						if (!new_account) {
							un_loading.hide();
							username.removeClass("ui-state-error");
							un_message.removeClass("picon-task-complete").removeClass("picon-task-attempt").html("").hide();
							return;
						}
						var id = <?php echo json_encode("{$this->entity->guid}"); ?>;
						$.ajax({
							url: <?php echo json_encode(pines_url('com_user', 'checkusername')); ?>,
							type: "POST",
							dataType: "json",
							data: {"id": id, "username": username.val()},
							beforeSend: function(){
								un_loading.show();
								username.removeClass("ui-state-error");
								un_message.removeClass("picon-task-complete").removeClass("picon-task-attempt").html("").hide();
							},
							complete: function(){
								un_loading.hide();
							},
							error: function(){
								username.addClass("ui-state-error");
								un_message.addClass("picon-task-attempt").html("Error checking username. Please check your internet connection.").show();
							},
							success: function(data){
								if (!data) {
									username.addClass("ui-state-error");
									un_message.addClass("picon-task-attempt").html("Error checking username.").show();
									return;
								}
								if (data.result) {
									username.removeClass("ui-state-error");
									un_message.addClass("picon-task-complete").html($_.safe(data.message)).show();
									return;
								}
								username.addClass("ui-state-error");
								un_message.addClass("picon-task-attempt").html($_.safe(data.message)).show();
							}
						});
					}).blur(function(){
						username.change();
					});
					<?php } ?>

					var pass_reenter = $("#p_muid_register_form"),
						recovery = $("#p_muid_recovery"),
						submit_btn = $("[name=submit]", "#p_muid_form");
					$("[name=existing]", "#p_muid_form").change(function(){
						if ($(this).is(":checked")) {
							new_account = false;
							pass_reenter.hide();
							recovery.show();
							submit_btn.val("Log In");
							username.change();
						} else {
							new_account = true;
							pass_reenter.show();
							recovery.hide();
							submit_btn.val("Sign Up");
							username.change();
						}
					}).change();
					$(":reset", "#p_muid_form").click(function(){
						new_account = false;
						pass_reenter.hide();
						recovery.show();
						submit_btn.val("Log In");
						username.change();
					});
				});
			</script>
			<?php if ($this->style != 'small') { ?><div class="pf-group"><?php } ?>
				<label><input class="pf-field" type="checkbox" name="existing" value="ON" checked="checked" /> I have an account.</label>
			<?php if ($this->style != 'small') { ?></div><?php } ?>
		</div>
		<?php } ?>
		<div class="pf-element">
			<label><span class="pf-label"><?php echo \Tilmeld\Tilmeld::$config['email_usernames'] ? 'Email' : 'Username'; ?></span>
				<?php if ($this->style != 'small') { ?>
				<span class="pf-group" style="display: block;">
				<?php } ?>
					<input class="pf-field form-control" type="<?php echo \Tilmeld\Tilmeld::$config['email_usernames'] ? 'email' : 'text'; ?>" name="username" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" />
					<?php if ($this->checkUsername) { echo ($this->style == 'compact') ? '<br class="pf-clearing" />' : ''; ?>
					<span class="pf-field picon picon-throbber loader" id="p_muid_username_loading" style="display: none;">&nbsp;</span>
					<span class="pf-field picon" id="p_muid_username_message" style="display: none;"></span>
					<?php } ?>
				<?php if ($this->style != 'small') { ?>
				</span>
				<?php } ?>
			</label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Password</span>
				<?php echo (\Tilmeld\Tilmeld::$config['pw_empty'] ? '<span class="pf-note">May be blank.</span>' : ''); ?>
				<input class="pf-field form-control" type="password" name="password" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
		</div>
		<?php if (\Tilmeld\Tilmeld::$config['allow_registration']) { ?>
		<div id="p_muid_register_form" style="display: none;">
			<div class="pf-element">
				<label><span class="pf-label">Re-enter Password</span>
					<input class="pf-field form-control" type="password" name="password2" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
			</div>
			<?php if (\Tilmeld\Tilmeld::$config['referral_codes']) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Referral Code</span>
					<span class="pf-note">Optional</span>
					<input class="pf-field form-control" type="text" name="referral_code" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
			</div>
			<?php } if (\Tilmeld\Tilmeld::$config['one_step_registration']) { ?>
			<div class="pf-element">
				<span class="pf-required">*</span> Required field.
			</div>
			<?php if (in_array('name', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
			<div class="pf-element">
				<label><span class="pf-label">First Name <span class="pf-required">*</span></span>
					<input class="pf-field form-control" type="text" name="nameFirst" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Middle Name</span>
					<input class="pf-field form-control" type="text" name="nameMiddle" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Last Name</span>
					<input class="pf-field form-control" type="text" name="nameLast" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
			</div>
			<?php } if (!\Tilmeld\Tilmeld::$config['email_usernames'] && in_array('email', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Email <span class="pf-required">*</span></span>
					<input class="pf-field form-control" type="email" name="email" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
			</div>
			<?php } if (in_array('phone', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Phone Number</span>
					<input class="pf-field form-control" type="tel" name="phone" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
			</div>
			<?php } if (in_array('fax', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Fax Number</span>
					<input class="pf-field form-control" type="tel" name="fax" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
			</div>
			<?php } if (in_array('timezone', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
			<div class="pf-element<?php echo ($this->style == 'small') ? ' pf-full-width' : ''; ?>">
				<label><span class="pf-label">Timezone</span>
					<span class="pf-note">This overrides the primary group's timezone.</span>
					<?php echo ($this->style == 'compact') ? '<div class="pf-group">' : ''; ?>
					<select class="pf-field form-control" name="timezone"<?php echo ($this->style == 'small') ? ' style="max-width: 95%;"' : ''; ?>>
						<option value="">--Default--</option>
						<?php $tz = DateTimeZone::listIdentifiers();
						sort($tz);
						foreach ($tz as $cur_tz) { ?>
						<option value="<?php e($cur_tz); ?>"><?php e($cur_tz); ?></option>
						<?php } ?>
					</select>
					<?php echo ($this->style == 'compact') ? '</div>' : ''; ?>
				</label>
			</div>
			<?php } if (in_array('address', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
			<div class="pf-element">
				<script type="text/javascript">
					$_(function(){
						var address_us = $("#p_muid_address_us");
						var addressInternational = $("#p_muid_addressInternational");
						$("#p_muid_form [name=addressType]").change(function(){
							var addressType = $(this);
							if (addressType.is(":checked") && addressType.val() == "us") {
								address_us.show();
								addressInternational.hide();
							} else if (addressType.is(":checked") && addressType.val() == "international") {
								addressInternational.show();
								address_us.hide();
							}
						}).change();
					});
				</script>
				<span class="pf-label">Address Type</span>
				<label><input class="pf-field" type="radio" name="addressType" value="us" checked="checked" /> US</label>
				<label><input class="pf-field" type="radio" name="addressType" value="international" /> International</label>
			</div>
			<div id="p_muid_address_us" style="display: none;">
				<div class="pf-element">
					<label><span class="pf-label">Address 1</span>
						<input class="pf-field form-control" type="text" name="addressStreet" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Address 2</span>
						<input class="pf-field form-control" type="text" name="addressStreet2" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
				</div>
				<div class="pf-element<?php echo ($this->style == 'small') ? ' pf-full-width' : ''; ?>">
					<label for="p_muid_addressCity"><span class="pf-label">City, State</span></label>
					<?php echo ($this->style == 'compact') ? '<div class="pf-group" style="white-space: nowrap; margin-right: 16px;">' : ''; ?>
					<input class="pf-field form-control" type="text" name="addressCity" id="p_muid_addressCity" size="<?php echo ($this->style == 'small') ? '10' : '15'; ?>" />
					<select class="pf-field form-control" name="addressState"<?php echo ($this->style == 'small') ? ' style="max-width: 95%;"' : ''; ?>>
						<option value="">None</option>
						<?php foreach ([
								'AL' => 'Alabama',
								'AK' => 'Alaska',
								'AZ' => 'Arizona',
								'AR' => 'Arkansas',
								'CA' => 'California',
								'CO' => 'Colorado',
								'CT' => 'Connecticut',
								'DE' => 'Delaware',
								'DC' => 'DC',
								'FL' => 'Florida',
								'GA' => 'Georgia',
								'HI' => 'Hawaii',
								'ID' => 'Idaho',
								'IL' => 'Illinois',
								'IN' => 'Indiana',
								'IA' => 'Iowa',
								'KS' => 'Kansas',
								'KY' => 'Kentucky',
								'LA' => 'Louisiana',
								'ME' => 'Maine',
								'MD' => 'Maryland',
								'MA' => 'Massachusetts',
								'MI' => 'Michigan',
								'MN' => 'Minnesota',
								'MS' => 'Mississippi',
								'MO' => 'Missouri',
								'MT' => 'Montana',
								'NE' => 'Nebraska',
								'NV' => 'Nevada',
								'NH' => 'New Hampshire',
								'NJ' => 'New Jersey',
								'NM' => 'New Mexico',
								'NY' => 'New York',
								'NC' => 'North Carolina',
								'ND' => 'North Dakota',
								'OH' => 'Ohio',
								'OK' => 'Oklahoma',
								'OR' => 'Oregon',
								'PA' => 'Pennsylvania',
								'RI' => 'Rhode Island',
								'SC' => 'South Carolina',
								'SD' => 'South Dakota',
								'TN' => 'Tennessee',
								'TX' => 'Texas',
								'UT' => 'Utah',
								'VT' => 'Vermont',
								'VA' => 'Virginia',
								'WA' => 'Washington',
								'WV' => 'West Virginia',
								'WI' => 'Wisconsin',
								'WY' => 'Wyoming',
								'AA' => 'Armed Forces (AA)',
								'AE' => 'Armed Forces (AE)',
								'AP' => 'Armed Forces (AP)'
							] as $key => $cur_state) { ?>
						<option value="<?php echo $key; ?>"><?php echo $cur_state; ?></option>
						<?php } ?>
					</select>
					<?php echo ($this->style == 'compact') ? '</div>' : ''; ?>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Zip</span>
						<input class="pf-field form-control" type="text" name="addressZip" size="<?php echo ($this->style == 'small') ? '10' : '24'; ?>" /></label>
				</div>
			</div>
			<div id="p_muid_addressInternational" style="display: none;">
				<div class="pf-element pf-full-width">
					<label><span class="pf-label">Address</span>
						<span class="pf-group pf-full-width">
							<span class="pf-field" style="display: block;">
								<textarea style="width: 100%;" rows="3" cols="35" name="addressInternational"></textarea>
							</span>
						</span></label>
				</div>
			</div>
			<?php	}
			} ?>
		</div>
		<?php } ?>
		<div class="pf-element<?php echo ($this->style == 'small') ? '' : ' pf-buttons'; ?>">
			<input type="hidden" name="option" value="com_user" />
			<input type="hidden" name="action" value="login" />
			<?php if ($this->sawasc) { ?>
			<input type="hidden" name="ClientHash" value="" />
			<?php } if ( !empty($this->url) ) { ?>
			<input type="hidden" name="url" value="<?php e($this->url); ?>" />
			<?php } ?>
			<input class="pf-button btn btn-primary" type="submit" name="submit" value="Log In" />
			<?php if ($this->style != 'small') { ?>
			<input class="pf-button btn btn-default" type="reset" name="reset" value="Reset" />
			<?php } ?>
		</div>
		<?php if (!$this->hide_recovery && \Tilmeld\Tilmeld::$config['pw_recovery']) { ?>
		<div class="pf-element" id="p_muid_recovery">
			<?php if ($this->style != 'small') { ?>
			<span class="pf-label" style="height: 1px;">&nbsp;</span>
			<?php } ?>
			<a class="pf-field" href="<?php e(pines_url('com_user', 'recover')); ?>">I can't access my account.</a>
		</div>
		<?php } ?>
	</form>
</div>
<?php if ($this->style == 'compact') { ?>
<script type="text/javascript">
	$_(function(){
		var notice = new PNotify({
			type: 'info',
			title: <?php echo json_encode($this->title); ?>,
			text: $("#p_muid_form").detach().show().append("<br style=\"clear: both; height: 0; line-height: 0;\" />"),
			icon: 'fa fa-key',
			width: 'auto',
			hide: false,
			sticker: false,
			history: {
				history: false
			},
			insert_brs: false,
			before_open: function(pnotify){
				// This prevents the notice from displaying when it's created.
				pnotify.update({
					before_open: null
				});
				return false;
			}
		});
		$("#p_muid_compact_link").click(function(){
			notice.open();
		});
	});
</script>
<a href="javascript:void(0);" id="p_muid_compact_link"><?php e($this->compact_text); ?></a>
<?php }