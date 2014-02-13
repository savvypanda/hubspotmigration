<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th><input type="checkbox" name="checkall-toggle" value="" title="<?= JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
				<th><?php echo JHTML::_('grid.sort', 'Joomla User', 'juser_name', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'Hubspot Author ID', 'hb_author_id', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'Hubspot Author Name', 'hb_author_name', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'Auto Created?', 'auto_created', $this->lists->order_Dir, $this->lists->order) ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="20">
					<?php if($this->pagination->total > 0) echo $this->pagination->getListFooter() ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php if(!empty($this->items)):
				$m = 1;
				foreach($this->items as $i => $item):
					$m = 1-$m; ?>
					<tr class="row<?=$m ?>">
						<td align="center"><?=JHtml::_('grid.id',$i,$item->hubspotmigration_author_id);?></td>
						<td><?php if($item->juser_id): ?><a href="index.php?option=com_users&task=user.edit&id=<?=$item->juser_id?>"><?=$item->juser_name?></a><?php endif; ?></td>
						<td><?php if($item->hb_author_id): ?><?=$item->hb_author_id?><?php endif; ?></td>
						<td><?php if($item->hb_author_name): ?><?=$item->hb_author_name?><?php endif; ?></td>
						<td align="center"><?=($item->auto_created)?'auto':'manual'?></td>
					</tr>
				<?php endforeach;
			else: ?>
				<tr>
					<td colspan="20">No authors yet</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<input type="hidden" name="option" id="option" value="com_hubspotmigration" />
	<input type="hidden" name="view" id="view" value="authors" />
	<input type="hidden" name="task" id="task" value="browse" />
	<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>" />
	<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>