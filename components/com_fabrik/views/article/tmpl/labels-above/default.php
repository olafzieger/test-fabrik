<?php if ($this->params->get('show_page_heading', 1)) { ?>
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php echo $this->escape($this->params->get('page_heading')); ?></div>
<?php } ?>
<?php $form = $this->form;
?>
<div class="fabrikForm fabrikDetails" id="<?php echo $form->formid; ?>">
<?php
if ($this->params->get('show-title', 1)) {?>
<h1><?php echo $form->label;?></h1>
<?php }
echo $form->intro;
echo $this->plugintop;
$active = ($form->error != '') ? '' : ' fabrikHide';
echo "<div class='fabrikMainError fabrikError$active'>" . $form->error . "</div>";?>

	<?php
	if ($this->showEmail) {
		echo $this->emailLink;
	}
	if ($this->showPDF) {
		echo $this->pdfLink;
	}
	if ($this->showPrint) {
		echo $this->printLink;
	}
	echo $this->loadTemplate('relateddata');
	foreach ( $this->groups as $group) {
		?>
		<fieldset class="fabrikGroup" id="group<?php echo $group->id;?>" style="<?php echo $group->css;?>">
		<legend><?php echo $group->title;?></legend>
		<?php if ($group->canRepeat) {
			foreach ($group->subgroups as $subgroup) {
			?>
				<div class="fabrikSubGroup">
					<div class="fabrikSubGroupElements">
						<?php
						$this->elements = $subgroup;
						echo $this->loadTemplate('group');
						?>
					</div>
					<?php if ($group->editable) { ?>
						<div class="fabrikGroupRepeater">
							<?php if ($group->canAddRepeat) {?>
							<a class="addGroup" href="#">
								<?php echo FabrikHelperHTML::image('plus-sign.png', 'form', $this->tmpl, JText::_('COM_FABRIK_ADD_GROUP'));?>
							</a>
							<?php }?>
							<a class="deleteGroup" href="#">
								<?php echo FabrikHelperHTML::image('minus-sign.png', 'form', $this->tmpl, JText::_('COM_FABRIK_DELETE_GROUP'));?>
							</a>
						</div>
					<?php } ?>
				</div>
				<?php
			}
		} else {
			$this->elements = $group->elements;
			echo $this->loadTemplate('group');
		}?>
	</fieldset>
<?php
	}
	echo $this->hiddenFields;
	?>
	<?php echo $this->pluginbottom; ?>
	<div class="fabrikActions"><?php echo $form->resetButton;?> <?php echo $form->submitButton;?>
	<?php echo $form->copyButton  . " " . $form->gobackButton . ' ' . $this->message?>
	</div>
</form>
<?php
echo FabrikHelperHTML::keepalive();
?>