<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<h1><?=($this->item->hubspotmigration_textmapping_id)?'Edit':'New'?> Text Mapping</h1>
	<p><select name="is_regex">
			<option value="0"<?php if(!$this->item->is_regex) echo ' selected="selected"'; ?>>Plain Text</option>
			<option value="1"<?php if($this->item->is_regex) echo ' selected="selected"'; ?>>Regular Expression</option>
	</select></p>
	<p>
		<label for="regex_from">From: </label><input type="text" name="regex_from" value="<?=$this->item->regex_from?>" />
		<label for="regex_to">To: </label><input type="text" name="regex_to" value="<?=$this->item->regex_to?>" />
		<select name="enabled">
			<option value="1"<?php if($this->item->enabled) echo ' selected="selected"'; ?>>enabled</option>
			<option value="0"<?php if(!$this->item->enabled) echo ' selected="selected"'; ?>>disabled</option>
		</select>
	</p>

	<input type="hidden" name="hubspotmigration_textmapping_id" value="<?=$this->item->hubspotmigration_textmapping_id?>" />
	<input type="hidden" name="option" id="option" value="com_hubspotmigration" />
	<input type="hidden" name="view" id="view" value="textmapping" />
	<input type="hidden" name="task" id="task" value="edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>