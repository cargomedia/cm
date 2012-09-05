<?php

abstract class CM_Component_Abstract extends CM_View_Abstract {
	/**
	 * @var CM_Model_User|null
	 */
	protected $_viewer;

	/**
	 * @var string
	 */
	protected $_tplName = 'default.tpl';

	/**
	 * @var CM_ComponentFrontendHandler
	 */
	protected $_js = null;

	/**
	 * @var CM_Params
	 */
	protected $_params;

	/**
	 * @param CM_Params|array|null $params
	 * @param CM_Model_User|null   $viewer
	 */
	public function __construct($params = null, CM_Model_User $viewer = null) {
		$this->_viewer = $viewer;
		if (is_null($params)) {
			$params = CM_Params::factory();
		}
		if (is_array($params)) {
			$params = CM_Params::factory($params, true);
		}
		$this->_params = $params;
		$this->_js = new CM_ComponentFrontendHandler();

		if ($this->_params->has('template')) {
			$this->setTplName($this->_params->getString('template'));
		}
	}

	/**
	 * Checks if a component can be accessed by the currently set user
	 *
	 * Access for everyone is default. Should be overloaded by every component
	 *
	 * @throws CM_Exception_AuthRequired
	 * @throws CM_Exception_Nonexistent
	 */
	abstract public function checkAccessible();

	/**
	 * @return CM_ComponentFrontendHandler
	 */
	public function getFrontendHandler() {
		return $this->_js;
	}

	/**
	 * @return CM_Params
	 */
	public function getParams() {
		return $this->_params;
	}

	public function prepare() {
	}

	/**
	 * @return string
	 */
	public function getTplName() {
		return $this->_tplName;
	}

	/**
	 * @param string $filename
	 * @throws CM_Exception_Invalid
	 */
	public function setTplName($filename) {
		$filename = (string) $filename . '.tpl';
		if (preg_match('/[^\w\.-]/', $filename)) {
			throw new CM_Exception_Invalid('Invalid tpl-name `' . $filename . '`');
		}
		$this->_tplName = $filename;
	}

	/**
	 * Get auto id prefixed id value for an html element.
	 *
	 * @param string $id_value
	 * @return string
	 */
	final public function getTagAutoId($id_value) {
		return $this->getAutoId() . '-' . $id_value;
	}

	/**
	 * Checks if a user is set on the component
	 *
	 * @throws CM_Exception_AuthRequired If no user is set
	 */
	protected function _checkViewer() {
		$this->_getViewer(true);
	}

	/**
	 * @param boolean $needed OPTIONAL Throw a CM_Exception_AuthRequired if not authenticated
	 * @return CM_Model_User|null
	 * @throws CM_Exception_AuthRequired
	 */
	protected function _getViewer($needed = false) {
		if (!$this->_viewer) {
			if ($needed) {
				throw new CM_Exception_AuthRequired();
			}
			return null;
		}
		return $this->_viewer;
	}

	public static function ajax_reload(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_View_Ajax $response) {
		return $response->reloadComponent($params->getAll());
	}

	/**
	 * @param CM_Model_User $user
	 * @param string        $event
	 * @param mixed         $data
	 */
	public static function stream(CM_Model_User $user, $event, $data) {
		$namespace = get_called_class() . ':' . $event;
		CM_Stream::publishUser($user, array('namespace' => $namespace, 'data' => $data));
	}

	/**
	 * @param string             $className
	 * @param CM_Params|array    $params
	 * @param CM_Model_User|null $viewer
	 * @return CM_Component_Abstract
	 * @throws CM_Exception
	 */
	public static function factory($className, $params = null, CM_Model_User $viewer = null) {
		if (!class_exists($className) || !is_subclass_of($className, __CLASS__)) {
			throw new CM_Exception('Cannot find valid class definition for component `' . $className . '`.');
		}
		$component = new $className($params, $viewer);
		return $component;
	}
}
