<?php

abstract class CM_Response_Component_Abstract extends CM_Response_Abstract {
	/**
	 * Request components object model node.
	 *
	 * @var array
	 */
	protected $_component;

	/**
	 * @var Smarty
	 */
	protected $_layout;

	/**
	 * @param CM_Request_Abstract $request
	 */
	public function __construct(CM_Request_Abstract $request) {
		parent::__construct($request);
		$this->_layout = $this->getRender()->getLayout();
		$query = $this->_request->getQuery();
		if (!isset($query['component'])) {
			throw new CM_Exception_Invalid('Component param not set (query: `' . CM_Util::var_line($query) . '`)');
		}
		if (!is_array($query['component'])) {
			throw new CM_Exception_Invalid('Component param is not an array');
		}
		$this->_component = $query['component'];
	}

	/**
	 * @param array $params OPTIONAL
	 * @return string Auto-id
	 */
	public function reloadComponent($params = null) {
		$componentParams = $this->_component['params'];
		if ($params) {
			$componentParams = array_merge($componentParams, $params);
		}
		$componentParams = CM_Params::factory($componentParams);

		$component = CM_Component_Abstract::factory($this->_component['className'], $componentParams);

		$component->setViewer($this->getViewer());
		$component->checkAccessible();
		$component->prepare();
		$html = $this->getRender()->render($component, array('parentId' => $this->_component['parentId']));

		$this->getRender()->getJs()->onloadHeaderJs('cm.views["' . $this->_component['id'] . '"].$().replaceWith(' . json_encode($html) . ');');
		$this->getRender()->getJs()->onloadPrepareJs(
			'cm.views["' . $component->auto_id . '"]._callbacks=cm.views["' . $this->_component['id'] . '"]._callbacks;');
		$this->getRender()->getJs()->onloadPrepareJs('cm.views["' . $this->_component['id'] . '"].remove(true);');
		$this->getRender()->getJs()->onloadReadyJs('cm.views["' . $component->auto_id . '"]._ready();');
		$this->_component['id'] = $component->auto_id;

		return $component->auto_id;
	}

	/**
	 * @param CM_Params $params
	 * @return string Auto-id
	 */
	public function loadComponent(CM_Params $params) {
		$component = CM_Component_Abstract::factory($params->getString('component'), $params);
		$component->setViewer($this->getViewer());
		$component->checkAccessible();
		$component->prepare();

		$html = $this->getRender()->render($component, array('parentId' => $this->_component['id']));

		$this->getRender()->getJs()->onloadHeaderJs('cm.window.appendHidden(' . json_encode($html) . ');');

		return $component->auto_id;
	}

	/**
	 * @param int  $width	OPTIONAL
	 * @param bool $closable OPTIONAL
	 */
	public function popoutComponent($width = null, $closable = null) {
		$params = array();
		if (null !== $width) {
			$params['width'] = (int) $width;
		}
		if (null !== $closable) {
			$params['closable'] = (bool) $closable;
		}
		$this->getRender()->getJs()->onloadJs('cm.views["' . $this->_component['id'] . '"].popOut(' . json_encode($params) . ');');
	}

	public function popinComponent() {
		$this->getRender()->getJs()->onloadJs('cm.views["' . $this->_component['id'] . '"].popIn();');
	}

	public function redirect($path, array $params = null) {
		$url = CM_Page_Abstract::link($path, $params);
		$this->getRender()->getJs()->onloadPrepareJs('window.location.href = ' . json_encode($url));
	}
}
