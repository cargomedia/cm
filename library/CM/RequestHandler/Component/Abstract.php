<?php

abstract class CM_RequestHandler_Component_Abstract extends CM_RequestHandler_Abstract {
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
			throw new CM_Exception_Invalid('Component param not set (query: `' . var_line($query) . '`)');
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

		$render = $this->getRender();
		// Render new component
		$html = $render->render($component, array('parent' => $this->_component['parentId']));

		$render->getJs()->onloadHeaderJs(
			'sk.components["' . $this->_component['id'] . '"].$().replaceWith(' . json_encode($html) . ');');
		$render->getJs()->onloadPrepareJs(
			'sk.components["' . $component->auto_id . '"]._callbacks=sk.components["' . $this->_component['id'] .
					'"]._callbacks;');

		// Delete old component
		$render->getJs()->onloadPrepareJs($this->annulCOMNode($this->_component));

		$render->getJs()->onloadReadyJs('sk.components["' . $component->auto_id . '"]._ready();');

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

		$html = $this->getRender()->render($component, array('parent' => $this->_component['id']));

		$this->getRender()->getJs()->onloadHeaderJs('sk.window.appendHidden(' . json_encode($html) . ');');

		return $component->auto_id;
	}

	/**
	 * @param int $width OPTIONAL
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
		$this->getRender()->getJs()->onloadJs('sk.components[\'' . $this->_component['id'] . '\'].popOut(' . json_encode($params) . ');');
	}

	public function popinComponent() {
		$this->getRender()->getJs()->onloadJs('sk.components[\'' . $this->_component['id'] . '\'].popIn();');
	}

	public function redirect($url) {
		$this->getRender()->getJs()->onloadPrepareJs('window.location.href = ' . json_encode($url));
	}

	/**
	 * @param array $comNode
	 * @return string
	 */
	public function annulCOMNode(array $comNode) {
		$js = '';
		if ($comNode['parentId']) {
			$js .= 'var children = sk.components["' . $comNode['parentId'] . '"].getChildren();';
			$js .= 'for (var i = 0, child; child = children[i]; i++) {';
			$js .= '	if (child.getAutoId() == "' . $comNode['id'] . '") {';
			$js .= '  children.splice(i,1);';
			$js .= '	}';
			$js .= '}' . PHP_EOL;
		}
		foreach ($comNode['children'] as $child) {
			$js .= $this->annulCOMNode($child) . PHP_EOL;
		}
		foreach ($comNode['forms'] as $form) {
			$js .= 'delete sk.forms["' . $form['id'] . '"];' . PHP_EOL;
		}
		$js .= 'sk.components["' . $comNode['id'] . '"].trigger("destruct");';
		$js .= 'delete sk.components["' . $comNode['id'] . '"]' . PHP_EOL;
		return $js;
	}
}
