<?php
/**
 * Provides a form to recover a user account password.
 *
 * @package Tilmeld
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

$this->title = 'Account Recovery';
$this->note = 'You can now set a new password for your user account.';
?>
<script type="text/javascript">
  $_(function(){
    var password = $("[name=password]", "#p_muid_form");
    var password2 = $("[name=password2]", "#p_muid_form");
    $("#p_muid_form").submit(function(){
      if (password.val() != password2.val()) {
        alert("Your passwords do not match.");
        return false;
      }
      return true;
    });
  });
</script>
<form class="pf-form" id="p_muid_form" method="post" action="<?php e(pines_url('com_user', 'recoverpassword')); ?>">
  <div class="pf-element pf-heading">
    <p>To reset your password, type your new password below.</p>
  </div>
  <div class="pf-element">
    <label><span class="pf-label">Password</span>
      <input class="pf-field form-control" type="password" name="password" size="24" value="" /></label>
  </div>
  <div class="pf-element">
    <label><span class="pf-label">Re-enter Password</span>
      <input class="pf-field form-control" type="password" name="password2" size="24" value="" /></label>
  </div>
  <div class="pf-element pf-buttons">
    <input type="hidden" name="form" value="true" />
    <input type="hidden" name="id" value="<?php e($this->entity->guid); ?>" />
    <input type="hidden" name="secret" value="<?php e($this->secret); ?>" />
    <input class="pf-button btn btn-primary" type="submit" value="Submit" />
    <input class="pf-button btn btn-default" type="button" onclick="$_.get(<?php e(json_encode(pines_url())); ?>);" value="Cancel" />
  </div>
</form>