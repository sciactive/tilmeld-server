<?php
/**
 * Provides a form to recover a user account.
 *
 * @package Tilmeld
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */

$this->title = 'Account Recovery';
$this->note = 'If you\'ve forgotten your username or password, you can use this form to recover your account.';
if (\Tilmeld\Tilmeld::$config['email_usernames'])
  $this->note = 'If you\'ve forgotten your password, you can use this form to recover your account.';
?>
<script type="text/javascript">
  $_(function(){
    var form = $("#p_muid_form");
    $("input[name=type]", "#p_muid_form").change(function(){
      var box = $(this);
      if (box.is(":checked"))
        form.find(".toggle").hide().filter("."+box.val()).show();
    }).change();
  });
</script>
<form class="pf-form" id="p_muid_form" method="post" action="<?php e(pines_url('com_user', 'recover')); ?>">
  <?php if (\Tilmeld\Tilmeld::$config['email_usernames']) { ?>
  <input class="pf-field" type="hidden" name="type" value="password" />
  <?php } else { ?>
  <div class="pf-element">
    <span class="pf-label">Recovery Type</span>
    <label><input class="pf-field" type="radio" name="type" value="password" checked="checked" /> I forgot my password.</label>
    <label><input class="pf-field" type="radio" name="type" value="username" /> I forgot my username.</label>
  </div>
  <?php } ?>
  <div class="pf-element pf-heading">
    <p class="toggle password">To reset your password, type your <?php echo \Tilmeld\Tilmeld::$config['email_usernames'] ? 'email' : 'username'; ?> you use to sign in below.</p>
    <p class="toggle username" style="display: none;">To retrieve your username, type your full email address exactly as you entered it when creating your account below.</p>
  </div>
  <div class="pf-element">
    <label>
      <span class="pf-label toggle password"><?php echo \Tilmeld\Tilmeld::$config['email_usernames'] ? 'Email Address' : 'Username'; ?></span>
      <span class="pf-label toggle username" style="display: none;">Email Address</span>
      <input class="pf-field form-control" type="text" name="account" size="24" value="" />
    </label>
  </div>
  <div class="pf-element pf-buttons">
    <input class="pf-button btn btn-primary" type="submit" value="Submit" />
    <input class="pf-button btn btn-default" type="button" onclick="$_.get(<?php e(json_encode(pines_url())); ?>);" value="Cancel" />
  </div>
</form>