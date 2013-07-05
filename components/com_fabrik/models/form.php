<?php
/**
 * Fabrik Form Model
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
require_once 'fabrikmodelform.php';
require_once COM_FABRIK_FRONTEND . '/helpers/element.php';

/**
 * Fabrik Form Model
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikFEModelForm extends FabModelForm
{

	/**
	 * id
	 * @var int
	 */
	public $id = null;

	/**
	 * Set to -1 if form in ajax module, set to 1+ if in package
	 *
	 * @var int
	 */
	public $packageId = 0;

	/**
	 * Form's group elements
	 *
	 * @var array
	 */
	protected $elements = null;

	/**
	 * List model assocated with form
	 *
	 * @var FabrikFEModelList
	 */
	protected $listModel = null;

	/**
	 * Group ids that are actually tablejoins [groupid->joinid]
	 *
	 * @var array
	 */
	public $aJoinGroupIds = array();

	/**
	 * If editable if 0 then show view only verion of form
	 *
	 * @var bol true
	 */
	public $editable = true;

	/**
	 * Validation rule classes
	 *
	 * @var array
	 */
	protected $validationRuleClasses = null;

	/**
	 * The form running as a mambot or module(true)
	 *
	 * @var bool
	 */
	public $isMambot = false;

	/**
	 *  Join objects for the form
	 *
	 *  @var array
	 */
	protected $aJoinObjs = array();

	/**
	 * Concat string to create full element names
	 *
	 * @var string
	 */
	public $joinTableElementStep = '___';

	/**
	 *  Parameters
	 *
	 *  @var JRegistry
	 */
	protected $params = null;

	/**
	 * Row id to submit
	 *
	 * @var int
	 */
	public $rowId = null;

	/**
	 * Submitted as ajax
	 *
	 * @since 3.0
	 * @var bool
	 */
	public $ajax = null;

	/**
	 * Form table
	 *
	 * @var JTable
	 */
	public $form = null;

	/**
	 * Last current element found in hasElement()
	 *
	 * @var object
	 */
	protected $currentElement = null;

	/**
	 * If true encase table and element names with "`" when getting elemenet list
	 *
	 * @var bool
	 */
	protected $addDbQuote = false;

	/**
	 * Form Data
	 *
	 * @var array
	 */
	public $formData = null;

	/**
	 * Form errors
	 *
	 * @var array
	 */
	public $errors = array();

	/**
	 * Uploader helper
	 *
	 * @var FabrikUploader
	 */
	protected $uploader = null;

	/**
	 * Pages (array containing group ids for each page in the form)
	 *
	 * @var array
	 */
	protected $pages = null;

	/**
	 * Session model deals with storing incomplete pages
	 *
	 * @var FabrikFEModelFormsession
	 */
	public $sessionModel = null;

	/**
	 * Modified data by any validation rule that uses replace functionality
	 *
	 * @var array
	 */
	public $modifiedValidationData = null;

	/**
	 * Group Models
	 *
	 * @var array
	 */
	public $groups = null;

	/**
	 * Store the form's previous data when processing
	 *
	 * @var array
	 * /
	public $origData = null;

	/**
	 * Stores elements not shown in the list view
	 * @var array
	 */
	protected $elementsNotInList = null;

	/**
	 * Form data
	 *
	 * @var array
	 */
	public $data = null;

	/**
	 * Form data - keys use the full element name (listname___elementname)
	 * @var unknown_type
	 */
	public $formDataWithTableName = null;

	/**
	 * Should the form store the main row? Set to false in juser
	 * plugin if fabrik table is also #__users
	 *
	 * @var bool
	 */
	public $storeMainRow = true;

	/**
	 * Query used to load form record.
	 *
	 * @var string
	 */
	public $query = null;

	/**
	 * Specifies element name that have been overridden from a form plugin,
	 * so encrypted RO data should be ignored
	 *
	 * @var array
	 */
	protected $pluginUpdatedElements = array();

	/**
	 * Linked fabrik lists
	 *
	 * @var array
	 */
	protected $linkedFabrikLists = null;

	/**
	 * Are we copying a row?  i.e. using form's Copy button.  Plugin manager needs to know.
	 *
	 *  @var bool
	 */
	public $copyingRow = false;

	/**
	 * Container string for form plugin JS ini code
	 *
	 * @since 3.1b
	 *
	 * @var string
	 */
	public $formPluginJS = '';

	/**
	 * Form plugin files to load
	 *
	 * @since 3.1b
	 *
	 * @var array
	 */
	public $formPluginShim = array();

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
		$app = JFactory::getApplication();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$id = $app->input->getInt('formid', $usersConfig->get('formid'));
		$this->setId($id);
	}

	/**
	 * Method to set the form id
	 *
	 * @param   int  $id  list ID number
	 *
	 * @since 3.0
	 *
	 * @return  void
	 */

	public function setId($id)
	{
		// Set new form ID
		$this->id = $id;
		$this->setState('form.id', $id);

		// $$$ rob not sure why but we need this getState() here when assinging id from admin view
		$this->getState();
	}

	/**
	 * Set row id
	 *
	 * @param   string  $id  primary key value
	 *
	 * @since   3.0.7
	 *
	 * @return  void
	 */

	public function setRowId($id)
	{
		$this->rowId = $id;
	}

	/**
	 * Method to get the form id
	 *
	 * @return  int
	 */

	public function getId()
	{
		return $this->getState('form.id');
	}

	/**
	 * Get form table (alias to getTable())
	 *
	 * @return  object  form table
	 */

	public function getForm()
	{
		return $this->getTable();
	}

	/**
	 * Checks if the params object has been created and if not creates and returns it
	 *
	 * @return  object  params
	 */

	public function getParams()
	{
		if (!isset($this->params))
		{
			$form = $this->getForm();
			$this->params = new JRegistry($form->params);
		}
		return $this->params;
	}

	/**
	 * Makes sure that the form is not viewable based on the list's access settings
	 *
	 * Also sets the form's editable state, if it can record in to a db table
	 *
	 * @return  int  0 = no access, 1 = view only , 2 = full form view, 3 = add record only
	 */

	public function checkAccessFromListSettings()
	{
		$form = $this->getForm();
		$app = JFactory::getApplication();
		if ($form->record_in_database == 0)
		{
			return 2;
		}
		$listModel = $this->getListModel();
		if (!is_object($listModel))
		{
			return 2;
		}
		$data = $this->getData();
		$ret = 0;
		if ($listModel->canViewDetails())
		{
			$ret = 1;
		}
		$pRowid = FabrikWorker::getMenuOrRequestVar('rowid', '', $this->isMambot);

		// New form can we add?
		if ($this->rowId == 0 || $pRowid == '-1')
		{
			// If they can edit can they also add
			if ($listModel->canAdd())
			{
				$ret = 3;
			}
			// $$$ hugh - corner case for rowid=-1, where they DON'T have add perms, but DO have edit perms
			elseif ($pRowid == '-1' && $listModel->canEdit($data))
			{
				$ret = 2;
			}
		}
		else
		{
			// Editing from - can we edit
			if ($listModel->canEdit($data))
			{
				$ret = 2;
			}
		}
		// If no access (0) or read only access (1) set the form to not be editable
		$editable = ($ret <= 1) ? false : true;
		$this->setEditable($editable);
		if ($app->input->get('view', 'form') == 'details')
		{
			$this->setEditable(false);
		}
		return $ret;
	}

	/**
	 * Get the template name
	 *
	 * @since 3.0
	 *
	 * @return string tmpl name
	 */

	public function getTmpl()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$params = $this->getParams();
		$item = $this->getForm();
		$tmpl = '';
		$default = FabrikWorker::j3() ? 'bootstrap' : 'default';
		$document = JFactory::getDocument();
		if ($document->getType() === 'pdf')
		{
			$tmpl = $params->get('pdf_template', '') !== '' ? $params->get('pdf_template') : $default;
		}
		else
		{
			if ($app->isAdmin())
			{
				$tmpl = $this->isEditable() ? $params->get('admin_form_template') : $params->get('admin_details_template');
				$tmpl = $tmpl == '' ? $default : $tmpl;
			}
			if ($tmpl == '')
			{
				if ($this->isEditable())
				{
					$tmpl = $item->form_template == '' ? $default : $item->form_template;
				}
				else
				{
					$tmpl = $item->view_only_template == '' ? $default : $item->view_only_template;
				}
			}
		}
		$tmpl = FabrikWorker::getMenuOrRequestVar('fabriklayout', $tmpl, $this->isMambot);

		// Finally see if the options are overridden by a querystring var
		$baseTmpl = $tmpl;
		$tmpl = $input->get('layout', $tmpl);

		// Test it exists - otherwise revert to baseTmpl tmpl
		$folder = $this->isEditable() ? 'form' : 'details';
		if (!JFolder::exists(JPATH_SITE . '/components/com_fabrik/views/' . $folder . '/tmpl/' . $tmpl))
		{
			$tmpl = $baseTmpl;
		}
		$item->form_template = $tmpl;
		return $tmpl;
	}

	/**
	 * loads form's css files
	 * Checks : custom css file, template css file. Including them if found
	 *
	 * @return  void
	 */

	public function getFormCss()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$jTmplFolder = FabrikWorker::j3() ? 'tmpl' : 'tmpl25';
		$tmpl = $this->getTmpl();
		$v = $this->isEditable() ? 'form' : 'details';

		// Check for a form template file (code moved from view)
		if ($tmpl != '')
		{
			$qs = '?c=' . $this->getId();

			/* $$$ need &amp; for pdf output which is parsed through xml parser otherwise fails
			 * If FabrikHelperHTML::styleSheetajax loaded then dont do &amp;
			 */
			$qs .= FabrikHelperHTML::cssAsAsset() ? '&view=' . $v : '&amp;view=' . $v;
			$tmplPath = 'templates/' . $app->getTemplate() . '/html/com_fabrik/form/' . $tmpl . '/template_css.php' . $qs;
			if (!FabrikHelperHTML::stylesheetFromPath($tmplPath))
			{
				FabrikHelperHTML::stylesheetFromPath('components/com_fabrik/views/form/' . $jTmplFolder . '/' . $tmpl . '/template_css.php' . $qs);
			}
			/* $$$ hugh - as per Skype convos with Rob, decided to re-instate the custom.css convention.  So I'm adding two files:
			 * custom.css - for backward compat with existing 2.x custom.css
			 * custom_css.php - what we'll recommend people use for custom css moving foward.
			 */
			if (!FabrikHelperHTML::stylesheetFromPath('templates/' . $app->getTemplate() . '/html/com_fabrik/form/' . $tmpl . '/custom.css' . $qs))
			{
				FabrikHelperHTML::stylesheetFromPath('components/com_fabrik/views/form/' . $jTmplFolder . '/' . $tmpl . '/custom.css' . $qs);
			}
			if (!FabrikHelperHTML::stylesheetFromPath('templates/' . $app->getTemplate() . '/html/com_fabrik/form/' . $tmpl . '/custom_css.php' . $qs))
			{
				FabrikHelperHTML::stylesheetFromPath('components/com_fabrik/views/form/' . $jTmplFolder . '/' . $tmpl . '/custom_css.php' . $qs);
			}
		}
		if ($app->isAdmin() && $input->get('tmpl') === 'components')
		{
			FabrikHelperHTML::stylesheet('administrator/templates/system/css/system.css');
		}
	}

	/**
	 * Load the JS files into the document
	 *
	 * @param   array  &$srcs  js script srcs to load in the head
	 *
	 * @return null
	 */

	public function getCustomJsAction(&$srcs)
	{
		// $$$ hugh - added ability to use form_XX, as am adding custom list_XX
		$view = $this->isEditable() ? 'form' : 'details';
		if (JFile::exists(COM_FABRIK_FRONTEND . '/js/' . $this->getId() . '.js'))
		{
			$srcs[] = 'components/com_fabrik/js/' . $this->getId() . '.js';
		}
		elseif (JFile::exists(COM_FABRIK_FRONTEND . '/js/' . $view . '_' . $this->getId() . '.js'))
		{
			$srcs[] = 'components/com_fabrik/js/' . $view . '_' . $this->getId() . '.js';
		}
	}

	/**
	 * Set the page title for form
	 *
	 * @param   string  $title  default title
	 *
	 * @return	string	page title
	 */

	public function getPageTitle($title = '')
	{
		$params = $this->getParams();
		$label = $this->getLabel();
		$app = JFactory::getApplication();
		$input = $app->input;
		if ($input->get('view') == 'details')
		{
			if (!$params->get('show-title-in-detail-view', true))
			{
				$title = '';
			}
			else
			{
				$title = ($title == '') ? $label : $title . " ";
			}
		}
		else
		{
			$title = ($title == '') ? $label : $title . " ";
		}
		$groups = $this->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel)
			{
				$element = $elementModel->getElement();
				if ($element->use_in_page_title == '1')
				{
					$default = $elementModel->getTitlePart($this->data);
					$s = is_array($default) ? implode(', ', $default) . ' ' : $default . ' ';
					$title .= ' ' . $s;
				}
			}
		}
		return $title;
	}

	/**
	 * Compares the forms table with its groups to see if any of the groups are in fact table joins
	 *
	 * @param   array  $joins  tables joins
	 *
	 * @return	array	array(group_id =>join_id)
	 */

	public function getJoinGroupIds($joins)
	{
		$arJoinGroupIds = array();
		$groups = $this->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{
			foreach ($joins as $join)
			{
				if ($join->element_id == 0 && $groupModel->getGroup()->id == $join->group_id)
				{
					$arJoinGroupIds[$groupModel->getId()] = $join->id;
				}
			}
		}
		$this->aJoinGroupIds = $arJoinGroupIds;
		return $arJoinGroupIds;
	}

	/**
	 * Gets the javascript actions the forms elements
	 *
	 * @return  array  javascript actions
	 */

	public function getJsActions()
	{
		if (isset($this->jsActions))
		{
			return $this->jsActions;
		}
		$this->jsActions = array();
		$db = FabrikWorker::getDbo(true);
		$j = new JRegistry;
		$aJsActions = array();
		$aElIds = array();
		$groups = $this->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel)
			{
				/* $$$ hugh - only needed getParent when we weren't saving changes to parent params to child
				 * which we should now be doing ... and getParent() causes an extra table lookup for every child
				 * element on the form.
				 */
				$aJsActions[$elementModel->getElement()->id] = array();
				$aElIds[] = (int) $elementModel->getElement()->id;
			}
		}
		if (!empty($aElIds))
		{
			$query = $db->getQuery(true);
			$query->select('*')->from('#__{package}_jsactions')->where('element_id IN (' . implode(',', $aElIds) . ')');
			$db->setQuery($query);
			$res = $db->loadObjectList();
			if ($db->getErrorNum())
			{
				JError::raiseError(500, $db->getErrorMsg());
			}
		}
		else
		{
			$res = array();
		}
		if (is_array($res))
		{
			foreach ($res as $r)
			{
				// Merge the js attribs back into the array
				$a = json_decode($r->params);
				foreach ($a as $k => $v)
				{
					$r->$k = $v;
				}
				unset($r->params);
				if (!isset($r->js_published) || (int) $r->js_published === 1)
				{
					$this->jsActions[$r->element_id][] = $r;
				}
			}
		}
		return $this->jsActions;
	}

	/**
	 * Test to try to load all group data in one query and then bind that data to group table objects
	 * in getGroups()
	 *
	 * @return  array
	 */

	public function getPublishedGroups()
	{
		$db = FabrikWorker::getDbo(true);
		if (!isset($this->_publishedformGroups) || empty($this->_publishedformGroups))
		{
			$params = $this->getParams();
			$query = $db->getQuery(true);
			$query->select(' *, fg.group_id AS group_id, RAND() AS rand_order')
			->from('#__{package}_formgroup AS fg')
			->join('INNER', '#__{package}_groups as g ON g.id = fg.group_id')
			->where('fg.form_id = ' . (int) $this->getId() . ' AND published = 1');
			if ($params->get('randomise_groups') == 1)
			{
				$query->order('rand_order');
			}
			else
			{
				$query->order('fg.ordering');
			}
			$db->setQuery($query);
			$groups = $db->loadObjectList('group_id');
			if ($db->getErrorNum())
			{
				JError::raiseError(500, $db->getErrorMsg());
			}
			$this->_publishedformGroups = $this->mergeGroupsWithJoins($groups);
		}
		return $this->_publishedformGroups;
	}

	/**
	 * Get the ids of all the groups in the form
	 *
	 * @return  array  group ids
	 */

	public function getGroupIds()
	{
		$groups = $this->getPublishedGroups();
		return array_keys($groups);
	}

	/**
	 * Force load in the group ids
	 * separate from getGroupIds as you need to force load these
	 * when saving the table
	 *
	 * @deprecated - not used?
	 *
	 * @return  array  group ids
	 */

	protected function _loadGroupIds()
	{
		unset($this->_publishedformGroups);
		return $this->getGroupIds();
	}

	/**
	 * Merge in Join Ids into an array of groups
	 *
	 * @param   array  $groups  form groups
	 *
	 * @return  array
	 */

	private function mergeGroupsWithJoins($groups)
	{
		$db = FabrikWorker::getDbo(true);
		$form = $this->getForm();
		if ($form->record_in_database)
		{
			$listModel = $this->getListModel();
			$listid = (int) $listModel->getId();
			if (is_object($listModel) && $listid !== 0)
			{
				$query = $db->getQuery(true);
				$query->select('g.id, j.id AS joinid')->from('#__{package}_joins AS j')
					->join('INNER', '#__{package}_groups AS g ON g.id = j.group_id')->where('list_id = ' . $listid . ' AND g.published = 1');

				// Added as otherwise you could potentially load a element joinid as a group join id. 3.1
				$query->where('j.element_id = 0');
				$db->setQuery($query);
				$joinGroups = $db->loadObjectList('id');
				foreach ($joinGroups as $k => $o)
				{
					if (array_key_exists($k, $groups))
					{
						$groups[$k]->join_id = $o->joinid;
					}
				}
			}
		}
		return $groups;
	}

	/**
	 * Get the forms published group objects
	 *
	 * @return  array  Group model objects with table row loaded
	 */

	public function getGroups()
	{
		if (!isset($this->groups))
		{
			$this->groups = array();
			$listModel = $this->getListModel();
			$groupModel = JModelLegacy::getInstance('Group', 'FabrikFEModel');
			$groupdata = $this->getPublishedGroups();
			foreach ($groupdata as $id => $groupd)
			{
				$thisGroup = clone ($groupModel);
				$thisGroup->setId($id);
				$thisGroup->setContext($this, $listModel);

				// $$ rob 25/02/2011 this was doing a query per group - pointless as we bind $groupd to $row afterwards
				// $row = $thisGroup->getGroup();
				$row = FabTable::getInstance('Group', 'FabrikTable');
				$row->bind($groupd);
				$thisGroup->setGroup($row);
				if ($row->published == 1)
				{
					$this->groups[$id] = $thisGroup;
				}
			}
		}
		return $this->groups;
	}

	/**
	 * Gets each element in the form along with its group info
	 *
	 * @param   bool  $excludeUnpublished  included unpublished elements in the result
	 *
	 * @return  array  element objects
	 */

	public function getFormGroups($excludeUnpublished = true)
	{
		$params = $this->getParams();
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query
			->select(
				'*, #__{package}_groups.params AS gparams, #__{package}_elements.id as element_id
		, #__{package}_groups.name as group_name, RAND() AS rand_order')->from('#__{package}_formgroup')
			->join('LEFT', '#__{package}_groups	ON #__{package}_formgroup.group_id = #__{package}_groups.id')
			->join('LEFT', '#__{package}_elements ON #__{package}_groups.id = #__{package}_elements.group_id')
			->where('#__{package}_formgroup.form_id = ' . (int) $this->getState('form.id'));

		if ($excludeUnpublished)
		{
			$query->where('#__{package}_elements.published = 1');
		}
		if ($params->get('randomise_groups') == 1)
		{
			$query->order('rand_order, #__{package}_elements.ordering');
		}
		else
		{
			$query->order('#__{package}_formgroup.ordering, #__{package}_formgroup.group_id, #__{package}_elements.ordering');
		}
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		if ($db->getErrorNum())
		{
			JError::raiseError(500, $db->getErrorMsg());
		}
		$this->elements = $groups;
		return $groups;
	}

	/**
	 * Similar to getFormGroups() except that this returns a data structure of
	 * form
	 * --->group
	 * -------->element
	 * -------->element
	 * --->group
	 * if run before then existing data returned
	 *
	 * @return  array  element objects
	 */

	public function getGroupsHiarachy()
	{
		if (!isset($this->groups))
		{
			$this->getGroups();
			$this->groups = FabrikWorker::getPluginManager()->getFormPlugins($this);
		}
		return $this->groups;
	}

	/**
	 * Get an list of elements that aren't shown in the table view
	 *
	 * @return  array  of element table objects
	 */

	public function getElementsNotInTable()
	{
		if (!isset($this->elementsNotInList))
		{
			$this->elementsNotInList = array();
			$groups = $this->getGroupsHiarachy();
			foreach ($groups as $group)
			{
				$elements = $group->getPublishedElements();
				foreach ($elements as $elementModel)
				{
					if ($elementModel->canView() || $elementModel->canUse())
					{
						$element = $elementModel->getElement();
						if (!isset($element->show_in_list_summary) || !$element->show_in_list_summary)
						{
							$this->elementsNotInList[] = $element;
						}
					}
				}

			}
		}
		return $this->elementsNotInList;
	}

	/**
	 * This checks to see if the form has a file upload element
	 * and returns the correct encoding type for the form
	 *
	 * @return  string  form encoding type
	 */

	public function getFormEncType()
	{
		$groups = $this->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel)
			{
				if ($elementModel->isUpload())
				{
					return "multipart/form-data";
				}
			}
		}
		return "application/x-www-form-urlencoded";
	}

	/**
	 * Run a method on all the element plugins in the form
	 *
	 * @param   string  $method  method to call
	 * @param   array   $data    posted form data
	 *
	 * @deprecated - not used?
	 *
	 * @return  void
	 */

	public function runElementPlugins($method, $data)
	{
		$groups = $this->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel)
			{
				$params = $elementModel->getParams();
				if (method_exists($elementModel, $method))
				{
					$elementModel->$method($params, $data);
				}
			}
		}
	}

	/**
	 * Get the plugin manager
	 *
	 * @deprecated use return FabrikWorker::getPluginManager(); instead since 3.0b
	 *
	 * @return  object  plugin manager
	 */

	public function getPluginManager()
	{
		return FabrikWorker::getPluginManager();
	}

	/**
	 * When the form is submitted we want to get the orginal record it
	 * is updating - this is used in things like the fileupload element
	 * to check for changes in uploaded files and process the difference
	 *
	 * @return	array
	 */

	protected function setOrigData()
	{
		$app = JFactory::getApplication();
		if ($app->input->getString('rowid', '', 'string') == '')
		{
			$this->_origData = array(new stdClass);
		}
		else
		{
			$listModel = $this->getListModel();
			$fabrikDb = $listModel->getDb();
			$sql = $this->buildQuery();
			$fabrikDb->setQuery($sql);
			$this->_origData = $fabrikDb->loadObjectList();
		}
	}

	/**
	 * Get the form record's original data - before any alterations were made to it
	 * in the form
	 *
	 * @return  array
	 */

	public function getOrigData()
	{
		if (!isset($this->_origData))
		{
			$this->setOrigData();
		}
		return $this->_origData;
	}

	/**
	 * Are we copying a row?  Usually set in controller process().
	 *
	 * @param   bool  $set  if true, set copyingRow to true
	 *
	 * @return	bool
	 */

	public function copyingRow($set = false)
	{
		if ($set)
		{
			$this->copyingRow = true;
		}
		return $this->copyingRow;
	}

	/**
	 * Processes the form data and decides what action to take
	 *
	 * @return  bool  false if one of the plugins reuturns an error otherwise true
	 */

	public function process()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		error_reporting(error_reporting() ^ (E_WARNING | E_NOTICE));
		@set_time_limit(300);
		require_once COM_FABRIK_FRONTEND . '/helpers/uploader.php';
		$form = $this->getForm();
		$pluginManager = FabrikWorker::getPluginManager();
		$params = $this->getParams();

		$sessionModel = JModelLegacy::getInstance('Formsession', 'FabrikFEModel');
		$sessionModel->setFormId($this->getId());
		$sessionModel->setRowId($this->rowId);
		/* $$$ rob rowId can be updated by juser plugin so plugin can use check (for new/edit)
		 * now looks at origRowId
		 */
		$this->origRowId = $this->rowId;

		$this->getGroupsHiarachy();

		if ($form->record_in_database == '1')
		{
			$this->setOrigData();
		}

		if (in_array(false, $pluginManager->runPlugins('onBeforeProcess', $this)))
		{
			return false;
		}
		$this->removeEmptyNoneJoinedGroupData($this->formData);

		$this->setFormData();

		if (!$this->_doUpload())
		{
			return false;
		}
		/** $$$ rob 27/10/2011 - moved above _doUpload as code in there is trying to update formData which is not yet set
		 * this->setFormData();
		 */

		if (in_array(false, $pluginManager->runPlugins('onBeforeStore', $this)))
		{
			return false;
		}

		$this->formDataWithTableName = $this->formData;
		if ($form->record_in_database == '1')
		{
			$this->processToDB();
		}

		// Clean the cache.
		$cache = JFactory::getCache($input->get('option'));
		$cache->clean();

		// $$$rob run this before as well as after onAfterProcess (ONLY for redirect plugin)
		// so that any redirect urls are available for the plugin (e.g twitter)
		$pluginManager->runPlugins('onLastProcess', $this);

		if (in_array(false, $pluginManager->runPlugins('onAfterProcess', $this)))
		{
			// $$$ rob this no longer stops default redirect (not needed any more)
			// returning false here stops the default redirect occuring
			return false;
		}
		// Need to remove the form session before redirect plugins occur
		$sessionModel->remove();

		// $$$rob used ONLY for redirect plugins
		if (in_array(false, $pluginManager->runPlugins('onLastProcess', $this)))
		{
			// $$$ rob this no longer stops default redirect (not needed any more)
			// returning false here stops the default redirect occuring
			return false;
		}
		$package = $app->getUserState('com_fabrik.package', 'fabrik');

		// Clean both admin and front end cache.
		parent::cleanCache('com_' . $package, 1);
		parent::cleanCache('com_' . $package, 0);
		return true;
	}

	/**
	 * Perform file uploads
	 *
	 * @return bool
	 */

	protected function _doUpload()
	{
		$oUploader = $this->getUploader();
		$oUploader->upload();
		if ($oUploader->moveError)
		{
			return false;
		}
		return true;
	}

	/**
	 * Update the data that gets posted via the form and stored by the form
	 * model. Used in elements to modify posted data see fabrikfileupload
	 *
	 * @param   string  $key          in key.dot.format to set a recursive array
	 * @param   string  $val          value to set to
	 * @param   bool    $update_raw   automatically update _raw key as well
	 * @param   bool    $override_ro  update data even if element is RO
	 *
	 * @return  void
	 */

	public function updateFormData($key, $val, $update_raw = false, $override_ro = false)
	{
		if (strstr($key, '.'))
		{
			$nodes = explode('.', $key);
			$count = count($nodes);
			$pathNodes = $count - 1;
			if ($pathNodes < 0)
			{
				$pathNodes = 0;
			}
			$ns = &$this->formData;
			for ($i = 0; $i <= $pathNodes; $i++)
			{
				// If any node along the registry path does not exist, create it
				if (!isset($ns[$nodes[$i]]))
				{
					$ns[$nodes[$i]] = array();
				}
				$ns = &$ns[$nodes[$i]];
			}
			$ns = $val;

			// $$$ hugh - changed name of $ns, as re-using after using it to set by reference was borking things up!
			$ns_full = &$this->fullFormData;
			for ($i = 0; $i <= $pathNodes; $i++)
			{
				// If any node along the registry path does not exist, create it
				if (!isset($ns_full[$nodes[$i]]))
				{
					$ns_full[$nodes[$i]] = array();
				}
				$ns_full = &$ns_full[$nodes[$i]];
			}
			$ns_full = $val;

			// $$$ hugh - FIXME - nope, this won't work!  We don't know which path node is the element name.
			// $$$ hugh again - should now work, with little preg_replace hack, if last part is numeric, then second to last will be element name
			if ($update_raw)
			{
				if (preg_match('#\.\d+$#', $key))
				{
					$key = preg_replace('#(.*)(\.\d+)$#', '$1_raw$2', $key);
				}
				else
				{
					$key .= '_raw';
				}
				$nodes = explode('.', $key);
				$count = count($nodes);
				$pathNodes = $count - 1;
				if ($pathNodes < 0)
				{
					$pathNodes = 0;
				}
				$ns_raw = &$this->formData;
				for ($i = 0; $i <= $pathNodes; $i++)
				{
					// If any node along the registry path does not exist, create it
					if (!isset($ns_raw[$nodes[$i]]))
					{
						$ns_raw[$nodes[$i]] = array();
					}
					$ns_raw = &$ns_raw[$nodes[$i]];
				}
				$ns_raw = $val;

				$ns_raw_full = $this->_fullFormData;
				for ($i = 0; $i <= $pathNodes; $i++)
				{
					// If any node along the registry path does not exist, create it
					if (!isset($ns_raw_full[$nodes[$i]]))
					{
						$ns_raw_full[$nodes[$i]] = array();
					}
					$ns_raw_full = &$ns_raw_full[$nodes[$i]];
				}
				$ns_raw_full = $val;
			}
		}
		else
		{
			if (isset($this->formData))
			{
				$this->formData[$key] = $val;
			}
			// Check if set - for case where you have a fileupload element & confirmation plugin - when plugin is trying to update none existant data
			if (isset($this->_fullFormData))
			{
				$this->_fullFormData[$key] = $val;
			}
			/*
			 * Need to allow RO (encrypted) elements to be updated.  Consensus is that
			 * we should actually modify the actual encrypted element in the $_REQUEST,
			 * but turns out this is a major pain in the butt (see _cryptViewOnlyElements() in the
			 * form view for details!).  Main problem is we need to know if it's a join and/or repeat group,
			 * which means loading up the element model.  So for now, just going to add the element name to a
			 * class array, $this->pluginUpdatedElements[], which we'll check in addDefaultDataFromRO()
			 * in the table model, or wherever else we need it.
			 */
			/*
			 if (array_key_exists('fabrik_vars', $_REQUEST)
			&& array_key_exists('querystring', $_REQUEST['fabrik_vars'])
			&& array_key_exists($key, $_REQUEST['fabrik_vars']['querystring'])) {
			$crypt = FabrikWorker::getCrypt();
			// turns out it isn't this simple, of course!  see above
			$_REQUEST['fabrik_vars']['querystring'][$key] = $crypt->encrypt($val);
			}
			 */
			// add element name to this array, which will then cause this element to be skipped
			// during the RO data phase of writing the row.  Don't think it really matter what we set it to,
			// might as well be the value.  Note that we need the new $override_ro arg, as some elements
			// use updateFormData() as part of normal operation, which should default to NOT overriding RO.
			if ($override_ro)
			{
				$this->pluginUpdatedElements[$key] = $val;
			}
			if ($update_raw)
			{
				$key .= '_raw';
				$this->formData[$key] = $val;
				if (isset($this->_fullFormData))
				{
					$this->_fullFormData[$key] = $val;
				}
				if ($override_ro)
				{
					$this->pluginUpdatedElements[$key] = $val;
				}
			}
		}
	}

	/**
	 * Intended for use by things like PHP form plugin code, PHP validations, etc.,
	 * so folk don't have to access formData directly.
	 *
	 * @param   string  $fullName     full element name
	 * @param   bool    $raw          get raw data
	 * @param   mixed   $default      value
	 * @param   string  $repeatCount  repeat count if needed
	 *
	 * @since	3.0.6
	 *
	 * @return mixed
	 */
	public function getElementData($fullName, $raw = false, $default = '', $repeatCount = null)
	{
		$data = isset($this->formData) ? $this->formData : $this->data;
		$value = null;
		if ($raw)
		{
			$fullName .= '_raw';
		}
		// Simplest case, element name exists in main group
		if (is_array($data) && array_key_exists($fullName, $data))
		{
			$value = $data[$fullName];
		}
		/* Maybe we are being called from onAfterProcess hook, or somewhere else
		 * running after store, when non-joined data names have been reduced to short
		 * names in formData, so peek in _fullFormData
		 */
		elseif (isset($this->_fullFormData) && array_key_exists($fullName, $this->_fullFormData))
		{
			$value = $this->_fullFormData[$fullName];
		}

		// If we didn't find it, set to default
		if (!isset($value))
		{
			$value = $default;
		}
		return $value;
	}

	/**
	 * This will strip the html from the form data according to the
	 * filter settings applied from article manager->parameters
	 * see here - http://forum.joomla.org/index.php/topic,259690.msg1182219.html#msg1182219
	 *
	 * @return  array  form data
	 */

	public function &setFormData()
	{
		if (isset($this->formData))
		{
			return $this->formData;
		}
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		list($this->dofilter, $this->filter) = FabrikWorker::getContentFilter();

		$this->ajaxPost = $app->input->getBool('fabrik_ajax');

		// Was POST but needs to be request for qs inserts to work
		$aData = $_REQUEST;
		array_walk_recursive($aData, array($this, '_clean'));

		// Set here so element can call formModel::updateFormData()
		$this->formData = $aData;
		$this->_fullFormData = $this->formData;
		$session = JFactory::getSession();
		$session->set('com_' . $package . '.form.data', $this->formData);
		return $this->formData;
	}

	/**
	 * Called from setFormData to clean up posted data from either ajax or posted form
	 * used in array_walk_recursive() method
	 *
	 * @param   mixed  &$item  (string or array)
	 *
	 * @return  void
	 */

	protected function _clean(&$item)
	{
		if (is_array($item))
		{
			array_walk_recursive($item, array($this, '_clean'));
		}
		else
		{
			if ($this->dofilter)
			{
				$item = preg_replace('/%([0-9A-F]{2})/mei', "chr(hexdec('\\1'))", $item);
				if ($this->ajaxPost)
				{
					$item = rawurldecode($item);
				}
				if ($this->dofilter)
				{
					@$item = $this->filter->clean($item);
				}
			}
			else
			{
				if ($this->ajaxPost)
				{
					$item = rawurldecode($item);
				}
			}
		}
	}

	/**
	 * Loop over elements and call their preProcess() method
	 *
	 * @return  void
	 */

	private function callElementPreprocess()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$repeatTotals = $input->get('fabrik_repeat_group', array(0), 'post', 'array');
		$groups = $this->getGroupsHiarachy();

		// Curerntly this is just used by calculation elements
		foreach ($groups as $groupModel)
		{
			$group = $groupModel->getGroup();
			$repeatedGroupCount = JArrayHelper::getValue($repeatTotals, $group->id, 0, 'int');
			$elementModels = $groupModel->getPublishedElements();
			for ($c = 0; $c < $repeatedGroupCount; $c++)
			{
				foreach ($elementModels as $elementModel)
				{
					$elementModel->preProcess($c);
				}
			}
		}
	}

	/**
	 * Without this the first groups repeat data was always being saved (as it was posted but hidden
	 * on the form.
	 *
	 * @param   array  &$data  posted form data
	 *
	 * @return  void
	 */

	protected function removeEmptyNoneJoinedGroupData(&$data)
	{
		$repeats = JArrayHelper::getValue($data, 'fabrik_repeat_group', array());
		$groups = $this->getGroups();
		foreach ($repeats as $groupid => $c)
		{
			if ($c == 0)
			{
				$group = $groups[$groupid];
				if ($group->isJoin())
				{
					continue;
				}
				$elements = $group->getPublishedElements();
				foreach ($elements as $elementModel)
				{
					$name = $elementModel->getElement()->name;
					$data[$name] = '';
					$data[$name . '_raw'] = '';
				}
			}
		}
	}

	/**
	 * Prepare the submitted form data for copying
	 *
	 * @return  string  Original records reference
	 */

	protected function prepareForCopy()
	{
		$listModel = $this->getListModel();
		$item = $listModel->getTable();
		$k = $item->db_primary_key;
		$k = FabrikString::safeColNameToArrayKey($k);
		$origid = $this->formData[$k];

		// COPY function should create new records
		if (array_key_exists('Copy', $this->formData))
		{
			$this->rowId = '';
			$this->formData[$k] = '';
			$this->formData['rowid'] = '';
		}
		return $origid;
	}

	/**
	 * As part of the form process we may need to update the referring url if making a copy
	 *
	 * @param   string  $origid    Original record ref
	 * @param   string  $insertId  New insert reference
	 *
	 * @return  void
	 */
	protected function updateRefferrer($origid, $insertId)
	{
		$input = JFactory::getApplication()->input;

		// Set the redirect page to the form's url if making a copy and set the id to the new insertid
		if (array_key_exists('Copy', $this->formData))
		{
			$u = str_replace('rowid=' . $origid, 'rowid=' . $insertId, $input->get('HTTP_REFERER', '', 'string'));
			$input->set('fabrik_referrer', $u);
		}
	}

	/**
	 * Set various request / input arrays with the main records insert id
	 *
	 * @param   string  $insertId  The records insert id
	 *
	 * @return  void
	 */

	protected function setInsertId($insertId)
	{
		$input = JFactory::getApplication()->input;
		$listModel = $this->getListModel();
		$item = $listModel->getTable();
		$tmpKey = str_replace("`", "", $item->db_primary_key);
		$tmpKey = str_replace('.', '___', $tmpKey);
		$this->formData[$tmpKey] = $insertId;
		$this->formData[$tmpKey . '_raw'] = $insertId;
		$this->formData[FabrikString::shortColName($item->db_primary_key)] = $insertId;
		$this->formData[FabrikString::shortColName($item->db_primary_key) . '_raw'] = $insertId;

		$this->fullFormData[$tmpKey] = $insertId;
		$this->fullFormData[$tmpKey . '_raw'] = $insertId;
		$this->fullFormData['rowid'] = $insertId;
		$this->formData['rowid'] = $insertId;
		$this->formDataWithTableName[$tmpKey] = $insertId;
		$this->formDataWithTableName[$tmpKey . '_raw'] = $insertId;
		$this->formDataWithTableName['rowid'] = $insertId;

		$input->set($tmpKey, $insertId);
		$input->set('rowid', $insertId);

		// $$$ hugh - pretty sure we need to unset 'usekey' now, as it is not relavent to joined data,
		// and it messing with storeRow of joins
		$input->set('usekey', '');
	}

	/**
	 * Process groups when the form is submitted
	 *
	 * @return  void
	 */

	protected function processGroups()
	{
		$groupModels = $this->getGroups();
		foreach ($groupModels as $groupModel)
		{
			$groupModel->process();
		}
	}

	/**
	 * Process individual elements when subitting the form
	 * Used for multi-select join elements which need to store data in
	 * related tables
	 *
	 * @since   3.1rc2
	 *
	 * @return  void
	 */

	protected function processElements()
	{
		$groups = $this->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel)
			{
				$elementModel->onFinalStoreRow($this->formData);
			}
		}
	}

	/**
	 * Process the form to the database
	 *
	 * @return void
	 */

	public function processToDB()
	{
		$pluginManager = FabrikWorker::getPluginManager();
		$app = JFactory::getApplication();
		$input = $app->input;
		$listModel = $this->getListModel();
		$item = $listModel->getTable();

		$origid = $this->prepareForCopy();

		$this->formData = $listModel->removeTableNameFromSaveData($this->formData, '___');
		$insertId = $this->storeMainRow ? $this->submitToDatabase($this->rowId) : $this->rowId;

		$this->updateRefferrer($origid, $insertId);
		$this->setInsertId($insertId);

		// Store join data
		$this->processGroups();

		// Enable db join checkboxes in repeat groups to save data
		$this->processElements();

		if (in_array(false, $pluginManager->runPlugins('onBeforeCalculations', $this)))
		{
			return;
		}
		$this->listModel->doCalculations();
	}

	/**
	 * Saves the form data to the database
	 *
	 * @param   int  $rowId  if 0 then insert a new row - otherwise update this row id
	 *
	 * @return	mixed	insert id (or rowid if updating existing row) if ok , else string error message
	 */

	protected function submitToDatabase($rowId = '0')
	{
		$app = JFactory::getApplication();
		$this->getGroupsHiarachy();
		$pluginManager = FabrikWorker::getPluginManager();
		$groups = $this->getGroupsHiarachy();
		$listModel = $this->getListModel();
		$listModel->encrypt = array();
		$data = array();
		foreach ($groups as $groupModel)
		{
			// Joined groups stored in groupModel::process();
			if ($groupModel->isJoin())
			{
				continue;
			}
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel)
			{
				if ($elementModel->encryptMe())
				{
					$listModel->encrypt[] = $elementModel->getElement()->name;
				}
				// Following line added to fix importcsv where data from first row is used for every row.
				$elementModel->defaults = null;
				$elementModel->onStoreRow($data);
			}
		}
		$listModel = $this->getListModel();
		$listModel->setFormModel($this);
		$item = $listModel->getTable();
		$listModel->storeRow($data, $rowId);

		$usekey = $app->input->get('usekey', '');
		if (!empty($usekey))
		{
			return $listModel->lastInsertId;
		}
		else
		{
			return ($rowId == 0) ? $listModel->lastInsertId : $rowId;
		}
	}

	/**
	 * Get list model
	 *
	 * @depreciated as of fabrik 3.0 - use getListModel instead
	 *
	 * @return  object  list model
	 */

	public function getTableModel()
	{
		return $this->getListModel();
	}

	/**
	 * Get the form's list model
	 * (was getTable but that clashed with J1.5 func)
	 *
	 * @return  FabrikFEModelList  fabrik list model
	 */

	public function getListModel()
	{
		if (!isset($this->listModel))
		{
			$this->listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
			$item = $this->getForm();
			$this->listModel->loadFromFormId($item->id);
			$this->listModel->setFormModel($this);
		}
		return $this->listModel;
	}

	/**
	 * Get the class names for each of the validation rules
	 *
	 * @deprecated (was only used in element label)
	 *
	 * @return	array	(validaionruleid => classname )
	 */

	public function loadValidationRuleClasses()
	{
		if (is_null($this->validationRuleClasses))
		{
			$validationRules = FabrikWorker::getPluginManager()->getPlugInGroup('validationrule');
			$classes = array();
			foreach ($validationRules as $rule)
			{
				$classes[$rule->name] = $rule->name;
			}
			$this->validationRuleClasses = $classes;
		}
		return $this->validationRuleClasses;
	}

	/**
	 * Add in any encrypted stuff, in case we fail validation ...
	 * otherwise it won't be in $data when we rebuild the page.
	 * Need to do it here, so _raw fields get added in the next chunk 'o' code.
	 *
	 * @param   array  &$post  posted form data passed by reference
	 *
	 * @return	null
	 */

	public function addEncrytedVarsToArray(&$post)
	{
		if (array_key_exists('fabrik_vars', $_REQUEST) && array_key_exists('querystring', $_REQUEST['fabrik_vars']))
		{
			$groups = $this->getGroupsHiarachy();
			$gkeys = array_keys($groups);
			$crypt = FabrikWorker::getCrypt();
			$w = new FabrikWorker;
			foreach ($gkeys as $g)
			{
				$groupModel = $groups[$g];
				$elementModels = $groupModel->getPublishedElements();
				foreach ($elementModels as $elementModel)
				{
					$element = $elementModel->getElement();
					foreach ($_REQUEST['fabrik_vars']['querystring'] as $key => $encrypted)
					{
						if ($elementModel->getFullName(true, false) == $key)
						{
							/* 	$$$ rob - dont test for !canUse() as confirmation plugin dynamically sets this
							 * if ($elementModel->canView())
							 * $$$ hugh - testing adding non-viewable, non-editable elements to encrypted vars
							 */

							if (is_array($encrypted))
							{
								// Repeat groups
								$v = array();
								foreach ($encrypted as $e)
								{
									// $$$ rob urldecode when posting from ajax form
									$e = urldecode($e);
									$e = empty($e) ? '' : $crypt->decrypt($e);
									$e = FabrikWorker::JSONtoData($e);
									$v[] = $w->parseMessageForPlaceHolder($e, $post);
								}
							}
							else
							{
								// $$$ rob urldecode when posting from ajax form
								$encrypted = urldecode($encrypted);
								$v = empty($encrypted) ? '' : $crypt->decrypt($encrypted);
								/* $$$ hugh - things like elementlist elements (radios, etc) seem to use
								 * their JSON data for encrypted read only vals, need to decode.
								 */
								$v = FabrikWorker::JSONtoData($v, true);
								foreach ($v as &$tmpV)
								{
									$tmpV = $w->parseMessageForPlaceHolder($tmpV, $post);
								}
							}
							$elementModel->setGroupModel($groupModel);
							$elementModel->setValuesFromEncryt($post, $key, $v);
							/* $$ rob set both normal and rawvalues to encrypted - otherwise validate method doesn't
							 * pick up decrypted value
							 */
							$elementModel->setValuesFromEncryt($post, $key . '_raw', $v);
						}
					}
				}
			}
		}
	}

	/**
	 * When submitting data copy values to _raw equivalent
	 *
	 * @param   array  &$post  form data
	 *
	 * @return	null
	 */

	public function copyToRaw(&$post)
	{
		$groups = $this->getGroupsHiarachy();
		$app = JFactory::getApplication();
		$input = $app->input;
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel)
			{
				$elName2 = $elementModel->getFullName(true, false);
				if (!array_key_exists($elName2 . '_raw', $post))
				{
					// Post required getValue() later on
					$input->set($elName2 . '_raw', @$post[$elName2]);
					$post[$elName2 . '_raw'] = @$post[$elName2];
				}
			}
		}
	}

	/**
	 * Has the form failed a validation
	 *
	 * @return bool
	 */

	public function failedValidation()
	{
		return $this->hasErrors();
	}

	/**
	 * Validate the form
	 * modifies post data to include validation replace data
	 *
	 * @return  bool  true if form validated ok
	 */

	public function validate()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		if ((bool) $input->getBool('fabrik_ignorevalidation', false) === true)
		{
			// Put in when saving page of form
			return true;
		}
		require_once COM_FABRIK_FRONTEND . '/helpers/uploader.php';
		$pluginManager = JModelLegacy::getInstance('Pluginmanager', 'FabrikFEModel');
		$validationRules = $pluginManager->getPlugInGroup('validationrule');

		$post = $this->setFormData();

		// Contains any data modified by the validations
		$this->modifiedValidationData = array();
		$w = new FabrikWorker;

		$ok = true;

		// $$$ rob 01/07/2011 fileupload needs to examine records previous data for validations on edting records
		$this->setOrigData();

		// $$$ rob copy before addEncrytedVarsToArray as well as after
		// so that any placedholders(.._raw) contained in the encrypted vars are correctly replaced
		$this->copyToRaw($post);

		/* $$$ rob for PHP 5.2.1 (and potential up to before 5.2.6) $post is not fully associated with formData -
		 * so the above copToRaw does not update $this->formData.
		 * $$$ hugh - had to add the &, otherwise replace validations weren't work, as modifying
		 * $post wasn't modifying $this->formData.  Which is weird, as I thought all array assignments
		 * were by reference?
		 * $$$ hugh - FIXME - wait ... what ... hang on ... we assign $this->formData in $this->setFormData(),
		 * which we assigned to $post a few ines up there ^^.  Why are we now assigning $post back to $this->formData??
		 */
		$this->formData = &$post;

		/* $$$ hugh - add in any encrypted stuff, in case we fail validation ...
		 * otherwise it won't be in $data when we rebuild the page.
		 * Need to do it here, so _raw fields get added in the next chunk 'o' code.
		 */
		$this->addEncrytedVarsToArray($post);

		// $$$ hugh - moved this to after addEncryptedVarsToArray(), so read only data is
		// available to things like calc's running in preProcess phase.
		$this->callElementPreprocess();

		// Add in raw fields - the data is already in raw format so just copy the values
		$this->copyToRaw($post);

		$groups = $this->getGroupsHiarachy();
		$repeatTotals = $input->get('fabrik_repeat_group', array(0), 'array');
		$ajaxPost = $input->getBool('fabrik_ajax');

		$joindata = array();

		foreach ($groups as $groupModel)
		{
			$groupCounter = $groupModel->getGroup()->id;
			$elementModels = $groupModel->getPublishedElements();
			$elDbVals = array();
			if ($groupModel->isJoin())
			{
				$joinModel = $groupModel->getJoinModel();
			}

			foreach ($elementModels as $elementModel)
			{
				// If the user can't view or edit the element, then don't validate it. Otherwise user sees failed validation but no indication of what failed
				if (!$elementModel->canUse() && !$elementModel->canView())
				{
					continue;
				}
				$elDbVals = array();
				$element = $elementModel->getElement();

				$validation_rules = $elementModel->validator->findAll();

				// $$ rob incorrect for ajax validation on joined elements
				// $elName = $elementModel->getFullName(true, false);
				$elName = $input->getBool('fabrik_ajax') ? $elementModel->getHTMLId(0) : $elementModel->getFullName(true, false);
				$this->errors[$elName] = array();
				$elName2 = $elementModel->getFullName(true, false);

				// $$$rob fix notice on validation of multipage forms
				if (!array_key_exists($groupCounter, $repeatTotals))
				{
					$repeatTotals[$groupCounter] = 1;
				}
				for ($c = 0; $c < $repeatTotals[$groupCounter]; $c++)
				{
					$this->errors[$elName][$c] = array();

					// $$$ rob $this->formData was $_POST, but failed to get anything for calculation elements in php 5.2.1
					$form_data = $elementModel->getValue($this->formData, $c, array('runplugins' => 0, 'use_default' => false));
					if (get_magic_quotes_gpc())
					{
						if (is_array($form_data))
						{
							foreach ($form_data as &$d)
							{
								if (is_string($d))
								{
									$d = stripslashes($d);
									if ($ajaxPost)
									{
										$d = rawurldecode($d);
									}
								}
							}
						}
						else
						{
							$form_data = stripslashes($form_data);
							if ($ajaxPost)
							{
								$form_data = rawurldecode($form_data);
							}
						}
					}

					// Internal element plugin validations
					if (!$elementModel->validate(@$form_data, $c))
					{
						$ok = false;
						$this->errors[$elName][$c][] = $elementModel->getValidationErr();
					}

					/**
					 * $$$ rob 11/04/2012 was stopping multiselect/chx dbjoin elements from saving in normal group.
					 * if ($groupModel->canRepeat() || $elementModel->isJoin())
					 */
					if ($groupModel->canRepeat())
					{
						// $$$ rob for repeat groups no join setting to array() menat that $_POST only contained the last repeat group data
						// $elDbVals = array();
						$elDbVals[$c] = $form_data;
					}
					else
					{
						$elDbVals = $form_data;
					}
					// Validations plugins attached to elemenets
					$pluginc = 0;
					if (!$elementModel->mustValidate())
					{
						continue;
					}
					foreach ($validation_rules as $plugin)
					{
						$plugin->formModel = $this;

						if ($plugin->shouldValidate($form_data, $pluginc))
						{
							if (!$plugin->validate($form_data, $elementModel, $pluginc, $c))
							{
								$this->errors[$elName][$c][] = $w->parseMessageForPlaceHolder($plugin->getMessage($pluginc));
								$ok = false;
							}
							if (method_exists($plugin, 'replace'))
							{
								if ($groupModel->canRepeat())
								{
									$elDbVals[$c] = $form_data;
									$testreplace = $plugin->replace($elDbVals[$c], $elementModel, $pluginc, $c);
									if ($testreplace != $elDbVals[$c])
									{
										$elDbVals[$c] = $testreplace;
										$this->modifiedValidationData[$elName][$c] = $testreplace;
										$joindata[$elName2 . '_raw'][$c] = $testreplace;
										$post[$elName . '_raw'][$c] = $testreplace;
									}
								}
								else
								{
									$testreplace = $plugin->replace($elDbVals, $elementModel, $pluginc, $c);
									if ($testreplace != $elDbVals)
									{
										$elDbVals = $testreplace;
										$this->modifiedValidationData[$elName] = $testreplace;
										$input->set($elName . '_raw', $elDbVals);
										$post[$elName . '_raw'] = $elDbVals;
									}
								}
							}
						}
						$pluginc++;
					}
				}
				if ($groupModel->isJoin() || $elementModel->isJoin())
				{
					$joindata[$elName2] = $elDbVals;
				}
				else
				{
					$input->set($elName, $elDbVals);
					$post[$elName] = $elDbVals;
				}
				// Unset the deafults or the orig submitted form data will be used (see date plugin mysql vs form format)
				$elementModel->defaults = null;
			}
		}
		// Insert join data into request array
		foreach ($joindata as $key => $val)
		{
			$input->set($key, $val);
			$post[$key] = $val;
		}
		if (!empty($this->errors))
		{
			FabrikWorker::getPluginManager()->runPlugins('onError', $this);
		}
		FabrikHelperHTML::debug($this->errors, 'form:errors');
		$this->setErrors($this->errors);

		return $ok;
	}

	/**
	 * Get form validation errors - if empty test session for errors
	 * 31/01/13 - no longer restoring from session errors - see http://fabrikar.com/forums/showthread.php?t=31377
	 * 19/02/13 - Changed from http refferer test to this->isMambot to restore session errors when redirecting from a non-ajax form
	 * in module that has failed validaiton - see http://fabrikar.com/forums/showthread.php?t=31870
	 *
	 * @return  array  errors
	 */

	public function getErrors()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$context = 'com_' . $package . '.form.' . $this->getId() . '.' . (int) $this->getRowId() . '.';
		$session = JFactory::getSession();

		// Store errors in local array as clearErrors() removes $this->errors
		$errors = array();
		if (empty($this->errors))
		{
			if ($this->isMambot)
			{
				$errors = $session->get($context . 'errors', array());
			}
		}
		else
		{
			$errors = $this->errors;
		}
		$this->clearErrors();
		$this->errors = $errors;
		return $this->errors;
	}

	/**
	 * Clear form validation errors
	 *
	 * @return  void
	 */

	public function clearErrors()
	{
		$session = JFactory::getSession();
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$this->errors = array();
		$context = 'com_' . $package . '.form.' . $this->getId() . '.' . (int) $this->getRowId() . '.';
		$session->clear($context . 'errors');
		/* $$$ rob this was commented out, but putting back in to test issue that if we have ajax validations on
		 * and a field is validated, then we dont submit the form, and go back to add the form, the previously validated
		 * values are shown in the form.
		 */
		$session->set($context . 'session.on', false);
	}

	/**
	 * Set form validation errors in session
	 *
	 * @param   array  $errors  error messages
	 *
	 * @return void
	 */

	public function setErrors($errors)
	{
		$session = JFactory::getSession();
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$context = 'com_' . $package . '.form.' . $this->getId() . '.' . (int) $this->getRowId() . '.';
		$session->set($context . 'errors', $errors);
		$session->set($context . 'session.on', true);
	}

	/**
	 * Get a JSON encoded string of error and modified data messages
	 *
	 * @return string
	 */

	public function getJsonErrors()
	{
		$data = array('modified' => $this->modifiedValidationData, 'errors' => $this->errors);
		return json_encode($data);
	}

	/**
	 * Should the form do a spoof check
	 *
	 * @return	bool
	 */

	public function spoofCheck()
	{
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		return $this->getParams()->get('spoof_check', $fbConfig->get('spoofcheck_on_formsubmission', true));
	}

	/**
	 * Get an instance of the uploader object
	 *
	 * @return  object  uploader
	 */

	public function &getUploader()
	{
		if (is_null($this->uploader))
		{
			$this->uploader = new FabrikUploader($this);
		}
		return $this->uploader;
	}

	/**
	 * Get the forms table name
	 *
	 * @deprecated - not used?
	 *
	 * @return  string  table name
	 */

	public function getTableName()
	{
		$this->getListModel();
		return $this->getListModel()->getTable()->db_table_name;
	}

	/**
	 * Get the form row
	 *
	 * @param   string  $name     table name
	 * @param   string  $prefix   table name prefx
	 * @param   array   $options  initial state options
	 *
	 * @return object form row
	 */

	public function getTable($name = '', $prefix = 'Table', $options = array())
	{
		if (is_null($this->form))
		{
			$this->form = parent::getTable('Form', 'FabrikTable');
		}
		$id = $this->getId();
		if ($this->form->id != $id)
		{
			$this->form->load($id);
		}
		return $this->form;
	}

	/**
	 * Sets the variable of each of the form's group's elements to the value
	 * specified
	 *
	 * @param   string  $varName  variable name
	 * @param   string  $varVal   variable value
	 *
	 * @deprecated  not used
	 *
	 * @return  bool  false if update error occurs
	 */

	public function setElementVars($varName, $varVal)
	{
		if ($this->elements == null)
		{
			$this->getFormGroups();
		}
		foreach ($this->elements as $el)
		{
			$element = FabTable::getInstance('Element', 'FabrikTable');
			$element->load($el->id);
			if (!$element->set($varName, $varVal))
			{
				return false;
			}
			$element->store();
		}
		return true;
	}

	/**
	 * Determines if the form can be published
	 *
	 * @return  bool  true if publish dates are ok
	 */

	public function canPublish()
	{
		$db = FabrikWorker::getDbo();
		$form = $this->getForm();
		$nullDate = $db->getNullDate();
		$publishup = JFactory::getDate($form->publish_up)->toUnix();
		$publishdown = JFactory::getDate($form->publish_down)->toUnix();
		$now = JFactory::getDate()->toUnix();
		if ($form->published == '1')
		{
			if ($now >= $publishup || $form->publish_up == '' || $form->publish_up == $nullDate)
			{
				if ($now <= $publishdown || $form->publish_down == '' || $form->publish_down == $nullDate)
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Create a drop down list of all the elements in the form
	 *
	 * @param   string  $name                Drop down name
	 * @param   string  $default             Current value
	 * @param   bool    $excludeUnpublished  Add elements that are unpublished
	 * @param   bool    $useStep             Concat table name and el name with '___' (true) or "." (false)
	 * @param   bool    $incRaw              Include raw labels default = true
	 * @param   string  $key                 What value should be used for the option value 'name' (default) or 'id' @since 3.0.7
	 * @param   string  $attribs             Select list attributs @since 3.1b
	 *
	 * @return	string	html list
	 */

	public function getElementList($name = 'order_by', $default = '', $excludeUnpublished = false, $useStep = false, $incRaw = true, $key = 'name', $attribs = 'class="inputbox" size="1"')
	{
		$aEls = array();
		$aEls = $this->getElementOptions($useStep, $key, false, $incRaw);
		$aEls[] = JHTML::_('select.option', '', '-');
		asort($aEls);
		return JHTML::_('select.genericlist', $aEls, $name, $attribs, 'value', 'text', $default);
	}

	/**
	 * Get an array of the form's element's ids
	 *
	 * @param   array  $ignore  ClassNames to ignore e.g. array('FabrikModelFabrikCascadingdropdown')
	 * @param   array  $opts    Property 'includePublised' can be set to 0; @since 3.0.7
	 *                          Property 'loadPrefilters' @since 3.0.7.1 - used to ensure that prefilter elements are loaded in inline edit
	 *
	 * @return  array  ints ids
	 */

	public function getElementIds($ignore = array(), $opts = array())
	{
		$aEls = array();
		$groups = $this->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel)
			{
				$this->getElementIds_check($elementModel, $ignore, $opts, $aEls);
			}
		}
		if (JArrayHelper::getValue($opts, 'loadPrefilters', false))
		{
			$listModel = $this->getListModel();
			list($afilterFields, $afilterConditions, $afilterValues, $afilterAccess, $afilterEval, $afilterJoins) = $listModel->prefilterSetting();
			foreach ($afilterFields as $name)
			{
				$raw = preg_match("/_raw$/", $name) > 0;
				$name = $name ? FabrikString::rtrimword($name, '_raw') : $name;
				$elementModel = $this->getElement($name);
			}
		}
		return $aEls;
	}

	/**
	 * Helper function for getElementIds(), test if the element should be added
	 *
	 * @param   plgFabrik_Element  $elementModel  Element model
	 * @param   array              $ignore        ClassNames to ignore e.g. array('FabrikModelFabrikCascadingdropdown')
	 * @param   array              $opts          Filter options
	 * @param   array              &$aEls         Array of element ids to load
	 *
	 * @return  void
	 */
	private function getElementIds_check($elementModel, $ignore, $opts, &$aEls)
	{
		$class = get_class($elementModel);
		if (!in_array($class, $ignore))
		{
			$element = $elementModel->getElement();
			if (JArrayHelper::getValue($opts, 'includePublised', true) && $element->published == 0)
			{
				continue;
			}
			$aEls[] = (int) $element->id;
		}
	}

	/**
	 * Creates options array to be then used by getElementList to create a drop down of elements in the form
	 * sperated as elements need to collate this options from muliple forms
	 *
	 * @param   bool    $useStep               concat table name and el name with '___' (true) or "." (false)
	 * @param   string  $key                   name of key to use (default "name")
	 * @param   bool    $show_in_list_summary  only show those elements shown in table summary
	 * @param   bool    $incRaw                include raw labels in list (default = false) Only works if $key = name
	 * @param   array   $filter                list of plugin names that should be included in the list - if empty include all plugin types
	 * @param   string  $labelMethod           An element method that if set can alter the option's label - used to only show elements that can be selected for search all
	 * @param   bool    $noJoins               do not include elements in joined tables (default false)
	 *
	 * @return	array	html options
	 */

	public function getElementOptions($useStep = false, $key = 'name', $show_in_list_summary = false, $incRaw = false, $filter = array(), $labelMethod = '', $noJoins = false)
	{
		$groups = $this->getGroupsHiarachy();
		$aEls = array();
		$step = $useStep ? '___' : '.';
		$gkeys = array_keys($groups);
		foreach ($gkeys as $gid)
		{
			$groupModel = $groups[$gid];
			if ($noJoins && $groupModel->isJoin())
			{
				continue;
			}
			$elementModels = $groupModel->getMyElements();
			$prefix = $groupModel->isJoin() ? $groupModel->getJoinModel()->getJoin()->table_join . '.' : '';
			foreach ($elementModels as $elementModel)
			{
				$el = $elementModel->getElement();
				if (!empty($filter) && !in_array($el->plugin, $filter))
				{
					continue;
				}
				if ($show_in_list_summary == true && $el->show_in_list_summary != 1)
				{
					continue;
				}
				$val = $el->$key;
				$label = strip_tags($prefix . $el->label);
				if ($labelMethod !== '')
				{
					$elementModel->$labelMethod($label);
				}
				if ($key != 'id')
				{
					$val = $elementModel->getFullName($useStep, false);
					if ($this->addDbQuote)
					{
						$val = FabrikString::safeColName($val);
					}
					if ($incRaw && is_a($elementModel, 'PlgFabrik_ElementDatabasejoin'))
					{
						/* @FIXME - next line had been commented out, causing undefined warning for $rawval
						 * on following line.  Not sure if getrawColumn is right thing to use here tho,
						 * like, it adds filed quotes, not sure if we need them.
						 */
						if ($elementModel->getElement()->published != 0)
						{
							$rawval = $elementModel->getRawColumn($useStep);
							if (!$this->addDbQuote)
							{
								$rawval = str_replace('`', '', $rawval);
							}
							$aEls[$label . '(raw)'] = JHTML::_('select.option', $rawval, $label . '(raw)');
						}
					}
				}
				$aEls[] = JHTML::_('select.option', $val, $label);
			}
		}
		asort($aEls);
		return $aEls;
	}

	/**
	 * Called via ajax nav
	 *
	 * @param   int  $dir  1 - move foward, 0 move back
	 *
	 * @return  bool  new row id loaded.
	 */

	public function paginateRowId($dir)
	{
		$db = FabrikWorker::getDbo();
		$app = JFactory::getApplication();
		$input = $app->input;
		$c = $dir == 1 ? '>=' : '<=';
		$limit = $dir == 1 ? 'LIMIT 2' : '';
		$intLimit = $dir == 1 ? 2 : 0;
		$listModel = $this->getListModel();
		$order = $listModel->buildQueryOrder();
		$item = $listModel->getTable();
		$rowid = $input->getString('rowid', '', 'string');
		$query = $db->getQuery(true);
		$query->select($item->db_primary_key . ' AS ' . FabrikString::safeColNameToArrayKey($item->db_primary_key))->from($item->db_table_name)
			->where($item->db_primary_key . ' ' . $c . ' ' . $rowid);
		$query = $listModel->buildQueryOrder($query);
		$db->setQuery($query, 0, $intLimit);
		$ids = $db->loadColumn();
		if ($dir == 1)
		{
			if (count($ids) >= 2)
			{
				$input->set('rowid', $ids[$dir]);
				return true;
			}
			else
			{
				return false;
			}
		}
		if (count($ids) - 2 >= 0)
		{
			$input->set('rowid', $ids[count($ids) - 2]);
			return true;
		}
		return false;
	}

	/**
	 * Are we creating a new record or editing an existing one?
	 * Put here to ensure compat when we go from 3.0 where rowid = 0 = new, to row id '' = new
	 *
	 * @since   3.0.9
	 *
	 * @return  boolean
	 */

	public function isNewRecord()
	{
		return $this->getRowId() == 0;
	}

	/**
	 * Get the current records row id
	 * setting a rowid of -1 will load in the current users record (used in
	 * conjunction wth usekey variable
	 *
	 * setting a rowid of -2 will load in the last created record
	 *
	 * @return  string  rowid
	 */

	public function getRowId()
	{
		if (isset($this->rowId))
		{
			return $this->rowId;
		}
		$app = JFactory::getApplication();
		$input = $app->input;
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$user = JFactory::getUser();

		$package = $app->getUserState('com_fabrik.package', 'fabrik');

		// $$$rob if we show a form module when in a fabrik form component view - we shouldn't use
		// the request rowid for the mambot as that value is destined for the component
		if ($this->isMambot && $input->get('option') == 'com_' . $package)
		{
			$this->rowId = $usersConfig->get('rowid');
		}
		else
		{
			$this->rowId = FabrikWorker::getMenuOrRequestVar('rowid', $usersConfig->get('rowid'), $this->isMambot);
			if ($this->rowId == -2)
			{
				// If the default was set to -2 (load last row) then a pagination form plugin's row id should override menu settings
				$this->rowId = FabrikWorker::getMenuOrRequestVar('rowid', $usersConfig->get('rowid'), $this->isMambot, 'request');
			}
		}
		if ($this->getListModel()->getParams()->get('sef-slug', '') !== '')
		{
			$this->rowId = explode(':', $this->rowId);
			$this->rowId = array_shift($this->rowId);
		}
		// $$$ hugh - for some screwed up reason, when using SEF, rowid=-1 ends up as :1
		// $$$ rob === compare as otherwise 0 == ":1" which menat that the users record was  loaded
		if ((string) $this->rowId === ':1')
		{
			$this->rowId = '-1';
		}
		// Set rowid to -1 to load in the current users record
		switch ($this->rowId)
		{
			case '-1':
				$this->rowId = (int) $user->get('id');
				break;
			case '-2':
			// Set rowid to -2 to load in the last recorded record
				$this->rowId = $this->getMaxRowId();
				break;
		}
		FabrikWorker::getPluginManager()->runPlugins('onSetRowId', $this);
		return $this->rowId;
	}

	/**
	 * Collates data to write out the form
	 *
	 * @return  mixed  bool
	 */

	public function render()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$profiler = JProfiler::getInstance('Application');
		JDEBUG ? $profiler->mark('formmodel render: start') : null;

		// $$$rob required in paolo's site when rendering modules with ajax option turned on
		$this->listModel = null;
		$this->setRowId($this->getRowId());

		/*
		 * $$$ hugh - need to call this here as we set $this->editable here, which is needed by some plugins
		 * hmmmm, this means that getData() is being called from checkAccessFromListSettings(),
		 * so plugins running onBeforeLoad will have to unset($formModel->_data) if they want to
		 * do something funky like change the rowid being loaded.  Not a huge problem, but caught me out
		 * when a custom PHP onBeforeLoad plugin I'd written for a client suddenly broke.
		 */
		$this->checkAccessFromListSettings();
		$pluginManager = FabrikWorker::getPluginManager();
		$res = $pluginManager->runPlugins('onBeforeLoad', $this);
		if (in_array(false, $res))
		{
			return false;
		}
		JDEBUG ? $profiler->mark('formmodel render: getData start') : null;
		$data = $this->getData();
		JDEBUG ? $profiler->mark('formmodel render: getData end') : null;
		$res = $pluginManager->runPlugins('onLoad', $this);
		if (in_array(false, $res))
		{
			return false;
		}

		// @TODO - relook at this:
		// $this->_reduceDataForXRepeatedJoins();
		JDEBUG ? $profiler->mark('formmodel render end') : null;

		$session = JFactory::getSession();
		$session->set('com_' . $package . '.form.' . $this->getId() . '.data', $this->data);

		// $$$ rob return res - if its false the the form will not load
		return $res;
	}

	/**
	 * Get the max row id - used when requesting rowid=-2 to return the last recorded detailed view
	 *
	 * @return  int  max row id
	 */

	protected function getMaxRowId()
	{
		if (!$this->getForm()->record_in_database)
		{
			return $this->rowId;
		}
		$listModel = $this->getListModel();
		$fabrikDb = $listModel->getDb();
		$item = $listModel->getTable();
		$k = $fabrikDb->quoteName($item->db_primary_key);

		// @TODO JQuery this
		$fabrikDb->setQuery("SELECT MAX($k) FROM " . FabrikString::safeColName($item->db_table_name) . $listModel->buildQueryWhere());
		return $fabrikDb->loadResult();
	}

	/**
	 * Does the form contain user errors
	 *
	 * @return  bool
	 */

	public function hasErrors()
	{
		$errorsFound = !empty($this->errors);
		$errorsFound = false;
		foreach ($this->errors as $field => $errors)
		{
			if (!empty($errors))
			{
				if (!empty($errors[0]))
				{
					$errorsFound = true;
				}
			}
		}
		if ($this->saveMultiPage(false))
		{
			$srow = $this->getSessionData();
			/*
			 * Test if its a resumed paged form
			 * if so _arErrors will be filled so check all elements had no errors
			 */
			$multiPageErrors = false;
			if ($srow->data != '')
			{
				foreach ($this->errors as $err)
				{
					if (!empty($err[0]))
					{
						$multiPageErrors = true;
					}
				}
				if (!$multiPageErrors)
				{
					$errorsFound = false;
				}
			}
		}
		return $errorsFound;
	}

	/**
	 * Main method to get the data to insert into the form
	 *
	 * @return  array  Form's data
	 */

	public function getData()
	{
		// If already set return it. If not was causing issues with the juser form plugin
		// when it tried to modify the form->data info, from within its onLoad method, when sync user option turned on.
		if (isset($this->data))
		{
			return $this->data;
		}
		$this->getRowId();
		$app = JFactory::getApplication();
		$input = $app->input;
		$profiler = JProfiler::getInstance('Application');
		JDEBUG ? $profiler->mark('formmodel getData: start') : null;
		$this->data = array();

		$f = JFilterInput::getInstance();

		/*
		 * $$$ hugh - we need to remove any elements from the query string,
		 * if the user doesn't have access, otherwise ACL's on elements can
		 * be bypassed by just setting value on form load query string!
		 */

		$clean_request = $f->clean($_REQUEST, 'array');
		foreach ($clean_request as $key => $value)
		{
			$test_key = FabrikString::rtrimword($key, '_raw');
			$elementModel = $this->getElement($test_key, false, false);
			if ($elementModel !== false)
			{
				if (!$elementModel->canUse())
				{
					unset($clean_request[$key]);
				}
			}
		}

		$data = array(FArrayHelper::toObject($clean_request));
		$form = $this->getForm();

		$aGroups = $this->getGroupsHiarachy();
		JDEBUG ? $profiler->mark('formmodel getData: groups loaded') : null;
		if (!$form->record_in_database)
		{
			FabrikHelperHTML::debug($data, 'form:getData from $_REQUEST');
			$data = $f->clean($_REQUEST, 'array');
		}
		else
		{
			JDEBUG ? $profiler->mark('formmodel getData: start get list model') : null;
			$listModel = $this->getListModel();
			JDEBUG ? $profiler->mark('formmodel getData: end get list model') : null;
			$fabrikDb = $listModel->getDb();
			JDEBUG ? $profiler->mark('formmodel getData: db created') : null;
			$item = $listModel->getTable();
			JDEBUG ? $profiler->mark('formmodel getData: table row loaded') : null;
			$this->aJoinObjs = $listModel->getJoins();
			JDEBUG ? $profiler->mark('formmodel getData: joins loaded') : null;
			if ($this->hasErrors())
			{
				// $$$ hugh - if we're a mambot, reload the form session state we saved in
				// process() when it banged out.
				if ($this->isMambot)
				{
					$srow = $this->getSessionData();
					$this->sessionModel->last_page = 0;
					if ($srow->data != '')
					{
						$data = FArrayHelper::toObject(unserialize($srow->data), 'stdClass', false);
						JFilterOutput::objectHTMLSafe($data);
						$data = array($data);
						FabrikHelperHTML::debug($data, 'form:getData from session (form in Mambot and errors)');
					}
				}
				else
				{
					// $$$ rob - use setFormData rather than $_GET
					// as it applies correct input filtering to data as defined in article manager parameters
					$data = $this->setFormData();
					$data = FArrayHelper::toObject($data, 'stdClass', false);

					// $$$rob ensure "<tags>text</tags>" that are entered into plain text areas are shown correctly
					JFilterOutput::objectHTMLSafe($data);
					$data = JArrayHelper::fromObject($data);
					FabrikHelperHTML::debug($data, 'form:getData from POST (form not in Mambot and errors)');
				}
			}
			else
			{
				$sessionLoaded = false;

				// Test if its a resumed paged form
				if ($this->saveMultiPage())
				{
					$srow = $this->getSessionData();
					JDEBUG ? $profiler->mark('formmodel getData: session data loaded') : null;
					if ($srow->data != '')
					{
						$sessionLoaded = true;
						/*
						 * $$$ hugh - this chunk should probably go in setFormData, but don't want to risk any side effects just now
						 * problem is that fater failed validation, non-repeat join element data is not formatted as arrays,
						 * but from this point on, code is expecting even non-repeat join data to be arrays.
						 */
						$tmp_data = unserialize($srow->data);
						$groups = $this->getGroupsHiarachy();
						foreach ($groups as $groupModel)
						{
							if ($groupModel->isJoin() && !$groupModel->canRepeat())
							{
								foreach ($tmp_data['join'][$groupModel->getJoinId()] as &$el)
								{
									$el = array($el);
								}
							}
						}
						$bits = JArrayHelper::fromObject($data[0]);
						$bits = array_merge($tmp_data, $bits);
						$data = array(FArrayHelper::toObject($bits));
						FabrikHelperHTML::debug($data, 'form:getData from session (form not in Mambot and no errors');
					}
				}
				if (!$sessionLoaded)
				{
					/* Only try and get the row data if its an active record
					 * use !== 0 as rowid may be alphanumeric
					 * $$$ hugh - when 'usekey', rowid can actually be 0 (like if using userid and this is guest access)
					 * so go ahead and try and load the row, if it doesn't exist, we'll supress the warning
					 */
					$usekey = FabrikWorker::getMenuOrRequestVar('usekey', '', $this->isMambot);
					if (!empty($usekey) || (int) $this->rowId !== 0 || (!is_numeric($this->rowId) && $this->rowId != ''))
					{
						// $$$ hugh - once we have a few join elements, our select statements are
						// getting big enough to hit default select length max in MySQL.
						$listModel->setBigSelects();

						// Otherwise lets get the table record
						$opts = $input->get('task') == 'form.inlineedit' ? array('ignoreOrder' => true) : array();
						$sql = $this->buildQuery($opts);

						$fabrikDb->setQuery($sql);
						FabrikHelperHTML::debug($fabrikDb->getQuery(), 'form:render');
						$rows = $fabrikDb->loadObjectList();
						if (is_null($rows))
						{
							JError::raiseWarning(500, $fabrikDb->getErrorMsg());
						}
						JDEBUG ? $profiler->mark('formmodel getData: rows data loaded') : null;

						// $$$ rob Ack above didnt work for joined data where there would be n rows rerutned frho "this rowid = $this->rowId  \n";
						if (!empty($rows))
						{
							// Only do this if the query returned some rows (it wont if usekey on and userid = 0 for example)
							$data = array();
							foreach ($rows as &$row)
							{
								if (empty($data))
								{
									// If loading in a rowid=-1 set the row id to the actual row id
									$this->rowId = isset($row->__pk_val) ? $row->__pk_val : $this->rowId;
								}
								$row = empty($row) ? array() : JArrayHelper::fromObject($row);
								$request = $clean_request;
								$request = array_merge($row, $request);
								$data[] = FArrayHelper::toObject($request);
							}
						}
						FabrikHelperHTML::debug($data, 'form:getData from querying rowid= ' . $this->rowId . ' (form not in Mambot and no errors)');

						// If empty data return and trying to edit a record then show error
						JDEBUG ? $profiler->mark('formmodel getData: empty test') : null;

						// Was empty($data) but that is never empty. Had issue where list prefilter meant record was not loaded, but no message shown in form
						if (empty($rows) && $this->rowId != '')
						{
							// $$$ hugh - special case when using -1, if user doesn't have a record yet
							if (FabrikWorker::getMenuOrRequestVar('rowid', '', $this->isMambot) == '-1')
							{
								return;
							}
							else
							{
								// If no key found set rowid to 0 so we can insert a new record.
								if (empty($usekey) && !$this->isMambot)
								{
									$this->rowId = 0;
									JError::raiseNotice(500, JText::sprintf('COULD NOT FIND RECORD IN DATABASE', $this->rowId));
									return;
								}
								else
								{
									// If we are using usekey then theres a good possiblity that the record
									// won't yet exists- so in this case suppress this error message
									$this->rowId = 0;
								}
							}
						}
					}
				}
				// No need to setJoinData if you are correcting a failed validation
				if (!empty($data))
				{
					$this->setJoinData($data);
				}
			}
			// Set the main part of the form's default data
			/* if ($this->rowId != '')
			{
			}
			else
			{
				// Could be a view
				if ($listModel->isView())
				{
					// @TODO test for new records from views
					$data = JArrayHelper::fromObject($data[0]);
				}
				else
				{
					if (($this->isMambot || $this->saveMultiPage()) && (!empty($data) && is_object($data[0])))
					{
						$data = JArrayHelper::fromObject($data[0]);
					}
					else
					{
						// $$$ rob was causing notices when adding record with joined groups as $data[0]->join unset if we just use request
						$data = JArrayHelper::fromObject($data[0]);
					}
				}
			}

*/
		}
//		$this->listModel = $listModel;

		// Test to allow {$my->id}'s to be evald from query strings
		$w = new FabrikWorker;
		$data = $w->parseMessageForPlaceHolder($data);
		$this->data = $data;
		FabrikHelperHTML::debug($data, 'form:data');
		JDEBUG ? $profiler->mark('queryselect: getData() end') : null;
		return $this->data;
	}

	/**
	 * Checks if user is logged in and form multipage settings to determine
	 * if the form saves to the session table on multipage navigation
	 *
	 * @param   bool  $useSessionOn  Return true if JSession contains session.on - used in confirmation
	 * plugin to re-show the previously entered form data. Not used in $this->hasErrors() otherwise logged in users
	 * can not get the confirmation plugin to work
	 *
	 * @return  bool
	 */

	public function saveMultiPage($useSessionOn = true)
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$params = $this->getParams();
		$session = JFactory::getSession();

		// Set in plugins such as confirmation plugin
		$pluginManager = FabrikWorker::getPluginManager();
		$pluginManager->runPlugins('usesSession', $this, 'form');
		if (in_array(true, $pluginManager->data))
		{
			if ($session->get('com_' . $package . '.form.' . $this->getId() . '.' . $this->getRowId() . '.session.on') == true && $useSessionOn)
			{
				return true;
			}
		}
		$save = (int) $params->get('multipage_save', 0);
		$user = JFactory::getUser();
		if ($user->get('id') !== 0)
		{
			return $save === 0 ? false : true;
		}
		else
		{
			return $save === 2 ? true : false;
		}
	}

	/**
	 * If editing a record which contains repeated join data then on start $data is an
	 * array with each records being a row in the database.
	 *
	 * We need to take this structure and convert it to the same format as when the form
	 * is submitted
	 *
	 * @param   array  &$data  form data
	 *
	 * @return  void
	 */

	public function setJoinData(&$data)
	{
		$this->_joinDefaultData = array();
		if (empty($data))
		{
			return;
		}

		// No joins so leave !
		if (!is_array($this->aJoinObjs) || $this->rowId === '')
		{
			return;
		}

		$groups = $this->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{

			$group = $groupModel->getGroup();

			$elementModels = $groupModel->getMyElements();
			foreach ($elementModels as $elementModel)
			{
				if ($groupModel->isJoin() || $elementModel->isJoin())
				{
					$names = $elementModel->getJoinDataNames();
					foreach ($data as $row)
					{
						for ($i = 0; $i < count($names); $i ++)
						{
							$name = $names[$i];
							if (array_key_exists($name, $row))
							{
								$v = $row->$name;
								$v = FabrikWorker::JSONtoData($v, $elementModel->isJoin());
								if (!is_array($data[0]->$name))
								{
									if ($groupModel->isJoin() && $groupModel->canRepeat())
									{
										$v = array($v);
									}
									$data[0]->$name = $v;
								}
								else
								{
									if ($groupModel->isJoin())
									{
										$n =& $data[0]->$name;
										$n[] = $v;
									}
								}
							}
						}
					}
				}
			}
		}

		// Remove the additional rows - they should have been merged into [0] above.
		$data = JArrayHelper::fromObject($data[0]);
	}

	/**
	 * Get the forms session data (used when using multipage forms)
	 *
	 * @return  object	session data
	 */

	protected function getSessionData()
	{
		$params = $this->getParams();
		$this->sessionModel = JModelLegacy::getInstance('Formsession', 'FabrikFEModel');
		$this->sessionModel->setFormId($this->getId());
		$this->sessionModel->setRowId($this->rowId);
		$useCookie = (int) $params->get('multipage_save', 0) === 2 ? true : false;
		if (!$useCookie)
		{
			// Incase a plugin is using cookie session (e.g. confirmation plugin)
			$useCookie = $this->sessionModel->canUseCookie();
		}
		$this->sessionModel->useCookie($useCookie);
		return $this->sessionModel->load();
	}

	/**
	 * Create the sql query to get the rows data for insertion into the form
	 *
	 * @param   array  $opts  key: ignoreOrder ingores order by part of query - needed for inline edit, as it only selects certain fields, order by on a db join element returns 0 results
	 *
	 * @deprecated	use buildQuery() instead
	 *
	 * @return  string	sql query to get row
	 */

	public function _buildQuery($opts = array())
	{
		return $this->buildQuery($opts = array());
	}

	/**
	 * Create the sql query to get the rows data for insertion into the form
	 *
	 * @param   array  $opts  key: ignoreOrder ingores order by part of query - needed for inline edit, as it only selects certain fields, order by on a db join element returns 0 results
	 *
	 * @return  string  query
	 */

	public function buildQuery($opts = array())
	{
		if (isset($this->query))
		{
			return $this->query;
		}
		$db = FabrikWorker::getDbo();
		$conf = JFactory::getConfig();
		$app = JFactory::getApplication();
		$input = $app->input;
		$form = $this->getForm();
		if (!$form->record_in_database)
		{
			return;
		}
		$listModel = $this->getListModel();
		$item = $listModel->getTable();
		$sql = $listModel->buildQuerySelect('form');
		$sql .= $listModel->buildQueryJoin();
		$emptyRowId = $this->rowId === '' ? true : false;
		$random = $input->get('random');
		$usekey = FabrikWorker::getMenuOrRequestVar('usekey', '', $this->isMambot, 'var');
		if ($usekey != '')
		{
			$usekey = explode('|', $usekey);
			foreach ($usekey as &$tmpk)
			{
				$tmpk = !strstr($tmpk, '.') ? $item->db_table_name . '.' . $tmpk : $tmpk;
				$tmpk = FabrikString::safeColName($tmpk);
			}
			if (!is_array($this->rowId))
			{
				$aRowIds = explode('|', $this->rowId);
			}
		}
		$comparison = $input->get('usekey_comparison', '=');
		$viewpk = $input->get('view_primary_key');

		// $$$ hugh - changed this to !==, as in rowid=-1/usekey situations, we can have a rowid of 0
		// I don't THINK this will have any untoward side effects, but ...
		if (!$random && !$emptyRowId)
		{
			$sql .= ' WHERE ';
			if (!empty($usekey))
			{
				$sql .= "(";
				$parts = array();
				for ($k = 0; $k < count($usekey); $k++)
				{
					// Ensure that the key value is not quoted as we Quote() afterwards
					if ($comparison == '=')
					{
						$parts[] = ' ' . $usekey[$k] . ' = ' . $db->quote($aRowIds[$k]);
					}
					else
					{
						$parts[] = ' ' . $usekey[$k] . ' LIKE ' . $db->quote('%' . $aRowIds[$k] . '%');
					}
				}
				$sql .= implode(' AND ', $parts);
				$sql .= ')';
			}
			else
			{
				$sql .= ' ' . $item->db_primary_key . ' = ' . $db->quote($this->rowId);
			}
		}
		else
		{
			if ($viewpk != '')
			{
				$sql .= ' WHERE ' . $viewpk . ' ';
			}
			elseif ($random)
			{
				// $$$ rob Should this not go after prefilters have been applied ?
				$sql .= ' ORDER BY RAND() LIMIT 1 ';
			}
		}
		// Get prefilter conditions from table and apply them to the record
		// the false, ignores any filters set by the table
		$where = $listModel->buildQueryWhere(false);
		if (strstr($sql, 'WHERE'))
		{
			// Do it this way as queries may contain subquerues which we want to keep the where
			$firstword = JString::substr($where, 0, 5);
			if ($firstword == 'WHERE')
			{
				$where = JString::substr_replace($where, 'AND', 0, 5);
			}
		}
		// Set rowId to -2 to indicate random record
		if ($random)
		{
			$this->setRowId(-2);
		}
		// $$$ rob ensure that all prefilters are wrapped in brackets so that
		// only one record is loaded by the query - might need to set $word = and?
		if (trim($where) != '')
		{
			$where = explode(' ', $where);
			$word = array_shift($where);
			$sql .= $word . ' (' . implode(' ', $where) . ')';
		}
		if (!$random && JArrayHelper::getValue($opts, 'ignoreOrder', false) === false)
		{
			// $$$ rob if showing joined repeat groups we want to be able to order them as defined in the table
			$sql .= $listModel->buildQueryOrder();
		}
		$this->query = $sql;
		return $sql;
	}

	/**
	 * Attempts to determine if the form contains the element
	 *
	 * @param   string  $searchName  element name to search for
	 * @param   bool    $checkInt    check search name against element id
	 * @param   bool    $checkShort  check short element name
	 *
	 * @return  bool  true if found, false if not found
	 */

	public function hasElement($searchName, $checkInt = false, $checkShort = true)
	{
		$groups = $this->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getMyElements();
			if (!is_array($groupModel->elements))
			{
				continue;
			}
			foreach ($groupModel->elements as $elementModel)
			{
				$element = $elementModel->getElement();
				if ($checkInt)
				{
					if ($searchName == $element->id)
					{
						$this->currentElement = $elementModel;
						return true;
					}
				}
				if ($searchName == $element->name && $checkShort)
				{
					$this->currentElement = $elementModel;
					return true;
				}
				if ($searchName == $elementModel->getFullName(true, false))
				{
					$this->currentElement = $elementModel;
					return true;
				}
				if ($searchName == $elementModel->getFullName(false, false))
				{
					$this->currentElement = $elementModel;
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Get an element
	 *
	 * @param   string  $searchName  name to search for
	 * @param   bool    $checkInt    check search name against element id
	 * @param   bool    $checkShort  check short element name
	 *
	 * @return  mixed  ok: element model not ok: false
	 */

	public function getElement($searchName, $checkInt = false, $checkShort = true)
	{
		return $this->hasElement($searchName, $checkInt, $checkShort) ? $this->currentElement : false;
	}

	/**
	 * Set the list model
	 *
	 * @param   object  &$listModel  list model
	 *
	 * @return  void
	 */

	public function setListModel(&$listModel)
	{
		$this->listModel = $listModel;
	}

	/**
	 * Is the page a multipage form?
	 *
	 * @return  bool
	 */

	public function isMultiPage()
	{
		$groups = $this->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{
			$params = $groupModel->getParams();
			if ($params->get('split_page'))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Get an object of pages, key'd on page counter and containing an array of the page's group ids
	 *
	 * @return  object
	 */

	public function getPages()
	{
		if (!is_null($this->pages))
		{
			return $this->pages;
		}
		$this->pages = new stdClass;
		$pageCounter = 0;
		$groups = $this->getGroupsHiarachy();
		$c = 0;
		foreach ($groups as $groupModel)
		{
			$params = $groupModel->getParams();
			if ($params->get('split_page') && $c != 0 && $groupModel->canView())
			{
				$pageCounter++;
			}
			if ($groupModel->canView())
			{
				if (!isset($this->pages->$pageCounter))
				{
					$this->pages->$pageCounter = array();
				}
				array_push($this->pages->$pageCounter, $groupModel->getId());
			}
			$c++;
		}
		return $this->pages;
	}

	/**
	 * Should the form submit via ajax or not?
	 *
	 * @return  bool
	 */

	public function isAjax()
	{
		if (is_null($this->ajax))
		{
			$app = JFactory::getApplication();
			$this->ajax = $app->input->getBool('ajax', false);

			// $$$ rob - no element requires AJAX submission!

			/* $groups = $this->getGroupsHiarachy();
			foreach ($groups as $groupModel)
			{
			    $elementModels = $groupModel->getPublishedElements();
			    foreach ($elementModels as $elementModel)
			    {
			        if ($elementModel->requiresAJAXSubmit())
			        {
			            $this->ajax = true;
			        }
			    }
			} */
		}
		return (bool) $this->ajax;
	}

	/**
	 * Used in special case where you have 2 + n-n joins in a single table
	 * In this case the sql query will most likely create four rows of data for
	 * each combination of possibilities
	 *
	 * E.g.
	 *
	 * tbl classes (id, label)
	 *
	 * left joined to:
	 * tbl student_classes (id, label, student_id)
	 *
	 * left joined to
	 * tbl student_teachers (id, label, teacher_id)
	 *
	 * entering one records with 2 students and 2 teachers gives you 4 rows in the query
	 *
	 * classid  student_id, teacher_id
	 * 1        1           1
	 * 1        2	          1
	 * 1        1	          2
	 * 1        2           2
	 *
	 * @since   2.0rc1
	 *
	 * @return  void
	 */

	protected function _reduceDataForXRepeatedJoins()
	{
		$groups = $this->getGroupsHiarachy();
		$listModel = $this->getListModel();
		$pkField = '';
		foreach ($groups as $groupModel)
		{
			/**
			 * $$$ hugh - we need to do this for non-repeat joins as well
			 */
			if ($groupModel->isJoin())
			{
				$joinModel = $groupModel->getJoinModel();
				$tblJoin = $joinModel->getJoin();

				// $$$ hugh - slightly modified these lines so we don't create $this->data['join'] if there is no
				// join data, because that then messes up code subsequent code that checks for empty($this->data)
				if (!isset($this->data['join']))
				{
					// $this->data['join'] = array();
					return;
				}
				if (!array_key_exists($tblJoin->id, $this->data['join']))
				{
					continue;
				}
				if ($tblJoin->table_join == '')
				{
					continue;
				}
				$jdata = &$this->data['join'][$tblJoin->id];
				$db = $listModel->getDb();
				$fields = $db->getTableColumns($tblJoin->table_join, false);
				$keyCount = 0;
				unset($pkField);
				foreach ($fields as $f)
				{
					if ($f->Key == 'PRI')
					{
						if (!isset($pkField))
						{
							$pkField = $tblJoin->table_join . '___' . $f->Field;
						}
						$keyCount ++;
					}
				}
				if (!isset($pkField))
				{
					$pkField = '';
				}
				/*
				 * Corner case if you link to #__user_profile - its primary key is made of 2 elements, so
				 * simply checking on the user_id (the first col) will find duplicate results and incorrectly
				 * merge down.
				 */
				if ($keyCount > 1)
				{
					return;
				}
				$usedkeys = array();
				if (!empty($jdata) && array_key_exists($pkField, $jdata))
				{
					foreach ($jdata[$pkField] as $key => $value)
					{
						/*
						 * $$$rob
						 * added : || ($value === '' && !empty($this->errors))
						 * this was incorrectly reducing empty data
						 * when re-viewing form after failed validation
						 * with a form with repeating groups (with empty data in the key fields
						 *
						 */
						if (!in_array($value, $usedkeys) || ($value === '' && !empty($this->errors)))
						{
							$usedkeys[$key] = $value;
						}
					}
				}
				$keystokeep = array_keys($usedkeys);

				// Remove unneeded data from array
				foreach ($jdata as $key => $value)
				{
					foreach ($value as $key2 => $v)
					{
						if (!in_array($key2, $keystokeep))
						{
							unset($jdata[$key][$key2]);
						}
					}
				}
				// Reduce the keys so that we dont have keys of 0, 2
				foreach ($jdata as $key => $array)
				{
					if ($groupModel->canRepeat())
					{
						$jdata[$key] = array_values($array);
					}
					else
					{
						// $$$ hugh - if it's a one-to-one, it should be a single value
						$aVals = array_values($array);
						$jdata[$key] = JArrayHelper::getValue($aVals, 0, '');
					}
				}
			}
		}
	}

	/**
	 * Query all active form plugins to see if they inject cutsom html into the top
	 * or bottom of the form
	 *
	 * @return  array  plugin top html, plugin bottom html (inside <form>) plugin end (after form)
	 */

	public function getFormPluginHTML()
	{
		$pluginManager = FabrikWorker::getPluginManager();
		$formPlugins = $pluginManager->getPlugInGroup('form');
		$form = $this->getForm();

		$pluginManager->runPlugins('getBottomContent', $this, 'form');
		$pluginbottom = implode("<br />", array_filter($pluginManager->data));

		$pluginManager->runPlugins('getTopContent', $this, 'form');
		$plugintop = implode("<br />", array_filter($pluginManager->data));

		// Inserted after the form's closing </form> tag
		$pluginManager->runPlugins('getEndContent', $this, 'form');
		$pluginend = implode("<br />", array_filter($pluginManager->data));
		return array($plugintop, $pluginbottom, $pluginend);
	}

	/**
	 * Presuming that our introduction looks like this:
	 *
	 * {new:this is an intro}
	 * {edit:You're editing a record}
	 * some more text
	 *
	 * creating a new form record will show the intro text as:
	 *
	 * this is an intro
	 * some more text
	 *
	 * and editing an existing record will show:
	 *
	 * You're editing a record
	 * some more text
	 *
	 * @return string modified intro
	 */

	public function getIntro()
	{
		$intro = $this->getForm()->intro;
		return $this->parseIntroOutroPlaceHolders($intro);
	}

	/**
	 * Parse into and outro text
	 *
	 * @param   string  $text  text to parse
	 *
	 * @since   3.0.7
	 *
	 * @return  string
	 */

	protected function parseIntroOutroPlaceHolders($text)
	{
		$match = ((int) $this->rowId === 0) ? 'new' : 'edit';
		$remove = ((int) $this->rowId === 0) ? 'edit' : 'new';
		$match = "/{" . $match . ":\s*.*?}/i";
		$remove = "/{" . $remove . ":\s*.*?}/i";
		$text = preg_replace_callback($match, array($this, '_getIntroOutro'), $text);
		$text = preg_replace($remove, '', $text);
		$text = str_replace('[', '{', $text);
		$text = str_replace(']', '}', $text);
		if (!$this->isEditable())
		{
			$match = "/{details:\s*.*?}/i";
			$text = preg_replace_callback($match, array($this, '_getIntroOutro'), $text);
		}
		else
		{
			$text = preg_replace("/{details:\s*.*?}/i", '', $text);
		}
		$w = new FabrikWorker;
		$text = $w->parseMessageForPlaceHolder($text, $this->data, true);

		// Jaanus: to remove content plugin code from intro and/or outro when plugins are not processed
		$params = $this->getParams();
		$jplugins = (int) $params->get('process-jplugins', '2');
		if ($jplugins === 0 || ($jplugins === 2 && $this->isEditable()))
		{
			$text = preg_replace("/{\s*.*?}/i", '', $text);
		}
		return $text;
	}

	/**
	 * Used from getIntro as preg_replace_callback function to strip
	 * undeisred text from form label intro
	 *
	 * @param   array  $match  preg matched strings
	 *
	 * @return  string  intro text
	 */

	private function _getIntroOutro($match)
	{
		$m = explode(":", $match[0]);
		array_shift($m);
		return FabrikString::rtrimword(implode(":", $m), "}");
	}
	/**
	 *  Jaanus: see text above about intro
	 *
	 *  @return  string  outro
	 */

	public function getOutro()
	{
		$params = $this->getParams();
		$outro = $params->get('outro');
		return $this->parseIntroOutroPlaceHolders($outro);
	}

	/**
	 * Get the form's label
	 *
	 * @return  string  label
	 */

	public function getLabel()
	{
		$label = $this->getForm()->label;
		if (!$this->isEditable())
		{
			return str_replace("{Add/Edit}", '', $label);
		}
		if (JString::stristr($label, "{Add/Edit}"))
		{
			$replace = ((int) $this->rowId === 0) ? JText::_('COM_FABRIK_ADD') : JText::_('COM_FABRIK_EDIT');
			$label = str_replace("{Add/Edit}", $replace, $label);
		}
		return $label;
	}

	/**
	 * Currently only called from listModel _createLinkedForm when copying existing table
	 *
	 * @TODO should move this to the admin model
	 *
	 * @return  object  form table
	 */

	public function copy()
	{
		// Array key = old id value new id
		$this->groupidmap = array();
		$app = JFactory::getApplication();
		$input = $app->input;
		$groupModels = $this->getGroups();
		$this->form = null;
		$form = $this->getTable();
		$form->id = false;

		// $$$ rob newFormLabel set in table copy
		if ($input->get('newFormLabel', '') !== '')
		{
			$form->label = $input->get('newFormLabel');
		}
		$res = $form->store();
		if (!$res)
		{
			JError::raiseError(500, $form->getErrorMsg());
			return false;
		}
		$newElements = array();
		foreach ($groupModels as $groupModel)
		{
			$oldid = $groupModel->getId();

			// $$$rob use + rather than array_merge to preserve keys
			$groupModel->_newFormid = $form->id;
			$newElements = $newElements + $groupModel->copy();

			$this->groupidmap[$oldid] = $groupModel->getGroup()->id;
		}
		// Need to do finalCopyCheck() on form elements
		$pluginManager = FabrikWorker::getPluginManager();

		// @TODO something not right here when copying a cascading dropdown element in a join group
		foreach ($newElements as $origId => $newId)
		{
			$plugin = $pluginManager->getElementPlugin($newId);
			$plugin->finalCopyCheck($newElements);
		}
		// Update the model's table to the copied one
		$this->form = $form;
		$this->setId($form->id);
		$this->newElements = $newElements;
		return $form;
	}

	/**
	 * Get the related lists (relations defined by db join foreign keys)
	 *
	 * @return  array  of links to view the related lists
	 */

	public function getRelatedTables()
	{
		$db = FabrikWorker::getDbo(true);
		$app = JFactory::getApplication();
		$input = $app->input;
		$links = array();
		$params = $this->getParams();
		if (!$params->get('show-referring-table-releated-data', false))
		{
			return $links;
		}
		$listModel = $this->getListModel();
		$referringTable = JModelLegacy::getInstance('List', 'FabrikFEModel');

		// $$$ rob - not sure that referring_table is anything other than the form's table id
		// but for now just defaulting to that if no other variable found (e.g when links in sef urls)
		$tid = $input->getInt('referring_table', $input->getInt('listid', $listModel->getTable()->id));
		$referringTable->setId($tid);
		$tmpKey = '__pk_val';
		$tableParams = $referringTable->getParams();
		$table = $referringTable->getTable();
		$joinsToThisKey = $referringTable->getJoinsToThisKey();
		$linksToForms = $referringTable->getLinksToThisKey();

		$row = $this->getData();
		$factedLinks = $tableParams->get('factedlinks', null);
		if (is_null($factedLinks))
		{
			return;
		}
		$linkedLists = $factedLinks->linkedlist;
		$aExisitngLinkedForms = $factedLinks->linkedform;
		$linkedform_linktype = $factedLinks->linkedform_linktype;
		$linkedtable_linktype = $factedLinks->linkedlist_linktype;
		$f = 0;
		foreach ($joinsToThisKey as $joinKey => $element)
		{
			$key = $element->list_id . '-' . $element->form_id . '-' . $element->element_id;
			if (isset($linkedLists->$key) && $linkedLists->$key != 0)
			{
				$qsKey = $referringTable->getTable()->db_table_name . '___' . $element->name;
				$val = $input->get($qsKey);
				if ($val == '')
				{
					// Default to row id if we are coming from a main link (and not a related data link)
					$val = $input->get($qsKey . '_raw', '', 'string');
					if (empty($val))
					{
						$thisKey = $this->getListModel()->getTable()->db_table_name . '___' . $element->join_key_column . '_raw';
						$val = JArrayHelper::getValue($this->data, $thisKey, $val);
						if (empty($val))
						{
							$val = $input->get('rowid');
						}
					}
				}

				/* $$$ hugh - changed to use _raw as key, see:
				 * http://fabrikar.com/forums/showthread.php?t=20020
				 */
				$linkKey = $element->db_table_name . '___' . $element->name;
				$linkKeyRaw = $linkKey . '_raw';
				$popUpLink = JArrayHelper::getValue($linkedtable_linktype->$key, $f, false);

				/* $$$ tom 2012-09-14 - If we don't have a key value, get all.  If we have a key value,
				 * use it to restrict the count to just this entry.
				 */
				$pks = array();
				if (!empty($val))
				{
					$pks[] = $val;
				}
				$recordCounts = $referringTable->getRecordCounts($element, $pks);

				$count = is_array($recordCounts) && array_key_exists($val, $recordCounts) ? $recordCounts[$val]->total : 0;

				$label = $factedLinks->linkedlistheader->$key == '' ? $element->listlabel : $factedLinks->linkedlistheader->$key;
				$links[$element->list_id][] = $label . ': ' . $referringTable->viewDataLink($popUpLink, $element, null, $linkKey, $val, $count, $f);
			}
			$f++;
		}
		$f = 0;

		// Create columns containing links which point to forms assosciated with this table
		foreach ($linksToForms as $element)
		{
			if ($element !== false)
			{
				$key = $element->list_id . '-' . $element->form_id . '-' . $element->element_id;
				$linkedForm = $aExisitngLinkedForms->$key;
				$popUpLink = $linkedform_linktype->$key;
				if ($linkedForm !== '0')
				{
					if (is_object($element))
					{
						// $$$rob moved these two lines here as there were giving warnings since Hugh commented out the if ($element != '') {
						// $$$ hugh - what?  Eh?  WhaddidIdo?  Anyway, we use $linkKey up ^^ there somewhere, so we need to define it earlier!
						$linkKey = @$element->db_table_name . '___' . @$element->name;
						$val = $input->get($linkKey, '', 'string');
						if ($val == '')
						{
							$val = $input->get($qsKey . '_raw', $input->get('rowid'));
						}
						$label = $factedLinks->linkedformheader->$key;
						$links[$element->list_id][] = $label . ': ' . $referringTable->viewFormLink($popUpLink, $element, null, $linkKey, $val, false, $f);
					}
				}
				$f++;
			}
		}
		return $links;
	}

	/**
	 * Create the form's html class name.
	 * Based on column counts etc as to whether form-horizontal applied
	 *
	 * @return  string
	 */

	public function getFormClass()
	{
		$params = $this->getParams();
		$class = array('');
		$horiz = true;
		$groups = $this->getGroupsHiarachy();
		foreach ($groups as $gkey => $groupModel)
		{
			$groupParams = $groupModel->getParams();
			if ($groupParams->get('group_columns', 1) > 1)
			{
				$horiz = false;
			}
		}
		if ($horiz && $params->get('labels_above', 0) != 1)
		{
			$class[] = 'form-horizontal';
		}
		return implode(' ', $class);
	}

	/**
	 * Get the url to use as the form's action property
	 *
	 * @return	string	url
	 */

	public function getAction()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');

		// Get the router
		$router = $app->getRouter();
		if ($app->isAdmin())
		{
			$action = JArrayHelper::getValue($_SERVER, 'REQUEST_URI', 'index.php');
			$action = str_replace("&", "&amp;", $action);

			// $$$rob no good for cck form?
			// return "index.php";
			return $action;
		}
		/* if ((int) $this->packageId !== 0)
		{
			$action = 'index.php?option=com_fabrik&view=form&formid=' . $this->getId();
			return $action;
		} */
		$option = $app->input->get('option');
		if ($option === 'com_' . $package)
		{
			$page = 'index.php?';

			// Get array of all querystring vars
			$uri = JFactory::getURI();

			/**
			 * Was $router->parse($uri);
			 * but if you had a module + form on a page using sef urls and
			 * Joomla's language switcher - calling parse() would re-parse the url and
			 * mung it well good and proper like.
			 *
			 */
			$queryvars = $router->getVars();
			if ($this->isAjax())
			{
				$queryvars['format'] = 'raw';
				unset($queryvars['view']);
				$queryvars['task'] = 'form.process';
			}
			$qs = array();
			foreach ($queryvars as $k => $v)
			{
				if ($k == 'rowid')
				{
					$v = $this->getRowId();
				}
				/* $$$ hugh - things get weird if we have been passed a urlencoded URL as a qs arg,
				 * which the $router->parse() above will have urldecoded, and it gets used as part of the URI path
				 * when we JRoute::_() below.  So we need to re-urlencode stuff and junk.
				 * Ooops, make sure it isn't an array, which we'll get if they have something like
				 * &table___foo[value]=bar
				 */
				if (!is_array($v))
				{
					$v = urlencode($v);
					$qs[] = $k . '=' . $v;
				}
				else
				{
					foreach ($v as $subV)
					{
						$qs[] = $k . '[]=' . urlencode($subV);
					}
				}

			}
			$action = $page . implode("&amp;", $qs);
			$action = JRoute::_($action);
		}
		else
		{
			// In plugin & SEF URLs
			if ((int) $router->getMode() === (int) JROUTER_MODE_SEF)
			{
				// $$$ rob if embedding a form in a form, then the embedded form's url will contain
				// the id of the main form - not sure if its an issue for now
				$action = JArrayHelper::getValue($_SERVER, 'REQUEST_URI', 'index.php');
			}
			else
			{
				// In plugin and no sef (routing dealt with in form controller)
				$action = 'index.php';
			}
		}
		return $action;
	}

	/**
	 * If the group is a joined group we want to ensure that
	 * its id field is contained with in the group's elements
	 *
	 * @param   object  &$groupTable  group table
	 *
	 * @return	string	html hidden field
	 */

	protected function _makeJoinIdElement(&$groupTable)
	{
		$listModel = $this->getListModel();
		$joinId = $this->aJoinGroupIds[$groupTable->id];
		$element = new stdClass;

		// Add in row id for join data
		$element->label = '';
		$element->error = '';
		$element->value = '';
		$element->id = '';
		$element->startRow = 0;
		$element->endRow = 0;
		$element->errorTag = '';
		$element->column = '';
		$element->className = '';
		$element->containerClass = '';
		foreach ($listModel->getJoins() as $oJoin)
		{
			if ($oJoin->id == $joinId)
			{
				$key = $oJoin->table_join . $this->joinTableElementStep . $oJoin->table_join_key;
				if (array_key_exists('join', $this->data))
				{
					// $$$ rob if join element is a db join the data $key contains label and not foreign key value
					if (@array_key_exists($key . '_raw', $this->data['join'][$joinId]))
					{
						$val = $this->data['join'][$joinId][$key . '_raw'];
					}
					else
					{
						$val = @$this->data['join'][$joinId][$key];
					}
					if (is_array($val))
					{
						if (array_key_exists(0, $val))
						{
							$val = $val[0];
						}
						else
						{
							$val = '';
						}
					}
				}
				else
				{
					$val = '';
				}
				if ($val == '')
				{
					// Somethings gone wrong - lets take the main table's key
					$k = $oJoin->join_from_table . $this->joinTableElementStep . $oJoin->table_key;
					$val = @$this->data[$k];
				}
				$element->value = $val;
				$element->element = '<input type="hidden" id="join.' . $joinId . '.rowid" name="join[' . $joinId . '][rowid]" value="' . $val
					. '" />';
				$element->hidden = true;
				$element->containerClass = 'fabrikElementContainer  fabrikHide';
			}
		}
		return $element;
	}

	/**
	 * Get an array of read only values
	 *
	 * @return  array
	 */

	public function getreadOnlyVals()
	{
		return $this->readOnlyVals;
	}

	/**
	 * Prepare the elements for rendering
	 *
	 * @param   string  $tmpl  form template
	 *
	 * @since   3.0
	 *
	 * @return  array
	 */

	public function getGroupView($tmpl = '')
	{
		if (isset($this->groupView))
		{
			return $this->groupView;
		}
		$app = JFactory::getApplication();
		$input = $app->input;

		// $$$rob - do regardless of whether form is editable as $data is required for hidden encrypted fields
		// and not used anywhere else (avoids a warning message)
		$data = array();
		/* $$$ rob - 3.0 for some reason just using $this->data was not right as join data was empty when editing exisitng record
		 * $$$ hugh - commented this out, as a) running getData() twice is expensive, and b) it blows away any changes onLoad plugins
		 * make to _data, like the juser plugin
		 * Ran this change for a couple of weeks before committing, seems to work without it.
		 *unset($this->data);
		 */
		$origData = $this->getData();
		foreach ($origData as $key => $val)
		{
			if (is_string($val))
			{
				$data[$key] = htmlspecialchars($val, ENT_QUOTES);
			}
			else
			{
				// Not sure what the htmlspecialchars is for above but if we dont assign here we loose join data
				$data[$key] = $val;
			}
		}
		$this->groupView = array();
		$this->readOnlyVals = array();

		// $$$ hugh - temp foreach fix
		$groups = $this->getGroupsHiarachy();
		foreach ($groups as $gkey => $groupModel)
		{
			$groupTable = $groupModel->getGroup();
			$group = $groupModel->getGroupProperties($this);
			$groupParams = $groupModel->getParams();
			$group->intro = $groupParams->get('intro');
			$group->columns = $groupParams->get('group_columns', 1);
			$group->splitPage = $groupParams->get('split_page', 0);
			if ($groupModel->canRepeat())
			{
				$group->tmpl = $groupParams->get('repeat_template', 'repeatgroup');
			}
			else
			{
				$group->tmpl = 'group';
			}
			$aElements = array();

			// Check if group is acutally a table join
			if (array_key_exists($groupTable->id, $this->aJoinGroupIds))
			{
				$aElements[] = $this->_makeJoinIdElement($groupTable);
			}
			$repeatGroup = 1;
			$foreignKey = null;
			$startHidden = false;
			if ($groupModel->canRepeat())
			{
				$joinTable = $groupModel->getJoinModel()->getJoin();
				$foreignKey = '';
				if (is_object($joinTable))
				{

					$repeatGroup = $groupModel->repeatCount();
					if (!$groupModel->fkPublished())
					{
						$startHidden = false;
					}
					else
					{
						// Show empty groups if we are validating a posted form

						// @TODO - relook at this !IMPORTANT
						/* if ($input->get('task') !== 'process' && $input->get('task') !== 'form.process')
						{
							$this->getSessionData();
							if ($this->sessionModel->row->data === '')
							{
								$startHidden = true;
								foreach ($origData['join'][$joinTable->id] as $jData)
								{
									if (!empty($jData[0]))
									{
										$startHidden = false;
										continue;
									}
								}
							}
						} */
					}

				}
			}
			// Test failed validated forms, repeat group counts are in request
			$repeatGroups = $input->get('fabrik_repeat_group', array(), 'array');
			if (!empty($repeatGroups))
			{
				$repeatGroup = JArrayHelper::getValue($repeatGroups, $gkey, $repeatGroup);
				if ($repeatGroup == 0)
				{
					$repeatGroup = 1;
					$startHidden = true;
				}
			}

			$groupModel->repeatTotal = $startHidden ? 0 : $repeatGroup;
			$aSubGroups = array();
			for ($c = 0; $c < $repeatGroup; $c++)
			{
				$aSubGroupElements = array();
				$elCount = 0;
				$elementModels = $groupModel->getPublishedElements();
				foreach ($elementModels as $elementModel)
				{
					/* $$$ rob ensure that the element is associated with the correct form (could occur if n plugins rendering form
					 * and detailed views of the same form.
					 */
					$elementModel->setFormModel($this);
					$elementModel->tmpl = $tmpl;

					/* $$$rob test don't include the element in the form is we can't use and edit it
					 * test for captcha element when user logged in
					 */
					if (!$this->isEditable())
					{
						$elementModel->inDetailedView = true;
					}

					if (!$this->isEditable() && !$elementModel->canView())
					{
						continue;
					}

					$elementModel->_foreignKey = $foreignKey;
					$elementModel->_repeatGroupTotal = $repeatGroup - 1;
					$element = $elementModel->preRender($c, $elCount, $tmpl);

					// $$$ hugh - experimenting with adding non-viewable, non-editable to encrypted vars
					// if (!$element || ($elementModel->canView() && !$elementModel->canUse()))
					if (!$element || !$elementModel->canUse())
					{
						/* $$$ hugh - $this->data doesn't seem to always have what we need in it, but $data does.
						 * can't remember exact details, was chasing a nasty issue with encrypted 'user' elements.
						 */

						// $$$ rob HTMLName seems not to work for joined data in confirmation plugin
						$elementModel->getValuesToEncrypt($this->readOnlyVals, $data, $c);
						$this->readOnlyVals[$elementModel->getFullName(true, false)]['repeatgroup'] = $groupModel->canRepeat();
						$this->readOnlyVals[$elementModel->getFullName(true, false)]['join'] = $groupModel->isJoin();
					}
					if ($element)
					{
						$elementModel->stockResults($element, $aElements, $this->data, $aSubGroupElements);
					}
					if ($element && !$element->hidden)
					{
						$elCount++;
					}
				}
				// If its a repeatable group put in subgroup
				if ($groupModel->canRepeat())
				{
					// Style attribute for group columns (need to occur after randomisation of the elements otherwise clear's are not ordered correctly)
					$ix = 1;
					foreach ($aSubGroupElements as $elKey => $element)
					{
						$ix = $groupModel->setColumnCss($element, $ix);
					}
					$aSubGroups[] = $aSubGroupElements;
				}
			}
			$groupModel->randomiseElements($aElements);

			// Style attribute for group columns (need to occur after randomisation of the elements otherwise clear's are not ordered correctly)
			$ix = 1;
			foreach ($aElements as $elKey => $element)
			{
				$ix = $groupModel->setColumnCss($element, $ix);
			}
			$group->elements = $aElements;
			$group->subgroups = $aSubGroups;
			$group->startHidden = $startHidden;

			// Only create the group if there are some element inside it
			if (count($aElements) != 0 && $groupModel->canView() !== false)
			{
				// 28/01/2011 $$$rob and if it is published
				$showGroup = (int) $groupParams->get('repeat_group_show_first');
				if ($showGroup !== 0)
				{
					// $$$ - hugh - testing new 'hide if no usable elements' option (4)
					// Jaanus: if not form view with "details only" option and not details view with "form only" option
					if (!($showGroup == 2 && $this->isEditable()) && !($showGroup == 3 && $input->get('view', 'form') == 'details')
						&& !($showGroup == 4 && !$groupModel->canView()))
					{
						$this->groupView[$group->name] = $group;
					}
				}
			}
		}
		return $this->groupView;
	}

	/**
	 * Get any fabrik tables that link to the join table
	 *
	 * @param   string  $table  table name
	 *
	 * @return  array
	 */

	public function getLinkedFabrikLists($table)
	{
		if (!isset($this->linkedFabrikLists))
		{
			$this->linkedFabrikLists = array();
		}
		if (!array_key_exists($table, $this->linkedFabrikLists))
		{
			$db = FabrikWorker::getDbo(true);
			if (trim($table == ''))
			{
				return array();
			}
			else
			{
				$query = $db->getQuery(true);
				$query->select('*')->from('#__{package}_lists')->where('db_table_name = ' . $db->quote($table));
				$db->setQuery($query);
			}
			$this->linkedFabrikLists[$table] = $db->loadColumn();
			if ($db->getErrorNum())
			{
				JError::raiseError(500, $db->getErrorMsg());
			}
		}
		return $this->linkedFabrikLists[$table];
	}

	/**
	 * Used to see if something legitimate in the submission process, like a form plugin,
	 * has modified an RO element value and wants to override the RO/origdata.
	 *
	 * If $value is set, then this method additionally adds the modified value to the updated array.
	 *
	 * @param   string  $fullname  full element name
	 * @param   mixed   $value     optional value, states that a plugin update the readonly value of $fullname
	 *
	 * @return bool
	 */

	public function updatedByPlugin($fullname = '', $value = null)
	{
		if (isset($value))
		{
			$this->pluginUpdatedElements[$fullname] = $value;
		}
		return array_key_exists($fullname, $this->pluginUpdatedElements);
	}

	/**
	 * Populate the model state
	 *
	 * @return  void
	 */

	protected function populateState()
	{
		$app = JFactory::getApplication('site');
		$input = $app->input;
		if (!$app->isAdmin())
		{
			// Load the menu item / component parameters.
			$params = $app->getParams();
			$this->setState('params', $params);

			// Load state from the request.
			$pk = $input->getInt('formid', $params->get('formid'));
		}
		else
		{
			$pk = $input->getInt('formid');
		}
		$this->setState('form.id', $pk);
	}

	/**
	 * Inline edit show the edited element
	 *
	 * @return string
	 */

	public function inLineEditResult()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$listModel = $this->getListModel();
		$listid = $listModel->getId();
		$listModel->clearCalculations();
		$listModel->doCalculations();
		$elementid = $input->getInt('elid');
		if ($elementid === 0)
		{
			return;
		}
		$elmentModel = $this->getElement($elementid, true);
		if (!$elmentModel)
		{
			return;
		}
		$rowid = $input->get('rowid');
		$listModel->setId($listid);

		// If the inline edit stored a element join we need to reset back the table
		$listModel->clearTable();
		$listModel->getTable();
		$data = JArrayHelper::fromObject($listModel->getRow($rowid));
		$key = $input->get('element');
		$html = '';
		$html .= $elmentModel->renderListData($data[$key], $data);
		$listRef = 'list_' . $input->get('listref');
		$doCalcs = "\nFabrik.blocks['" . $listRef . "'].updateCals(" . json_encode($listModel->getCalculations()) . ")";
		$html .= '<script type="text/javasript">';
		$html .= $doCalcs;
		$html .= "</script>\n";
		return $html;
	}

	/**
	 * Is the form editable
	 *
	 * @return  bool
	 */

	public function isEditable()
	{
		return $this->editable;
	}

	/**
	 * Set editable state
	 *
	 * @param   bool  $editable  editable state
	 *
	 * @since 3.0.7
	 *
	 * @return  void
	 */

	public function setEditable($editable)
	{
		$this->editable = $editable;
	}

	/**
	 * Helper method to get the session redirect key. Redirect plugin stores this
	 * other form plugins such as twitter or paypal may need to query the session to perform the final redirect
	 * once the user has returned from those sites.
	 *
	 * @return  string  the session key to store redirect information (note: ends in '.')
	 */

	public function getRedirectContext()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		return 'com_' . $package . '.form.' . $this->getId() . '.redirect.';
	}

	/**
	 * Resets cached form data.
	 *
	 * @param   bool  $unset_groups  also reset group and element model cached data
	 *
	 * @return  void
	 */

	public function unsetData($unset_groups = false)
	{
		unset($this->data);
		unset($this->query);
		if ($unset_groups)
		{
			/* $$$ hugh - unset group published elements list, and clear each
			 * element's default data.  Needed from content plugin, otherwise if
			 * we render the same form more than once with different rowid's, we end up
			 * rendering the first copy's element data X times.
			 * Not sure if we need to actually unset the group published elements list,
			 * but for the moment I'm just using a Big Hammer to get the content plugin working!
			 */
			$groups = $this->getGroupsHiarachy();
			foreach ($groups as $groupModel)
			{
				$groupModel->resetPublishedElements();
				$elementModels = $groupModel->getPublishedElements();
				foreach ($elementModels as $elementModel)
				{
					$elementModel->reset();
				}
			}
			unset($this->groups);
			$pluginManager = FabrikWorker::getPluginManager();
			$pluginManager->clearFormPlugins($this);
		}
	}

	/**
	 * Reset form's cached data, i.e. from content plugin, where we may be rendering the same
	 * form twice, with different row data.
	 *
	 * @return  void
	 */

	public function reset()
	{
		$this->unsetData(true);
	}

	/**
	 * Get redirect URL
	 *
	 * @param   bool  $incSession  set url in session?
	 * @param   bool  $isMambot    is Mambot
	 *
	 * @return   array  url: string  redirect url, baseRedirect (True: default redirect, False: plugin redirect)
	 *
	 * @since 3.0.6 (was in form controller)
	 */

	public function getRedirectURL($incSession = true, $isMambot = false)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$package = $app->getUserState('com_fabrik.package', 'fabrik');

		if ($app->isAdmin())
		{
			// Admin always uses option com_fabrik
			if (array_key_exists('apply', $this->formData))
			{
				$url = 'index.php?option=com_fabrik&task=form.view&formid=' . $input->getInt('formid') . '&rowid=' . $input->getString('rowid', '', 'string');
			}
			else
			{
				$url = 'index.php?option=com_fabrik&task=list.view&listid=' . $this->getListModel()->getId();
			}
		}
		else
		{
			if (array_key_exists('apply', $this->formData))
			{
				$url = 'index.php?option=com_' . $package . '&view=form&formid=' . $input->getInt('formid') . '&rowid=' . $input->getString('rowid', '', 'string')
					. '&listid=' . $input->getInt('listid');
			}
			else
			{
				if ($isMambot)
				{
					// Return to the same page
					$url = JArrayHelper::getValue($_SERVER, 'HTTP_REFERER', 'index.php');
				}
				else
				{
					// Return to the page that called the form
					$url = urldecode($input->post->get('fabrik_referrer', 'index.php', 'string'));
				}
				$Itemid = (int) FabrikWorker::itemId();
				if ($url == '')
				{
					if ($Itemid !== 0)
					{
						$url = 'index.php?' . http_build_query($app->getMenu('site')->getActive()->query) . '&Itemid=' . $Itemid;
					}
					else
					{
						// No menu link so redirect back to list view
						$url = 'index.php?option=com_' . $package . '&view=list&listid=' . $input->getInt('listid');
					}
				}
			}
			$config = JFactory::getConfig();
			if ($config->get('sef'))
			{
				$url = JRoute::_($url);
			}
		}
		// 3.0 need to distinguish between the default redirect and redirect plugin
		$baseRedirect = true;
		if (!$incSession)
		{
			return array('url' => $url, 'baseRedirect' => $baseRedirect);
		}
		$session = JFactory::getSession();
		$formdata = $session->get('com_' . $package . '.form.data');
		$context = $this->getRedirectContext();

		// If the redirect plug-in has set a url use that in preference to the default url
		$surl = $session->get($context . 'url', array());
		if (!empty($surl))
		{
			$baseRedirect = false;
		}
		if (!is_array($surl))
		{
			$surl = array($surl);
		}
		if (empty($surl))
		{
			$surl[] = $url;
		}
		$url = array_shift($surl);
		$session->set($context . 'url', $surl);

		// Redirect URL which set prefilters of < were converted to &lt; which then gave mySQL error
		$url = htmlspecialchars_decode($url);
		return array('url' => $url, 'baseRedirect' => $baseRedirect);
	}

	/**
	 * Should we show success messages
	 *
	 * @since  3.0.7
	 *
	 * @return boolean
	 */

	public function showSuccessMsg()
	{
		$mode = $this->getParams()->get('suppress_msgs', '0');
		return ($mode == 0 || $mode == 2);
	}

	/**
	 * Should we show ACL messages
	 *
	 * @since  3.0.7
	 *
	 * @return boolean
	 */

	public function showACLMsg()
	{
		$mode = $this->getParams()->get('suppress_msgs', '0');
		return $mode == 0 || $mode == 1;
	}

	/**
	 * If trying to add/edit a record when the user doesn't have rights to do so,
	 * what message, if any should we show.
	 *
	 * @since  3.0.7
	 *
	 * @return string
	 */

	public function aclMessage()
	{
		if (!$this->showACLMsg())
		{
			return '';
		}
		$input = JFactory::getApplication()->input;
		$msg = $input->get('rowid', '', 'string') == 0 ? 'COM_FABRIK_NOTICE_CANT_ADD_RECORDS' : 'COM_FABRIK_NOTICE_CANT_EDIT_RECORDS';
		return JText::_($msg);
	}
	/**
	 * Get redirect message
	 *
	 * @return  string  redirect message
	 *
	 * @since   3.0.6 (was in form controller)
	 */

	public function getRedirectMessage()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$session = JFactory::getSession();
		$registry = $session->get('registry');
		$formdata = $session->get('com_' . $package . '.form.data');

		// $$$ rob 30/03/2011 if using as a search form don't show record added message
		if ($registry && $registry->get('com_' . $package . '.searchform.fromForm') != $this->get('id'))
		{
			$msg = $this->showSuccessMsg() ? $this->getParams()->get('submit-success-msg', JText::_('COM_FABRIK_RECORD_ADDED_UPDATED')) : '';
		}
		else
		{
			$msg = '';
		}
		$context = $this->getRedirectContext();
		$smsg = $session->get($context . 'msg', array($msg));
		if (!is_array($smsg))
		{
			$smsg = array($smsg);
		}
		if (empty($smsg))
		{
			$smsg[] = $msg;
		}
		/**
		 * $$$ rob Was using array_shift to set $msg, not to really remove it from $smsg
		 * without the array_shift the custom message is never attached to the redirect page.
		 * use case 'redirct plugin with jump page pointing to a J page and thanks message selected.
		 */
		$custommsg = array_keys($smsg);
		$custommsg = array_shift($custommsg);
		$custommsg = JArrayHelper::getValue($smsg, $custommsg);
		if ($custommsg != '')
		{
			$msg = $custommsg;
		}
		$app = JFactory::getApplication();
		$q = $app->getMessageQueue();
		$found = false;
		foreach ($q as $m)
		{
			// Custom message already queued - unset default msg
			if ($m['type'] == 'message' && trim($m['message']) !== '')
			{
				$found = true;
				break;
			}
		}
		if ($found)
		{
			$msg = null;
		}
		$showmsg = null;
		$session->set($context . 'msg', $smsg);
		$showmsg = (array) $session->get($context . 'showsystemmsg', array(true));
		if (is_array($showmsg))
		{
			$showmsg = array_shift($showmsg);
		}
		$msg = $showmsg == 1 ? $msg : '';
		return $msg;
	}

	/**
	 * Build the JS key that the model uses in the view. This key is assigned to Fabrik.blocks
	 *
	 * @since   3.1rc1
	 *
	 * @return  string
	 */

	public function jsKey()
	{
		$key = $this->isEditable() ? 'form_' . $this->getId() : 'details_' . $this->getId();
		if ($this->getRowId() != '')
		{
			$key .= '_' . $this->getRowId();
		}
		return $key;
	}

}
