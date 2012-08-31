<?php

abstract class CM_Page_Abstract extends CM_View_Abstract {

	/**
	 * @var CM_Params
	 */
	protected $_params;

	/**
	 * @var CM_Model_User|null
	 */
	protected $_viewer = null;

	/**
	 * @param CM_Params          $params
	 * @param CM_Model_User|null $viewer
	 */
	public function __construct(CM_Params $params, CM_Model_User $viewer = null) {
		$this->_params = $params;
		$this->_viewer = $viewer;
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
			$pathToken = CM_Util::camelize($pathToken);
		}

		$className = $site->getNamespace() . '_Page_' . implode('_', $pathTokens);
		if (!class_exists($className) || !is_subclass_of($className, __CLASS__)) {
			throw new CM_Exception_Nonexistent('Cannot load page `' . $className . '`');
		}

		return new $className(CM_Params::factory($request->getQuery()), $request->getViewer());
	}

	/**
	 * @param array|null $params
	 * @return string
	 */
	public static function getPath(array $params = null) {
		$pageClassName = get_called_class();
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

		$path = '/' . strtolower(implode('/', $list));
		if ($path == '/index') {
			$path = '/';
		}
		return CM_Util::link($path, $params);
	}
}
