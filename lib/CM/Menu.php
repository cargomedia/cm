<?php

/**
 * Menu object class. Uses an array to create the object
 */

class CM_Menu {
	/**
	 * @var CM_MenuEntry[]
	 */
	protected $_entries = array();

	/**
	 * @var CM_MenuEntry[]
	 */
	protected $_allEntries = array();

	/**
	 * @var CM_Request_Abstract
	 */
	private $_request;

	/**
	 * Creates a new menu object with the given menu entries as array
	 *
	 * @param array							$menuEntries Menu entries
	 * @param CM_Request_Abstract			  $request
	 * @param CM_MenuEntry|null				$parent
	 */
	public final function __construct(array $menuEntries, CM_Request_Abstract $request, CM_MenuEntry $parent = null) {
		$this->_request = $request;

		foreach ($menuEntries as $menuEntry) {
			$entry = new CM_MenuEntry($menuEntry, $this, $parent);
			$this->_allEntries[] = $entry;
			if ($entry->getPage()->isViewable()) {
				$this->_entries[] = $entry;
			}
		}
	}

	/**
	 * @return CM_Request_Abstract
	 */
	public function getRequest() {
		return $this->_request;
	}

	/**
	 * @param CM_Page_Abstract $page
	 * @param int			  $depthMin	  OPTIONAL
	 * @param int			  $depthMax	  OPTIONAL
	 * @param int			  $_currentDepth OPTIONAL
	 * @return CM_MenuEntry|null
	 */
	public final function findEntry(CM_Page_Abstract $page, $depthMin = 0, $depthMax = null, $_currentDepth = 0) {
		foreach ($this->getAllEntries() as $entry) {
			// Page found
			if ($_currentDepth >= $depthMin) {
				if ($entry->compare($page->getPath(), $page->getParams()->getAll())) {
					return $entry;
				}
			}

			if ((null === $depthMax || $_currentDepth < $depthMax) && $entry->hasChildren()) {
				// Checks sub tree
				$foundEntry = $entry->getChildren()->findEntry($page, $depthMin, $depthMax, $_currentDepth + 1);

				// Entry was found
				if ($foundEntry) {
					return $foundEntry;
				}
			}
		}

		return null;
	}

	/**
	 * Returns all menu entries independent of viewable or not
	 *
	 * @return CM_MenuEntry[]
	 */
	public final function getAllEntries() {
		return $this->_allEntries;
	}

	/**
	 * Returns the list of available (viewable) menu entries
	 *
	 * @return CM_MenuEntry[]
	 */
	public final function getEntries() {
		return $this->_entries;
	}
}
