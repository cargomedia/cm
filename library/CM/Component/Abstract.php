<?php

abstract class CM_Component_Abstract extends CM_Renderable_Abstract {
	/**
	 * @var CM_Model_User
	 */
	protected $_viewer;

	/**
	 * Component work directories namespace.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * The name of a component template file.
	 *
	 * @var string
	 */
	protected $tpl_file = 'default.tpl';

	/**
	 * Component cache id.
	 *
	 * @var string
	 */
	protected $cache_id;

	/**
	 * Component layout auto id.
	 * Set up by a CM_Layout when rendering.
	 *
	 * @var string
	 */
	public $auto_id;

	/**
	 * @var CM_ComponentFrontendHandler
	 */
	protected $_js = null;

	/**
	 * Component forms.
	 *
	 * @var array
	 */
	public $forms = array();

	/**
	 * @var CM_Params
	 */
	protected $_params;

	/**
	 * @param CM_Params|array|null $params
	 */
	public function __construct($params = null) {
		if (is_null($params)) {
			$params = CM_Params::factory();
		}
		if (is_array($params)) {
			$params = CM_Params::factory($params, true);
		}
		$this->_params = $params;
		$this->_js = new CM_ComponentFrontendHandler();
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
	 * @return string[] List of class names
	 */
	public function getClassHierarchy() {
		$classHierarchy = array_values(class_parents($this));
		array_unshift($classHierarchy, get_class($this));
		return $classHierarchy;
	}

	/**
	 * Returns the namespace of a component.
	 *
	 * @return string
	 */
	public function getNamespaceLegacy() {
		if (!$this->namespace) {
			if (!preg_match('/^\w+_Component_(.+)$/', get_class($this), $matches)) {
				throw new CM_Exception("Cannot detect namespace from component's class-name");
			}
			$namespace = lcfirst($matches[1]);
			$namespace = preg_replace('/([A-Z])/', '_\1', $namespace);
			$namespace = str_replace('__', '.', strtolower($namespace));
			$this->namespace = $namespace;
		}

		return $this->namespace;
	}

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
	 * @param bool $needed OPTIONAL Throw an CM_Exception_AuthRequired if not authenticated
	 * @return CM_Model_User|null
	 * @throws CM_Exception_AuthRequired
	 */
	public function getViewer($needed = false) {
		if (!$this->_viewer) {
			if ($needed) {
				throw new CM_Exception_AuthRequired();
			}
			return null;
		}
		return $this->_viewer;
	}

	/**
	 * @param CM_Model_User $user
	 * @return CM_Component_Abstract
	 */
	public function setViewer(CM_Model_User $user = null) {
		$this->_viewer = $user;
		return $this;
	}

	/**
	 * Checks if a user is set on the component
	 *
	 * @throws CM_Exception_AuthRequired If no user is set
	 */
	protected function _checkViewer() {
		$this->getViewer(true);
	}

	protected function _isViewer() {
		return (boolean) $this->getViewer();
	}

	/**
	 * @return string
	 */
	public function getTpl() {
		return $this->tpl_file;
	}

	/**
	 * Get auto id prefixed id value for an html element.
	 *
	 * @param string $id_value
	 * @return string
	 */
	final public function getTagAutoId($id_value) {
		return $this->auto_id . '-' . $id_value;
	}

	public static function ajax_reload(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_RequestHandler_Component_Ajax $response) {
		return $response->reloadComponent($params->getAll());
	}

	public static function ajax_load(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_RequestHandler_Component_Ajax $response) {
		return $response->loadComponent($params);
	}

	/**
	 * sends a stream to the given user's channel,
	 * preformatted with the namespace set to the corresponding component.
	 * the given data is enclosed in the associative array under the key data
	 * @param CM_Model_User $user
	 * @param mixed $data
	 */
	public static function stream(CM_Model_User $user, $data) {
		CM_Stream::publishUser($user, array('namespace' => get_called_class(), 'data' => $data));
	}

	/**
	 * @param string $className
	 * @param CM_Params|array $params
	 * @return CM_Component_Abstract
	 * @throws CM_Exception
	 */
	public static function factory($className, $params) {
		if (!class_exists($className) || !is_subclass_of($className, __CLASS__)) {
			throw new CM_Exception('Illegal component name `' . $className . '`.');
		}
		$component = new $className($params);
		return $component;
	}
}
