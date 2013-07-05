<?php
/**
 * Fabrik Model
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

$version = new JVersion;
if ($version->RELEASE == '1.6')
{
	/**
	 * Fabrik Element List Model
	 *
	 * @package     Joomla
	 * @subpackage  Fabrik
	 * @since       3.0
	 */

	class FabModel extends JModelLegacy
	{
		/**
		 * Method to load and return a model object.
		 *
		 * @param   string  $name    The name of the view
		 * @param   string  $prefix  The class prefix. Optional.
		 * @param   array   $config  Options
		 *
		 * @return  mixed Model object or boolean false if failed
		 */

		private function _createTable($name, $prefix = 'Table', $config = array())
		{
			// Clean the model name
			$name = preg_replace('/[^A-Z0-9_]/i', '', $name);
			$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

			// Make sure we are returning a DBO object
			if (!array_key_exists('dbo', $config))
			{
				$config['dbo'] = $this->getDbo();
			}
			return FabTable::getInstance($name, $prefix, $config);
		}

		/**
		 * Method to get a table object, load it if necessary.
		 *
		 * @param   string  $name     The table name. Optional.
		 * @param   string  $prefix   The class prefix. Optional.
		 * @param   array   $options  Configuration array for model. Optional.
		 *
		 * @return	object	The table
		 */

		public function getTable($name = '', $prefix = 'Table', $options = array())
		{
			if (empty($name))
			{
				$name = $this->getName();
			}

			if ($table = $this->_createTable($name, $prefix, $options))
			{
				return $table;
			}
			JError::raiseError(JText::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name));
			return null;
		}
	}
}
else
{
	/**
	  * Fabrik Element List Model - Joomla 1.7 onwards
	 *
	 * @package     Joomla
	 * @subpackage  Fabrik
	 * @since       3.0
	 */

	class FabModel extends JModelLegacy
	{
		/**
		 * Method to load and return a model object.
		 *
		 * @param   string  $name    The name of the view
		 * @param   string  $prefix  The class prefix. Optional.
		 * @param   array   $config  configuration
		 *
		 * @return	mixed	Model object or boolean false if failed
		 */

		protected function _createTable($name, $prefix = 'Table', $config = array())
		{
			// Clean the model name
			$name = preg_replace('/[^A-Z0-9_]/i', '', $name);
			$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

			// Make sure we are returning a DBO object
			if (!array_key_exists('dbo', $config))
			{
				$config['dbo'] = $this->getDbo();
			}
			return FabTable::getInstance($name, $prefix, $config);
		}

		/**
		 * Method to get a table object, load it if necessary.
		 *
		 * @param   string  $name     The table name. Optional.
		 * @param   string  $prefix   The class prefix. Optional.
		 * @param   array   $options  Configuration array for model. Optional.
		 *
		 * @return	object	The table
		 */
		public function getTable($name = '', $prefix = 'Table', $options = array())
		{
			if (empty($name))
			{
				$name = $this->getName();
			}

			if ($table = $this->_createTable($name, $prefix, $options))
			{
				return $table;
			}
			JError::raiseError(JText::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name));
			return null;
		}
	}
}
