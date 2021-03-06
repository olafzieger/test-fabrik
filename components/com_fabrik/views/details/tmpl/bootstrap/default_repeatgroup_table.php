<?php
/**
 * Bootstrap Details Template: Repeat group rendered as a table
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0.7
 */

$group = $this->group;
?>
<table class="table table-striped repeatGroupTable">
	<thead>
		<tr>
	<?php
	// Add in the table heading
	$firstGroup = $group->subgroups[0];
	foreach ($firstGroup as $el) :
		$style = $el->hidden ? 'style="display:none"' : '';
		?>
		<th <?php echo $style . ' ' . $el->containerProperties; ?> class="<?php echo $el->containerClass?>">
			<?php echo $el->label?>
		</th>
		<?php
	endforeach;

	// This column will contain the add/delete buttons
	if ($group->editable) : ?>
	<th></th>
	<?php
	endif;
	?>
	</tr>
	</thead>
	<tbody>
		<?php

		// Load each repeated group in a <tr>
		$this->i = 0;
		foreach ($group->subgroups as $subgroup) :
			$this->elements = $subgroup;
			echo $this->loadTemplate('repeatgroup_row');
			$this->i ++;
		endforeach;
		?>
	</tbody>
</table>
