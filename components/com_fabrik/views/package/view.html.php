<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewPackage extends JViewLegacy
{

	function display($tpl = null)
	{
		FabrikHelperHTML::framework();
		$app = JFactory::getApplication();
		$input = $app->input;
		$state = $this->get('State');
		$item = $this->get('Item');
		$document = JFactory::getDocument();
		$srcs = array('media/com_fabrik/js/icons.js', 'media/com_fabrik/js/icongen.js', 'media/com_fabrik/js/canvas.js',
		'media/com_fabrik/js/history.js', 'media/com_fabrik/js/keynav.js', 'media/com_fabrik/js/tabs.js',
		'media/com_fabrik/js/pages.js', 'media/com_fabrik/js/frontpackage.js');

		FabrikHelperHTML::script($srcs);

		FabrikHelperHTML::stylesheet('media/com_fabrik/css/package.css');
		$canvas = $item->params->get('canvas');

		// $$$ rob 08/11/2011 test if component name set but still rendering
		// in option=com_fabrik then we should use fabrik as the package
		if ($input->get('option') === 'com_fabrik')
		{
			$item->component_name = 'fabrik';
		}
		$opts = JArrayHelper::getvalue($canvas, 'options', array());
		$tabs = JArrayHelper::getValue($canvas, 'tabs', array('Page 1'));
		$tabs = json_encode($tabs);
		$d = new stdClass;
		$layout = JArrayHelper::getValue($canvas, 'layout', $d);

		$layout = json_encode(JArrayHelper::getValue($canvas, 'layout', $d));
		$id = $this->get('State')->get('package.id');
		$script = "window.addEvent('fabrik.loaded', function() {
			new FrontPackage({
		tabs : $tabs,
		tabelement : 'packagemenu',
		pagecontainer : 'packagepages',
		layout: $layout,
		'packageId': $id,
		'package':'$item->component_name'
	});
		});";
		FabrikHelperHTML::addScriptDeclaration($script);

		// Force front end templates
		$this->_basePath = COM_FABRIK_FRONTEND . '/views';
		$tmpl = !isset($item->template) ? 'default' : $item->template;
		$this->addTemplatePath($this->_basePath . '/' . $this->_name . '/tmpl/' . $tmpl);
		$text = $this->loadTemplate();
		FabrikHelperHTML::runConentPlugins($text);
		echo $text;
	}

}
