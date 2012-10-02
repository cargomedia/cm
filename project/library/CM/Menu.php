<?php

class CM_Menu {
	/**
	 * @var CM_MenuEntry[]
	 */
	protected $_entries = array();

	/**
	 * Creates a new menu object with the given menu entries as array
	 *
	 * @param array              $menuEntries Menu entries
	 * @param CM_MenuEntry|null  $parent
	 */
	public function __construct(array $menuEntries, CM_MenuEntry $parent = null) {
		foreach ($menuEntries as $menuEntry) {
			$entry = new CM_MenuEntry($menuEntry, $this, $parent);
			$this->_entries[] = $entry;
		}
	}

	/**
	 * @param CM_Page_Abstract $page
	 * @param int|null         $depthMin
	 * @param int|null         $depthMax
	 * @param boolean|null     $findAll
	 * @param int              $_currentDepth
	 * @return CM_MenuEntry|CM_MenuEntry[]|null
	 */
	public final function findEntry(CM_Page_Abstract $page, $depthMin = null, $depthMax = null, $findAll = null, $_currentDepth = 0) {
		if (is_null($depthMin)) {
			$depthMin = 0;
		}
		if (is_null($findAll)) {
			$findAll = false;
		}
		$entries = array();
		foreach ($this->getAllEntries() as $entry) {
			// Page found
			if ($findAll || $_currentDepth >= $depthMin) {
				if ($entry->compare($page::getPath(), $page->getParams()->getAllOriginal())) {
					if (!$findAll) {
						return $entry;
					}
					$entries[] = $entry;
				}
			}

			if (($findAll || null === $depthMax || $_currentDepth < $depthMax) && $entry->hasChildren()) {
				// Checks sub tree
				$foundEntry = $entry->getChildren()->findEntry($page, $depthMin, $depthMax, $findAll, $_currentDepth + 1);

				// Entry was found
				if ($foundEntry) {
					if (!$findAll) {
						return $foundEntry;
					}
					$entries = array_merge($entries, $foundEntry);
				}
			}
		}
		if ($findAll) {
			return $entries;
		}
		return null;
	}

	/**
	 * @param CM_Page_Abstract $page
	 * @return CM_MenuEntry[]
	 */
	public final function findEntries(CM_Page_Abstract $page) {
		return $this->findEntry($page, null, null, true);
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
