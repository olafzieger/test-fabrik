<?php
/**
 * Contacts Custom Form Template: Element
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0
 */
 ?>
<?php
/*
 This part of the template is what actually renders each individual element.  You will be loading this
 template multiple times (once for each element you want to display) from your default_group.php file.

 You probably won't need to edit this file - most changes you want can probably be done
 by overriding the template_css.php file in your J template html overrides folder

 If you do edit this file, make sure you use the same parts of the element this example uses,
 i.e. the same class definitions, etc.
*/
?>
	<?php if ($this->tipLocation == 'above') {
		echo '<div>' . $element->tipAbove . '</div>';
	}?>
	<?php
	// as this is a custom template, you may want to exclude $this->element->colum
	// which is the inline css required to abide by the group's column set up
	?>
	<div <?php echo @$this->element->column;?> class="<?php echo $this->element->containerClass;?>">
		<?php echo $this->element->label;?>
		<?php echo $this->element->errorTag; ?>
		<div class="fabrikElement">
			<?php echo $this->element->element;?>
		</div>
		<?php if ($this->tipLocation == 'side') {
		echo $element->tipSide;
	}?>
		<div style="clear:both"></div>
	</div>
	<?php if ($this->tipLocation == 'below') {
		echo '<div>' . $element->tipBelow . '</div>';
	}?>

	<?php
	$this->element->rendered = true;
	?>