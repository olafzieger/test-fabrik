<div class="tab-pane" id="tab-publishing">

	<ul class="nav nav-tabs">
		<li class="active">
	    	<a data-toggle="tab" href="#publishing-details">
	    		<?php echo JText::_('COM_FABRIK_ELEMENT_LABEL_PUBLISHING_DETAILS'); ?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#publishing-rss">
	    		<?php echo JText::_('COM_FABRIK_ELEMENT_LABEL_RSS')?>
	    	</a>
	    </li>
	    <li>
	    	<a data-toggle="tab" href="#publishing-tips">
	    		<?php echo JText::_('COM_FABRIK_ELEMENT_LABEL_TIPS')?>
	    	</a>
	    </li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="publishing-details">
		    <fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('publishing') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="publishing-rss">
			<fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('rss') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="tab-pane" id="publishing-tips">
			<fieldset class="form-horizontal">
				<?php foreach ($this->form->getFieldset('tips') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>
	</div>
</div>
