<?php
/**
 * Provides a form for the user to choose current user module options.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
?>
<div class="pf-form">
	<div class="pf-element">
		This module will only be shown if there is a user currently logged in.
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Text</span>
			<span class="pf-note">"#name#" and "#username#" will be replaced by the current user's name and username.</span>
			<input class="pf-field form-control" type="text" name="text" size="36" value="<?php echo isset($this->text) ? h($this->text) : 'Logged in as #name# [#username#].'; ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Text Align</span>
			<select class="pf-field form-control" name="text_align">
				<option value="inherit"<?php echo $this->text_align == 'inherit' ? ' selected="selected"' : ''; ?>>Don't Change</option>
				<option value="left"<?php echo $this->text_align == 'left' ? ' selected="selected"' : ''; ?>>Left</option>
				<option value="right"<?php echo $this->text_align == 'right' ? ' selected="selected"' : ''; ?>>Right</option>
				<option value="center"<?php echo $this->text_align == 'center' ? ' selected="selected"' : ''; ?>>Center</option>
				<option value="justify"<?php echo $this->text_align == 'justify' ? ' selected="selected"' : ''; ?>>Justify</option>
			</select></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Font Style</span>
			<select class="pf-field form-control" name="font_style">
				<option value="inherit"<?php echo $this->font_style == 'inherit' ? ' selected="selected"' : ''; ?>>Don't Change</option>
				<option value="normal"<?php echo $this->font_style == 'normal' ? ' selected="selected"' : ''; ?>>Normal</option>
				<option value="italic"<?php echo $this->font_style == 'italic' ? ' selected="selected"' : ''; ?>>Italic</option>
				<option value="oblique"<?php echo $this->font_style == 'oblique' ? ' selected="selected"' : ''; ?>>Oblique</option>
			</select></label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Margin (Pixels)</span>
		<label class="pf-field" style="white-space: nowrap;">Top: <input class="form-control" type="number" name="margin_top" size="5" value="<?php echo isset($this->margin_top) ? h($this->margin_top) : '0'; ?>" /></label>
		<label class="pf-field" style="white-space: nowrap;">Right: <input class="form-control" type="number" name="margin_right" size="5" value="<?php echo isset($this->margin_right) ? h($this->margin_right) : '0'; ?>" /></label>
		<label class="pf-field" style="white-space: nowrap;">Bottom: <input class="form-control" type="number" name="margin_bottom" size="5" value="<?php echo isset($this->margin_bottom) ? h($this->margin_bottom) : '0'; ?>" /></label>
		<label class="pf-field" style="white-space: nowrap;">Left: <input class="form-control" type="number" name="margin_left" size="5" value="<?php echo isset($this->margin_left) ? h($this->margin_left) : '0'; ?>" /></label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Padding (Pixels)</span>
		<label class="pf-field" style="white-space: nowrap;">Top: <input class="form-control" type="number" name="padding_top" size="5" value="<?php echo isset($this->padding_top) ? h($this->padding_top) : '0'; ?>" /></label>
		<label class="pf-field" style="white-space: nowrap;">Right: <input class="form-control" type="number" name="padding_right" size="5" value="<?php echo isset($this->padding_right) ? h($this->padding_right) : '0'; ?>" /></label>
		<label class="pf-field" style="white-space: nowrap;">Bottom: <input class="form-control" type="number" name="padding_bottom" size="5" value="<?php echo isset($this->padding_bottom) ? h($this->padding_bottom) : '0'; ?>" /></label>
		<label class="pf-field" style="white-space: nowrap;">Left: <input class="form-control" type="number" name="padding_left" size="5" value="<?php echo isset($this->padding_left) ? h($this->padding_left) : '0'; ?>" /></label>
	</div>
</div>