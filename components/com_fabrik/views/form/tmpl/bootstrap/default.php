<?php
/**
 * Bootstrap Form Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.1
 */

$form = $this->form;
$model = $this->getModel();
$groupTmpl = $model->editable ? 'group' : 'group_details';
$active = ($form->error != '') ? '' : ' fabrikHide';

if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</div>
<?php
endif;

if ($this->params->get('show-title', 1)) :?>
<div class="page-header">
	<h1><?php echo $form->label;?></h1>
</div>
<?php
endif;

echo $form->intro;

if ($model->editable) :
echo '<form action="' . $form->action . '" class="' . $form->class . '" method="post" name="' . $form->name . '" id="' . $form->formid
				. '" enctype="' . $model->getFormEncType() . '">';
else:
	echo '<div class="fabrikForm fabrikDetails" id="' . $form->formid . '">';
endif;
echo $this->plugintop;
?>

<div class="fabrikMainError alert alert-error fabrikError<?php echo $active?>">
	<button class="close" data-dismiss="alert">×</button>
	<?php echo $form->error?>
</div>

<?php
echo $this->loadTemplate('buttons');
echo $this->loadTemplate('relateddata');
foreach ($this->groups as $group) :
	$this->group = $group;
	?>

		<<?php echo $form->fieldsetTag ?> class="fabrikGroup row-fluid" id="group<?php echo $group->id;?>" style="<?php echo $group->css;?>">

		<?php if (trim($group->title) !== '') :
		?>

			<<?php echo $form->legendTag ?> class="legend">
				<span><?php echo $group->title;?></span>
			</<?php echo $form->legendTag ?>>

		<?php endif;

		if ($group->intro !== '') : ?>
			<div class="groupintro"><?php echo $group->intro ?></div>
		<?php
		endif;

		// Load the group template - this can be :
		//  * default_group.php - standard group non-repeating rendered as an unordered list
		//  * default_repeatgroup.php - repeat group rendered as an unordered list
		//  * default_repeatgroup.table.php - repeat group rendered in a table.

		$this->elements = $group->elements;
		echo $this->loadTemplate($group->tmpl);

		 ?>
	</<?php echo $form->fieldsetTag ?>>
<?php
endforeach;
if ($model->editable) :
	echo $this->hiddenFields;
endif;

echo $this->pluginbottom;
echo $this->loadTemplate('actions');
echo $form->endTag;
echo $form->outro;
echo $this->pluginend;
echo FabrikHelperHTML::keepalive();
