<?php

class CM_RenderAdapter_Component extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		$components = $this->getRender()->getStack('components');
		$parentComponentId = null;

		if (!isset($params['parentId']) && !empty($components)) {
			$parentComponentId = $this->getRender()->getStackLast('components')->getAutoId();
		} elseif (isset($params['parentId'])) {
			$parentComponentId = $params['parentId'];
		}

		/** @var CM_Component_Abstract $component */
		$component = $this->_getView();

		$this->getRender()->pushStack('components', $component);

		$this->getTemplate()->assign($component->getTplParams());
		$this->getTemplate()->assign('viewer', $component->getViewer());

		$tplPath = $this->_getTplPath($component->getTplName());

		$cssClass = implode(' ', $component->getClassHierarchy());
		if (preg_match('#/([^/]+)\.tpl$#', $tplPath, $match)) {
			if ($match[1] != 'default') {
				$cssClass .= ' ' . $match[1]; // Include special-tpl name in class (e.g. 'mini')
			}
		}

		$html = '<div id="' . $component->getAutoId() . '" class="' . $cssClass . '">';
		$html .= $this->getRender()->getLayout()->fetch($tplPath);
		$html .= '</div>';

		$this->getRender()->getJs()->registerComponent($component, $parentComponentId);
		$this->getRender()->popStack('components');

		return $html;
	}
}
