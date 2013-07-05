<?php
/**
 * Plugin element to render multi select user group list
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.usergroup
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugin element to render multi select user group list
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.usergroup
 * @since       3.0.6
 */

class PlgFabrik_ElementUsergroup extends PlgFabrik_ElementList
{

	/**
	 * Db table field type
	 *
	 * @var string
	 */
	protected $fieldDesc = 'TEXT';

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           To preopulate element with
	 * @param   int    $repeatCounter  Repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$element = $this->getElement();
		$name = $this->getHTMLName($repeatCounter);
		$html_id = $this->getHTMLId($repeatCounter);
		$id = $html_id;
		$params = $this->getParams();

		$formModel = $this->getFormModel();
		$userEl = $formModel->getElement($params->get('user_element'), true);
		if ($userEl)
		{
			$data = $formModel->getData();
			$userid = JArrayHelper::getValue($data, $userEl->getFullName(true, false) . '_raw', 0);
			$thisUser = JFactory::getUser($userid);
		}
		$selected = $this->getValue($data, $repeatCounter);
		if (is_string($selected))
		{
			$selected = json_decode($selected);
		}
		if ($this->canUse())
		{
			return JHtml::_('access.usergroups', $name, $selected);
		}
		else
		{
			if ($userEl && !empty($thisUser->groups))
			{
				// Get the titles for the user groups.
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select($db->quoteName('title'));
				$query->from($db->quoteName('#__usergroups'));
				$query->where($db->quoteName('id') . ' IN ( ' . implode(' , ', $thisUser->groups) . ')');
				$db->setQuery($query);
				$selected = $db->loadColumn();
			}
			else
			{
				$selected = array();
			}
		}

		return implode(', ', $selected);
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */

	public function elementJavascript($repeatCounter)
	{
		$opts = parent::getElementJSOptions($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		return array('FbUsergroup', $id, $opts);
	}

	/**
	* Shows the data formatted for the list view
	*
	* @param   string  $data      Elements data
	* @param   object  &$thisRow  All the data in the lists current row
	*
	* @return  string	formatted value
	*/

	public function renderListData($data, &$thisRow)
	{
		$data = FabrikWorker::JSONtoData($data, true);
		JArrayHelper::toInteger($data);
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		if (!empty($data))
		{
			$query->select('title')->from('#__usergroups')->where('id IN (' . implode(',', $data) . ')');
			$db->setQuery($query);
			$data = $db->loadColumn();
		}
		$data = json_encode($data);
		return parent::renderListData($data, $thisRow);
	}

	/**
	 * Create an array of label/values which will be used to populate the elements filter dropdown
	 * returns all possible options
	 *
	 * @param   bool    $normal     Do we render as a normal filter or as an advanced search filter
	 * @param   string  $tableName  Table name to use - defaults to element's current table
	 * @param   string  $label      Field to use, defaults to element name
	 * @param   string  $id         Field to use, defaults to element name
	 * @param   bool    $incjoin    Include join
	 *
	 * @return  array	Filter value and labels
	 */

	protected function filterValueList_All($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, title');
		$query->from($db->quoteName('#__usergroups'));
		$db->setQuery($query);
		$selected = $db->loadObjectList();

		for ($i = 0; $i < count($selected); $i++)
		{
			$return[] = JHTML::_('select.option', $selected[$i]->id, $selected[$i]->title);
		}
		return $return;
	}

}
