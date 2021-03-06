<?php
/**
 * Admin List Linked Elements Tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die;
?>
<form action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<?php
$form = $this->formTable;
	  echo "<h3>".JText::_('COM_FABRIK_FORM') ."</h3>";
	  echo "<a href=\"index.php?option=com_fabrik&amp;view=form&amp;layout=edit&amp;id=$form->id\">".$form->label."</a><br />";
	  echo "<h3>".JText::_('COM_FABRIK_ELEMENTS') ."</h3>";
	  echo "<table style=\"margin-bottom:50px;\" class=\"adminlist table table-striped\" width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" >\n";
	  echo "<thead><tr><th class='title'>". JText::_('COM_FABRIK_ELEMENT') ."</th><th class='title'>". JText::_('COM_FABRIK_LABEL') ."</th><th class='title'>".JText::_('COM_FABRIK_GROUP')."</th></tr></thead>";
	  $k = 1;
	  echo "<tbody>";
	  foreach ( $this->formGroupEls as $el) :
	    $cid = $el->id;
	    echo "<tr class='sectiontableentry$k row$k'>"
	    ."<td><a href=\"index.php?option=com_fabrik&amp;view=element&amp;layout=edit&amp;id=$cid\">$el->name</a></td>"
	    ."<td>"."$el->label"."</td><td><a href='index.php?option=com_fabrik&amp;view=group&amp;layout=edit&amp;id=$el->group_id'>"."$el->group_name"."</a></td></tr>";
	    $k = 1 - $k;
	  endforeach;
	  echo "</tbody>";
	  echo "</table>";

	  ?>
	  	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
