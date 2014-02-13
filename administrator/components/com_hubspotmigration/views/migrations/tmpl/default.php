<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th><input type="checkbox" name="checkall-toggle" value="" title="<?= JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
				<th><?php echo JHTML::_('grid.sort', 'Migration ID', 'hubspotmigration_migration_id', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'K2 Category', 'k2_category_id', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'Content Group ID', 'blog_group_id', $this->lists->order_Dir, $this->lists->order) ?></th>
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
						<td align="center"><?=JHtml::_('grid.id',$i,$item->hubspotmigration_migration_id);?></td>
						<td><a href="index.php?option=com_hubspotmigration&view=migration&id=<?=$item->hubspotmigration_migration_id?>"><?=$item->hubspotmigration_migration_id?></a></td>
						<td><?=$item->blog_group_id?></td>
						<td><?=$item->k2_category_id?></td>
					</tr>
				<?php endforeach;
			else: ?>
				<tr>
					<td colspan="20">No migrations yet</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<input type="hidden" name="option" id="option" value="com_hubspotmigration" />
	<input type="hidden" name="view" id="view" value="migrations" />
	<input type="hidden" name="task" id="task" value="browse" />
	<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>" />
	<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>