<?php

abstract class CM_Page_Abstract extends CM_Renderable_Abstract {

	protected $_title = '';
	protected $_description = '';
	protected $_keywords = '';
	protected $_params;
	protected $_components = array();
	protected $_viewer = null;
	protected $_path = null;

	/**
	 * @param CM_Request_Abstract $request
	 */
	public function __construct(CM_Request_Abstract $request) {
		$params = $request->getQuery();
		$this->_request = $request;
		$this->_params = CM_Params::factory($params);
		$this->setViewer($request->getViewer());
	}

	/**
	 * @param CM_Response_Abstract $response
	 * @throws CM_Exception_Nonexistent
	 */
	abstract public function prepare(CM_Response_Abstract $response);

	/**
	 * Adds a component to the page
	 *
	 * @param CM_Component_Abstract $component
	 * @param int                   $location  OPTIONAL Location to add the component (default = 1)
	 */
	public final function addComponent(CM_Component_Abstract $component, $location = 1) {
		if (!isset($this->_components[$location])) {
			$this->_components[$location] = array();
		}
		$this->_components[$location][] = $component;
	}

	/**
	 * Returns an array with all components assigned to this page
	 *
	 * Sorted in subarray defined by location
	 *
	 * @param int $index
	 * @return array Components list
	 */
	public final function getComponents($index = 1) {
		if (!isset($this->_components[$index])) {
			return array();
		}
		return $this->_components[$index];
	}

	/**
	 * Overwrites complete components array including location
	 *
	 * Example: array(1 => array('Component1', 'Component2'), 2 => array('Component3'))
	 *
	 * @param array $components Components array
	 * @return CM_Page_Abstract
	 */
	public final function setComponents(array $components) {
		$this->_components = $components;
		return $this;
	}

	/**
	 * @return string Description
	 */
	public final function getDescription() {
		return $this->_description;
	}

	/**
	 * @param string $description
	 * @return CM_Page_Abstract
	 */
	public final function setDescription($description) {
		$this->_description = $description;
		return $this;
	}

	/**
	 * @return string keywords
	 */
	public final function getKeywords() {
		return $this->_keywords;
	}

	/**
	 * @param string $keywords Keywords
	 * @return CM_Page_Abstract
	 */
	public final function setKeywords($keywords) {
		$this->_keywords = $keywords;
		return $this;
	}

	/**
	 * @return CM_Params
	 */
	public final function getParams() {
		return $this->_params;
	}

	/**
	 * Returns the page path based on the class name.
	 *
	 * Removes all in front of Page_ including Page.
	 *
	 * @return string Page path
	 */
	public final function getPath() {
		// Caches path locally because stays the same
		if (!$this->_path) {

			$class = get_class($this);
			$list = explode('_', get_class($this));

			// Remove first parts
			foreach ($list as $index => $entry) {
				unset($list[$index]);

				if ($entry == 'Page') {
					break;
				}
			}

			// Converts upper case letters to dashes: CodeOfHonor => code-of-honor
			foreach ($list as $index => $entry) {
				$entry = lcfirst($entry);
				$list[$index] = preg_replace('/([A-Z])/', '-\1', $entry);
			}

			$this->_path = strtolower('/' . implode('/', $list));
		}

		return $this->_path;
	}

	/**
	 * @return CM_Request_Abstract
	 */
	public function getRequest() {
		return $this->_request;
	}

	/**
	 * Returns the translated title
	 *
	 * @return string Page title
	 */
	public final function getTitle() {
		return $this->_title;
	}

	/**
	 * @param string $title
	 * @return CM_Page_Abstract
	 */
	public final function setTitle($title) {
		$this->_title = $title;
		return $this;
	}

	/**
	 * @param bool $needed OPTIONAL Throw an CM_Exception_AuthRequired if not authenticated
	 * @return CM_Model_User|null
	 * @throws CM_Exception_AuthRequired
	 */
	public final function getViewer($needed = false) {
		if (!$this->_viewer) {
			if ($needed) {
				throw new CM_Exception_AuthRequired();
			}
			return null;
		}
		return $this->_viewer;
	}

	/**
	 * Sets the viewer of this page
	 *
	 * @param CM_Model_User $viewer
	 * @return CM_Page_Abstract Page object
	 */
	public final function setViewer(CM_Model_User $viewer = null) {
		$this->_viewer = $viewer;
		return $this;
	}

	/**
	 * Checks if the page is viewable by the current user
	 *
	 * @return bool True if page is visible
	 */
	public function isViewable() {
		return true;
	}

	/**
	 * Resets the component list
	 *
	 * @return CM_Page_Abstract
	 */
	public final function resetComponents() {
		$this->_components = array();
		return $this;
	}

	/**
	 * Creates a new page based on the given path (including params)
	 *
	 * @param CM_Site_Abstract $site
	 * @param CM_Request_Abstract $request
	 * @return CM_Page_Abstract
	 * @throws CM_Exception_Nonexistent
	 */
	public static final function factory(CM_Site_Abstract $site, CM_Request_Abstract $request) {
		$path = $request->getPath();

		$pathTokens = explode('/', $path);
		array_shift($pathTokens);

		// Rewrites code-of-honor to CodeOfHonor
		foreach ($pathTokens as &$pathToken) {
			$pathToken = preg_replace('/-([a-z])/e', 'strtoupper("$1")', ucfirst($pathToken));
		}

		$className = $site->getNamespace() . '_Page_' . implode('_', $pathTokens);
		if (!class_exists($className) || !is_subclass_of($className, __CLASS__)) {
			throw new CM_Exception_Nonexistent('Cannot load page `' . $className . '`');
		}

		return new $className($request);
	}

	/**
	 * @param string  $path
	 * @param array   $params Query parameters
	 * @param boolean $absolute
	 * @return string
	 */
	public static final function link($path, array $params = null, $absolute = false) {
		if ($absolute) {
			$path = substr(URL_ROOT, 0, -1) . $path;
		}
		$link = $path;

		if ($params) {
			$params = CM_Params::encode($params);
			$query = http_build_query($params);
			$link .= '?' . $query;
		}

		return $link;
	}

	/**
	 * @param string $path
	 * @param array  $params Query parameters
	 * @return string
	 */
	public static final function LinkAbs($path, array $params = null) {
		return self::link($path, $params, true);
	}

}
