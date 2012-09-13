<?php

abstract class CM_Response_View_Abstract extends CM_Response_Abstract {

	/**
	 * @param string|null $key
	 * @return array
	 * @throws CM_Exception_Invalid
	 */
	protected function _getViewInfo($key = null) {
		if (null === $key) {
			$key = 'view';
		}
		$query = $this->_request->getQuery();
		if (!isset($query[$key])) {
			throw new CM_Exception_Invalid('View `' . $key . '` not set.');
		}
		$viewInfo = $query[$key];
		if (!is_array($viewInfo)) {
			throw new CM_Exception_Invalid('View `' . $key . '` is not an array');
		}
		if (!isset($viewInfo['id'])) {
			throw new CM_Exception_Invalid('View id `' . $key . '` not set.');
		}
		if (!isset($viewInfo['className']) || !class_exists($viewInfo['className']) ||
				!('CM_View_Abstract' == $viewInfo['className'] || is_subclass_of($viewInfo['className'], 'CM_View_Abstract'))
		) {
			throw new CM_Exception_Invalid('View className `' . $key . '` is illegal: `' . $viewInfo['className'] . '`.');
		}
		if (!isset($viewInfo['params'])) {
			throw new CM_Exception_Invalid('View params `' . $key . '` not set.');
		}
		if (!isset($viewInfo['parentId'])) {
			$viewInfo['parentId'] = null;
		}
		return array('id' => (string) $viewInfo['id'], 'className' => (string) $viewInfo['className'], 'params' => (array) $viewInfo['params'],
			'parentId' => (string) $viewInfo['parentId']);
	}

	/**
	 * @param array $params OPTIONAL
	 * @return string Auto-id
	 */
	public function reloadComponent($params = null) {
		$componentInfo = $this->_getViewInfo();
		$componentParams = $componentInfo['params'];
		if ($params) {
			$componentParams = array_merge($componentParams, $params);
		}
		$componentParams = CM_Params::factory($componentParams);

		$component = CM_Component_Abstract::factory($componentInfo['className'], $componentParams, $this->getViewer());
		$component->checkAccessible();
		$component->prepare();

		$html = $this->getRender()->render($component, array('parentId' => $componentInfo['parentId']));

		$this->getRender()->getJs()->onloadHeaderJs('cm.views["' . $componentInfo['id'] . '"].$().replaceWith(' . json_encode($html) . ');');
		$this->getRender()->getJs()->onloadPrepareJs(
			'cm.views["' . $component->getAutoId() . '"]._callbacks=cm.views["' . $componentInfo['id'] . '"]._callbacks;');
		$this->getRender()->getJs()->onloadPrepareJs('cm.views["' . $componentInfo['id'] . '"].remove(true);');
		$this->getRender()->getJs()->onloadReadyJs('cm.views["' . $component->getAutoId() . '"]._ready();');
		$componentInfo['id'] = $component->getAutoId();

		return $component->getAutoId();
	}

	/**
	 * @param CM_Params $params
	 * @return string Auto-id
	 */
	public function loadComponent(CM_Params $params) {
		$viewInfo = $this->_getViewInfo();

		$component = CM_Component_Abstract::factory($params->getString('className'), $params, $this->getViewer());
		$component->checkAccessible();
		$component->prepare();

		$html = $this->getRender()->render($component, array('parentId' => $viewInfo['id']));

		$this->getRender()->getJs()->onloadHeaderJs('cm.window.appendHidden(' . json_encode($html) . ');');

		return $component->getAutoId();
	}

	/**
	 * @param CM_Params $params
	 * @return string Auto-id
	 */
	public function loadPage(CM_Params $params) {
		$layoutInfo = $this->_getViewInfo();

		$requestPage = new CM_Request_Get($params->getString('path'), null, $this->getRequest()->getViewer());
		$this->getSite()->rewrite($requestPage);
		$className = CM_Page_Abstract::getClassnameByPath($this->getSite()->getNamespace(), $requestPage->getPath());
		/** @var CM_Page_Abstract $page */
		$page = CM_Page_Abstract::factory($className, $requestPage->getQuery(), $requestPage->getViewer());

		$page->checkAccessible();
		$page->prepare();

		$html = $this->getRender()->render($page, array('parentId' => $layoutInfo['id']));

		$this->getRender()->getJs()->onloadHeaderJs('cm.window.appendHidden(' . json_encode($html) . ');');

		return $page->getAutoId();
	}

	public function popinComponent() {
		$componentInfo = $this->_getViewInfo();
		$this->getRender()->getJs()->onloadJs('cm.views["' . $componentInfo['id'] . '"].popIn();');
	}

	/**
	 * Add a reload to the response.
	 */
	public function reloadPage() {
		$this->getRender()->getJs()->onloadJs('window.location.reload(true)');
	}

	public function redirect($page, array $params = null) {
		$url = $this->getRender()->getUrlPage($page, $params);
		$this->redirectUrl($url);
	}

	public function redirectUrl($url, array $params = null) {
		$this->getRender()->getJs()->onloadPrepareJs('window.location.href = ' . json_encode($url));
	}
}
