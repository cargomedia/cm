<?php

class CM_RenderAdapter_Component extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		$parentViewId = null;
		if (count($this->getRender()->getStack('views'))) {
			/** @var CM_View_Abstract $parentView */
			$parentView = $this->getRender()->getStackLast('views');
			$parentViewId = $parentView->getAutoId();
		}

		/** @var CM_Component_Abstract $component */
		$component = $this->_getView();

		$this->getRender()->pushStack($this->_getStackKey(), $component);
		$this->getRender()->pushStack('views', $component);

		$cssClass = implode(' ', $component->getClassHierarchy());
		if (preg_match('#([^/]+)\.tpl$#', $component->getTplName(), $match)) {
			if ($match[1] != 'default') {
				$cssClass .= ' ' . $match[1]; // Include special-tpl name in class (e.g. 'mini')
			}
		}
		$html = '<div id="' . $component->getAutoId() . '" class="' . $cssClass . '">';

		$assign = $component->getTplParams();
		$assign['viewObj'] = $component;
		$html .= $this->_renderTemplate($component->getTplName(), $assign, true);

		$html .= '</div>';

		$this->getRender()->getJs()->registerComponent($component, $parentViewId);
		$this->getRender()->popStack($this->_getStackKey());
		$this->getRender()->popStack('views');

		return $html;
	}

	/**
	 * @param string $tplName
	 * @param array  $params
	 * @return string
	 */
	public function fetchTemplate($tplName, array $params) {
		/** @var CM_Component_Abstract $component */
		$component = $this->_getView();
		return $this->_renderTemplate($tplName, $params, true);
	}

	/**
	 * @return string
	 */
	protected function _getStackKey() {
		return 'components';
	}
}
