<?php
/**
 * Fabrik Timeline Viz Controller
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.timeline
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Fabrik Timeline Viz Controller
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.timeline
 * @since       3.0
 */

class FabrikControllerVisualizationtimeline extends FabrikControllerVisualization
{

	/**
	 * Get a series of timeline events
	 *
	 * @return  void
	 */

	public function ajax_getEvents()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$viewName = 'timeline';
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model = $this->getModel($viewName);
		$id = $input->getInt('visualizationid', 0);
		$model->setId($id);
		$model->onAjax_getEvents();
	}
}
