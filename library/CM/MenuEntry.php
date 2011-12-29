<?php

class CM_MenuEntry {

	/**
	 * @var string
	 */
	protected $_class = '';

	/**
	 * @var string
	 */
	private $_icon = null;

	/**
	 * @var CM_Menu
	 */
	protected $_submenu = null;

	/**
	 * @var array
	 */
	protected $_data = array();

	/**
	 * @var CM_MenuEntry
	 */
	protected $_parent = null;

	/**
	 * @var CM_Menu
	 */
	protected $_menu = null;

	/**
	 * @param array			  $data
	 * @param CM_Menu|null	   $menu
	 * @param CM_MenuEntry|null  $parent
	 */
	public final function __construct(array $data, CM_Menu $menu, CM_MenuEntry $parent = null) {
		$this->_data = $data;
		$this->_parent = $parent;
		$this->_menu = $menu;

		if (!isset($data['page'])) {
			throw new CM_Exception_Invalid('Page param has to be set');
		}

		if (!isset($data['label'])) {
			throw new CM_Exception_Invalid('Menu label has to be set');
		}

		if (isset($data['submenu'])) {
			$this->_submenu = new CM_Menu($data['submenu'], $this->_menu->getRequest(), $this);
		}

		if (isset($data['class'])) {
			$this->_class = $data['class'];
		}

		if (isset($data['icon'])) {
			$this->_icon = $data['icon'];
		}
	}

	/**
	 * @param string $path
	 * @param array  $params
	 * @return bool True if path/queries match
	 */
	public final function compare($path, array $params = array()) {
		if ($path == $this->getPage()->getPath() && array_intersect_assoc($this->getParams(), $params) == $this->getParams()) {
			return true;
		}
		return false;
	}

	/**
	 * @return CM_Menu Submenu/children of this entry
	 */
	public final function getChildren() {
		return $this->_submenu;
	}

	/**
	 * @return string
	 */
	public final function getClass() {
		return $this->_class;
	}

	/**
	 * @return int Entry depth (starting by 0)
	 */
	public final function getDepth() {
		return count($this->getParents());
	}

	/**
	 * @return string|null
	 */
	public final function getIcon() {
		return $this->_icon;
	}

	/**
	 * @return string Entry label
	 */
	public final function getLabel() {
		return $this->_data['label'];
	}

	/**
	 * Returns the specific page for this menu entry
	 *
	 * @return CM_Page_Abstract Page object
	 */
	public final function getPage() {
		$viewer = $this->_menu->getRequest()->getViewer();
		$viewerId = $viewer ? $viewer->getId() : 0;
		$className = $this->getPageName();

		$cacheKey = CM_CacheConst::Page . '_class:' . $className . '_userId:' . $viewerId;

		if (($page = CM_Cache_Runtime::get($cacheKey)) === false) {
			$page = new $className($this->_menu->getRequest());
			CM_Cache_Runtime::set($cacheKey, $page);
		}

		return $page;
	}

	/**
	 * @return string Returns page class name
	 */
	public final function getPageName() {
		return $this->_data['page'];
	}

	/**
	 * @return array Params list
	 */
	public final function getParams() {
		if (isset($this->_data['params'])) {
			return $this->_data['params'];
		} else {
			return array();
		}
	}

	/**
	 * @return CM_MenuEntry
	 */
	public final function getParent() {
		return $this->_parent;
	}

	/**
	 * @return CM_MenuEntry[] Parent menu entries
	 */
	public final function getParents() {
		$parents = array();
		if ($this->hasParent()) {
			$parents = $this->getParent()->getParents();
			$parents[] = $this->getParent();
		}

		return $parents;
	}

	/**
	 * Returns the url path (inclusive params)
	 *
	 * @return string Path
	 */
	public final function getPath() {
		return CM_Page_Abstract::link($this->getPage()->getPath(), $this->getParams());
	}

	/**
	 * @return CM_Menu
	 */
	public final function getSiblings() {
		return $this->_menu;
	}

	/**
	 * @return bool True if has submenu
	 */
	public final function hasChildren() {
		return !empty($this->_submenu);
	}

	/**
	 * @return bool True if has parent
	 */
	public final function hasParent() {
		return (bool) $this->_parent;
	}

	/**
	 * Checks if the given menu entry is active
	 *
	 * @return bool True if active
	 */
	public final function isActive() {
		$requestQuery = $this->_menu->getRequest()->getQuery();
		$requestPath = $this->_menu->getRequest()->getPath();

		$active = false;

		if ($this->compare($requestPath, $requestQuery)) {
			$active = true;
		} elseif ($this->_isSubmenuActive()) {
			$active = true;
		}

		return $active;
	}

	/**
	 * Checks if a menu is viewable to the current user (based on page)
	 *
	 * @return bool
	 */
	public final function isViewable() {
		return $this->getPage()->isViewable();
	}

	/**
	 * Checks if a submenu of the entry is active
	 *
	 * @return bool
	 */
	protected final function _isSubmenuActive() {
		if ($this->hasChildren()) {
			foreach ($this->getChildren()->getAllEntries() as $entry) {
				if ($entry->isActive()) {
					return true;
				}
			}
		}

		return false;
	}
}
