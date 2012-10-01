<?php

class CM_Menu {
	/**
	 * @var CM_MenuEntry[]
	 */
	protected $_entries = array();

	/**
	 * @var string
	 */
	private $_path;

	/**
	 * @var CM_Params
	 */
	private $_params;

	/**
	 * Creates a new menu object with the given menu entries as array
	 *
	 * @param array              $menuEntries Menu entries
	 * @param string             $path
	 * @param CM_Params          $params
	 * @param CM_MenuEntry|null  $parent
	 */
	public final function __construct(array $menuEntries, $path, CM_Params $params, CM_MenuEntry $parent = null) {
		$this->_path = (string) $path;
		$this->_params = $params;

		foreach ($menuEntries as $menuEntry) {
			$entry = new CM_MenuEntry($menuEntry, $this, $parent);
			$this->_entries[] = $entry;
		}
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->_path;
	}

	/**
	 * @return CM_Params
	 */
	public function getParams() {
		return $this->_params;
	}

	/**
	 * @param CM_Page_Abstract $page
	 * @param int              $depthMin      OPTIONAL
	 * @param int              $depthMax      OPTIONAL
	 * @param int              $_currentDepth OPTIONAL
	 * @return CM_MenuEntry|null
	 */
	public final function findEntry(CM_Page_Abstract $page, $depthMin = 0, $depthMax = null, $_currentDepth = 0) {
		foreach ($this->getAllEntries() as $entry) {
			// Page found
			if ($_currentDepth >= $depthMin) {
				if ($entry->compare($page::getPath(), $page->getParams()->getAllOriginal())) {
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
	 * @return CM_MenuEntry[]
	 */
	public final function getAllEntries() {
		return $this->_entries;
	}

	/**
	 * @param CM_Model_User|null $viewer
	 * @return CM_MenuEntry[]
	 */
	public final function getEntries(CM_Model_User $viewer = null) {
		return array_filter($this->_entries, function (CM_MenuEntry $entry) use ($viewer) {
			return $entry->isViewable($viewer);
		});
	}
}
