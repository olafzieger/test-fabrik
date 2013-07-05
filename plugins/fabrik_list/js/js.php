<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.js
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-list.php';

/**
 *  Add an action button to run PHP
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.js
 * @since       3.0
 */

class PlgFabrik_ListJs extends PlgFabrik_List
{

	/**
	 * Button prefix
	 * @var  string
	 */
	protected $buttonPrefix = 'js';

	/**
	 * Prep the button if needed
	 *
	 * @param   object  $params  Plugin params
	 * @param   object  &$model  List model
	 * @param   array   &$args   Arguements
	 *
	 * @return  bool;
	 */

	 public function button($params, &$model, &$args)
	{
		parent::button($params, $model, $args);
		return true;
	}

	/**
	 * Get the button label
	 *
	 * @return  string
	 */

	protected function buttonLabel()
	{
		return $this->getParams()->get('button_label', parent::buttonLabel());
	}

	/**
	 * Get the parameter name that defines the plugins acl access
	 *
	 * @return  string
	 */

	protected function getAclParam()
	{
		return 'access';
	}

	/**
	 * Can the plug-in select list rows
	 *
	 * @return  bool
	 */

	public function canSelectRows()
	{
		return true;
	}

	/**
	 * Return the javascript to create an instance of the class defined in formJavascriptClass
	 *
	 * @param   object  $params  Plugin parameters
	 * @param   object  $model   List model
	 * @param   array   $args    Array [0] => string table's form id to contain plugin
	 *
	 * @return bool
	 */

	public function onLoadJavascriptInstance($params, $model, $args)
	{
		parent::onLoadJavascriptInstance($params, $model, $args);
		$opts = $this->getElementJSOptions($model);
		$file = $params->get('js_file', '');
		if ($file !== '' && $file !== '-1')
		{
			$opts->js_code = JFile::read(JPATH_ROOT . '/plugins/fabrik_list/js/scripts/' . $file);
		}
		else
		{
			$opts->js_code = $params->get('js_code', '');
		}
		$opts->statusMsg = $params->get('msg', '');
		$opts = json_encode($opts);
		$this->jsInstance = "new FbListJs($opts)";
		return true;
	}

}
