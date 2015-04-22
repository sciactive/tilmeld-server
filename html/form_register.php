<?php
/**
 * Provides a form to register a new user.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
$this->title = 'New User Registration';
$this->note = 'Please fill in your account details.';
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php e(pines_url('com_user', 'registeruser')); ?>">
	<ul class="nav nav-tabs" style="clear: both;">
		<li class="active"><a href="#p_muid_tab_general" data-toggle="tab">General</a></li>
		<?php if (in_array('address', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
		<li><a href="#p_muid_tab_location" data-toggle="tab">Address</a></li>
		<?php } ?>
	</ul>
	<div id="p_muid_tabs" class="tab-content">
		<div class="tab-pane active" id="p_muid_tab_general">
			<div class="pf-element" style="float: right;"><span class="pf-required">*</span> Required Field</div>
			<?php if (in_array('name', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
			<div class="pf-element">
				<label><span class="pf-label">First Name <span class="pf-required">*</span></span>
					<input class="pf-field form-control" type="text" name="nameFirst" size="24" value="<?php e($this->entity->nameFirst); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Middle Name</span>
					<input class="pf-field form-control" type="text" name="nameMiddle" size="24" value="<?php e($this->entity->nameMiddle); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Last Name</span>
					<input class="pf-field form-control" type="text" name="nameLast" size="24" value="<?php e($this->entity->nameLast); ?>" /></label>
			</div>
			<?php } if (!\Tilmeld\Tilmeld::$config['email_usernames'] && in_array('email', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Email <span class="pf-required">*</span></span>
					<input class="pf-field form-control" type="email" name="email" size="24" value="<?php e($this->entity->email); ?>" /></label>
			</div>
			<?php } if (in_array('phone', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Phone</span>
					<input class="pf-field form-control" type="tel" name="phone" size="24" value="<?php e(format_phone($this->entity->phone)); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<?php } if (in_array('fax', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Fax</span>
					<input class="pf-field form-control" type="tel" name="fax" size="24" value="<?php e(format_phone($this->entity->fax)); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<?php } if (in_array('timezone', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Timezone</span>
					<span class="pf-note">This overrides the primary group's timezone.</span>
					<select class="pf-field form-control" name="timezone">
						<option value="">--Default--</option>
						<?php $tz = DateTimeZone::listIdentifiers();
						sort($tz);
						foreach ($tz as $cur_tz) { ?>
						<option value="<?php e($cur_tz); ?>"<?php echo $this->entity->timezone == $cur_tz ? ' selected="selected"' : ''; ?>><?php e($cur_tz); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<?php if (in_array('address', \Tilmeld\Tilmeld::$config['reg_fields'])) { ?>
		<div class="tab-pane" id="p_muid_tab_location">
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
				<label><input class="pf-field" type="radio" name="addressType" value="us"<?php echo ($this->entity->addressType == 'us') ? ' checked="checked"' : ''; ?> /> US</label>
				<label><input class="pf-field" type="radio" name="addressType" value="international"<?php echo $this->entity->addressType == 'international' ? ' checked="checked"' : ''; ?> /> International</label>
			</div>
			<div id="p_muid_address_us" style="display: none;">
				<div class="pf-element">
					<label><span class="pf-label">Address 1</span>
						<input class="pf-field form-control" type="text" name="addressStreet" size="24" value="<?php e($this->entity->addressStreet); ?>" /></label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Address 2</span>
						<input class="pf-field form-control" type="text" name="addressStreet2" size="24" value="<?php e($this->entity->addressStreet2); ?>" /></label>
				</div>
				<div class="pf-element">
					<span class="pf-label">City, State</span>
					<input class="pf-field form-control" type="text" name="addressCity" size="15" value="<?php e($this->entity->addressCity); ?>" />
					<select class="pf-field form-control" name="addressState">
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
						<option value="<?php echo $key; ?>"<?php echo $this->entity->addressState == $key ? ' selected="selected"' : ''; ?>><?php echo $cur_state; ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Zip</span>
						<input class="pf-field form-control" type="text" name="addressZip" size="24" value="<?php e($this->entity->addressZip); ?>" /></label>
				</div>
			</div>
			<div id="p_muid_addressInternational" style="display: none;">
				<div class="pf-element pf-full-width">
					<label><span class="pf-label">Address</span>
						<span class="pf-group pf-full-width">
							<span class="pf-field" style="display: block;">
								<textarea style="width: 100%;" rows="3" cols="35" name="addressInternational"><?php e($this->entity->addressInternational); ?></textarea>
							</span>
						</span></label>
				</div>
			</div>
			<br class="pf-clearing" />
		</div>
		<?php } ?>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->url) ) { ?>
		<input type="hidden" name="url" value="<?php e($this->url); ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn btn-default" type="button" onclick="$_.get(<?php e(json_encode(pines_url())); ?>);" value="Cancel" />
	</div>
</form>