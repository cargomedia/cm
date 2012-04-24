<?php

abstract class CM_Page_Abstract extends CM_View_Abstract {

	protected $_title = null;
	protected $_params;
	protected $_viewer = null;
	protected $_path = null;

	/**
	 * @param CM_Request_Abstract $request
	 */
	public function __construct(CM_Request_Abstract $request) {
		$params = $request->getQuery();
		$this->_request = $request;
		$this->_params = CM_Params::factory($params);
		$this->_viewer = $request->getViewer();
	}

	/**
	 * @param CM_Response_Abstract $response
	 * @throws CM_Exception_Nonexistent
	 */
	abstract public function prepare(CM_Response_Abstract $response);

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
			$this->_path = '/' . self::getPathByClassName(get_class($this));
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
	 * @return string|null
	 */
	public final function getTitle() {
		return $this->_title;
	}

	/**
	 * @param string $title
	 */
	public final function setTitle($title) {
		$this->_title = (string) $title;
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
	 * Checks if the page is viewable by the current user
	 *
	 * @return bool True if page is visible
	 */
	public function isViewable() {
		return true;
	}

	/**
	 * Creates a new page based on the given path (including params)
	 *
	 * @param CM_Site_Abstract    $site
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
	 * @param array|null $params
	 * @return string
	 */
	public static function getPath2(array &$params = null) {
		return static::getPathByClassName(get_called_class());
	}

	/**
	 * @param string $pageClassName
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	public static function getPathByClassName($pageClassName) {
		if (!class_exists($pageClassName) || !is_subclass_of($pageClassName, 'CM_Page_Abstract')) {
			throw new CM_Exception_Invalid('Cannot find valid class definition for page `' . $pageClassName . '`.');
		}
		$list = explode('_', $pageClassName);

		// Remove first parts
		foreach ($list as $index => $entry) {
			unset($list[$index]);
			if ($entry == 'Page') {
				break;
			}
		}

		// Converts upper case letters to dashes: CodeOfHonor => code-of-honor
		foreach ($list as $index => $entry) {
			$list[$index] = preg_replace('/([A-Z])/', '-\1', lcfirst($entry));
		}

		$path = strtolower(implode('/', $list));
		if ($path == 'index') {
			$path = '';
		}
		return $path;
	}



	/**
	 * @param string  $path
	 * @param array   $params Query parameters
	 * @return string
	 */
	public static final function link($path, array $params = null) {
		$link = $path;

		if (!empty($params)) {
			$params = CM_Params::encode($params);
			$query = http_build_query($params);
			$link .= '?' . $query;
		}

		return $link;
	}
}
