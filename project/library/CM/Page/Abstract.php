<?php

abstract class CM_Page_Abstract extends CM_View_Abstract {

	protected $_title = null;
	protected $_description = '';
	protected $_keywords = '';
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
	 * @return string Description
	 */
	public final function getDescription() {
		return $this->_description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->_description = (string) $description;
	}

	/**
	 * @return string keywords
	 */
	public final function getKeywords() {
		return $this->_keywords;
	}

	/**
	 * @param string $keywords
	 */
	public function setKeywords($keywords) {
		$this->_keywords = (string) $keywords;
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
