	<?php $element = $this->element; ?>
<div class="control-group <?php echo $element->containerClass . $element->span; ?>" <?php echo $element->containerProperties?>>
	<?php echo $element->label;?>

	<div class="controls">
		<?php if ($this->tipLocation == 'above') : ?>
			<p class="help-block"><?php echo $element->tipAbove ?></p>
		<?php endif ?>

		<div class="fabrikElement">
			<?php echo $element->element;?>
		</div>

		<span class="<?php echo $this->class?>">
			<?php echo $element->error ?>
		</span>

		<?php if ($this->tipLocation == 'side') : ?>
			<div class="help-block"><?php echo $element->tipSide ?></div>
		<?php endif ?>

	</div>

	<?php if ($this->tipLocation == 'below') :?>
		<div class="help-block"><?php echo $element->tipBelow ?></div>
	<?php endif ?>

</div><!--  end span -->
