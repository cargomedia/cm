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

	public function ajax_loadComponent(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_View_Ajax $response) {
		return $response->loadComponent($params);
	}

	public function ajax_loadPage(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_View_Ajax $response) {
		return $response->loadPage($params, $response);
	}

	/**
	 * @param CM_Model_User $user
	 * @param string        $event
	 * @param mixed|null         $data
	 */
	public static function stream(CM_Model_User $user, $event, $data = null) {
		$namespace = get_called_class() . ':' . $event;
		CM_Model_StreamChannel_Message_User::publish($user, $namespace, $data);
	}
}
