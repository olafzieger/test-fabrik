<?php
/**
 * Akismet Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.akismet
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!

defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/validation_rule.php';

/**
 * Akismet Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.akismet
 * @since       3.0
 */

class PlgFabrik_ValidationruleAkismet extends PlgFabrik_Validationrule
{
	/**
	 * Plugin name
	 *
	 * @var string
	 */
	protected $pluginName = 'akismet';

	/**
	 * Validate the elements data against the rule
	 *
	 * @param   string  $data           To check
	 * @param   object  &$elementModel  Element Model
	 * @param   int     $pluginc        Plugin sequence ref
	 * @param   int     $repeatCounter  Repeat group counter
	 *
	 * @return  bool  true if validation passes, false if fails
	 */

	public function validate($data, &$elementModel, $pluginc, $repeatCounter)
	{
		$params = $this->getParams();
		$user = JFactory::getUser();
		if ($params->get('akismet-key') != '')
		{
			$username = $user->get('username') != '' ? $user->get('username') : $this->_randomSring();
			$email = $user->get('email') != '' ? $user->get('email') : $this->_randomSring() . '@' . $this->_randomSring() . 'com';
			require_once JPATH_COMPONENT . '/plugins/validationrule/akismet/akismet.class.php';
			$akismet_comment = array('author' => $username, 'email' => $user->get('email'), 'website' => JURI::base(), 'body' => $data);
			$akismet = new Akismet(JURI::base(), $params->get('akismet-key'), $akismet_comment);
			if ($akismet->errorsExist())
			{
				throw new RuntimeException("Couldn't connected to Akismet server!");
			}
			else
			{
				if ($akismet->isSpam())
				{
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Create a random string
	 *
	 * @return string
	 */

	protected function _randomSring()
	{
		return preg_replace('/([ ])/e', 'chr(rand(97,122))', '     ');
	}
}
