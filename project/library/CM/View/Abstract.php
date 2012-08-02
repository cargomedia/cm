<?php

abstract class CM_View_Abstract extends CM_Class_Abstract {
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

	/**
	 * @param string[] $namespaces
	 * @return array[]
	 */
	public static function getClasses(array $namespaces) {
		return CM_Util::getClasses(self::_getFiles($namespaces));
	}

	/**
	 * @param string[] $namespaces
	 * @return string[]
	 */
	private static function _getFiles(array $namespaces) {
		$paths = array();
		foreach ($namespaces as $namespace) {
			$paths = array_merge($paths, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/View/'));
			$paths = array_merge($paths, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/Component/'));
			$paths = array_merge($paths, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/FormField/'));
			$paths = array_merge($paths, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/Form/'));
		}
		return $paths;
	}
}
