<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Fabrik Join Model
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikFEModelJoin extends FabModel
{
	/**
	 * Join table
	 *
	 * @var object
	 */
	protected $join = null;

	/**
	 * Join id to load
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * Data to bind to Join table
	 *
	 * @var array
	 */
	protected $data = null;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since       1.5
	 */

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Set the join id
	 *
	 * @param   int  $id  join id
	 *
	 * @return  void
	 */

	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Get the join id
	 *
	 * @return  int  join id
	 */

	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set data
	 *
	 * @param   array  $data  to set to
	 *
	 * @return  void
	 */

	public function setData($data)
	{
		$this->data = $data;
	}

	/**
	 * Get Join
	 *
	 * @return  FabTable
	 */

	public function getJoin()
	{
		if (!isset($this->join))
		{
			$this->join = FabTable::getInstance('join', 'FabrikTable');
			if (isset($this->data))
			{
				$this->join->bind($this->data);
			}
			else
			{
				$this->join->load($this->id);
			}
			$this->paramsType($this->join);
		}
		return $this->join;
	}

	/**
	 * When loading the join JTable ensure its params are set to be a JRegistry item
	 *
	 * @param  JTable  &$join  Join table
	 *
	 * @return  void
	 */

	private function paramsType(&$join)
	{
		if (is_string($join->params))
		{
			$join->params = trim($join->params) == '' ? '{"type": ""}' : $join->params;
			$join->params = new JRegistry($join->params);
		}

		// Set a default join alias - normally overwritten in listModel::_makeJoinAliases();
		$join->table_join_alias = $join->table_join;
	}

	/**
	 * Clear the join
	 *
	 * @return  void
	 */

	public function clearJoin()
	{
		unset($this->_join);
	}

	/**
	 * Load the model from the element id
	 *
	 * @param   string  $key  Db table key
	 * @param   int     $id   Key value
	 *
	 * @return  FabTable  join
	 */

	public function getJoinFromKey($key, $id)
	{
		if (!isset($this->join))
		{
			$db = FabrikWorker::getDbo(true);
			$this->join = FabTable::getInstance('join', 'FabrikTable');
			$this->join->load(array($key => $id));
			$this->paramsType($this->join);
		}
		return $this->join;
	}

	/**
	 * Get join table's primary key
	 *
	 * @param   string  $glue  Between table and field name
	 *
	 * @return  string
	 */

	public function getPrimaryKey($glue = '___')
	{
		$join = $this->getJoin();
		$pk = str_replace('`', '', $join->params->get('pk'));
		$pk = str_replace('.', $glue, $pk);
		return $pk;
	}

	/**
	 * Get foreign key
	 *
	 * @param   string  $glue  Between table and field name
	 *
	 * @return string
	 */

	public function getForeignKey($glue = '___')
	{
		$join = $this->getJoin();
		$fk = $join->table_join . $glue . $join->table_join_key;
		return $fk;
	}

	/**
	 * Get joined to table primary key
	 *
	 * @param   string  $glue  Between table and field name
	 *
	 * @return string
	 */

	public function getJoinedToTablePk($glue = '___')
	{
		$join = $this->getJoin();
		return $join->join_from_table . $glue . $join->table_key;
	}

	/**
	 * Set the join element ID
	 *
	 * @param   int  $id  element id
	 *
	 * @return  void
	 */

	public function setElementId($id)
	{
		$this->join->element_id = $id;
	}

	/**
	 * deletes the loaded join and then
	 * removes all elements, groups & form group record
	 *
	 * @param   int  $groupId  the group id that the join is linked to
	 *
	 * @return void/JError
	 */

	public function deleteAll($groupId)
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->delete(' #__{package}_elements')->where('group_id = ' . (int) $groupId);
		$db->setQuery($query);
		if (!$db->execute())
		{
			return JError::raiseError(500, $db->getErrorMsg());
		}
		$query->clear();
		$query->delete(' #__{package}_groups')->where('id = ' . (int) $groupId);
		$db->setQuery($query);
		if (!$db->execute())
		{
			return JError::raiseError(500, $db->getErrorMsg());
		}

		// Delete all form group records
		$query->clear();
		$query->delete(' #__{package}_formgroup')->where('group_id = ' . (int) $groupId);
		$db->setQuery($query);
		if (!$db->execute())
		{
			return JError::raiseError(500, $db->getErrorMsg());
		}
		$this->getJoin()->delete();
	}

	/**
	 * saves the table join data
	 *
	 * @param   array  $source  data to save
	 *
	 * @return  bool
	 */

	public function save($source)
	{
		if (!$this->bind($source))
		{
			return false;
		}
		if (!$this->check())
		{
			return false;
		}
		if (!$this->store())
		{
			return false;
		}
		$this->_error = '';
		return true;
	}

}
