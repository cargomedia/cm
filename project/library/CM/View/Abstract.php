<?php

abstract class CM_View_Abstract extends CM_Class_Abstract {

	const CONTEXT_ALL = 1;
	const CONTEXT_JAVASCRIPT = 2;
	const CONTEXT_CSS = 3;

	private $_autoId;

	/**
	 * @var array
	 */
	protected $_tplParams = array();

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return CM_Component_Abstract
	 */
	public function setTplParam($key, $value) {
		$this->_tplParams[$key] = $value;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getTplParams() {
		return $this->_tplParams;
	}

	/**
	 * @return string
	 */
	public function getAutoId() {
		if (!$this->_autoId) {
			$this->_autoId = uniqid();
		}
		return $this->_autoId;
	}

	public static function ajax_loadComponent(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_View_Ajax $response) {
		return $response->loadComponent($params);
	}

	public static function ajax_loadPage(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_View_Ajax $response) {
		return $response->loadPage($params);
	}

	/**
	 * @param string[] $namespaces
	 * @param int      $context
	 * @throws CM_Exception_Invalid
	 * @return array[]
	 */
	public static function getClasses(array $namespaces, $context) {
		$contextTypes = array(
			self::CONTEXT_ALL        => array('View', 'Page', 'Component', 'Form', 'FormField'),
			self::CONTEXT_JAVASCRIPT => array('View', 'Page', 'Component', 'Form', 'FormField'),
			self::CONTEXT_CSS        => array('Layout', 'Page', 'Component', 'FormField'),
		);
		if (!array_key_exists($context, $contextTypes)) {
			throw new CM_Exception_Invalid('Context needs to be one of: `CONTEXT_ALL`, `CONTEXT_JAVASCRIPT`, `CONTEXT_CSS`');
		}
		$paths = array();
		foreach ($namespaces as $namespace) {
			foreach ($contextTypes[$context] as $contextType) {
				$paths = array_merge($paths, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/' . $contextType . '/'));
			}
		}
		return CM_Util::getClasses($paths);
	}
}
