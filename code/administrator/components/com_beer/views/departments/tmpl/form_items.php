<? /** $Id$ */ ?>
<? defined('KOOWA') or die('Restricted access'); ?>

<? $i = 0; $m = 0; ?>
<? foreach (@$departments as $department) : ?>
<tr class="<?php echo 'row'.$m; ?>">
	<td align="center">
		<?= $i + 1; ?>
	</td>
	<td align="center">
		<? // @helper('grid.checkedOut', $project, $i, $project->id); ?>
		<?= @helper('grid.id', $i, $department->id); ?>
	</td>
	<td>
		<span class="editlinktip hasTip" title="<?= @text('Edit Profile') ?>::<?= @$escape($department->title); ?>">
			<a href="<?= @route('view=department&id='.$department->id); ?>">
				<?= @$escape($department->title); ?>
			</a>
		</span>
	</td>
	<td align="center" width="15px">
		<?= @helper('grid.enable', $department->enabled, $i) ?>
	</td>
	<td align="center" width="1%">
		<?= $department->people; ?>
	</td>
	<td align="center" width="1%">
		<?= $department->id; ?>
	</td>
</tr>
<? $i = $i + 1; $m = (1 - $m); ?>
<? endforeach; ?>		