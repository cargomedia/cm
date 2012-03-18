<?php

class CM_RenderAdapter_Component extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		$parentComponentId = null;
		if (isset($params['parentId'])) {
			$parentComponentId = $params['parentId'];
		} elseif (count($this->getRender()->getStack('components'))) {
			$parentComponentId = $this->getRender()->getStackLast('components')->getAutoId();
		} elseif (count($this->getRender()->getStack('pages'))) {
			$parentComponentId = $this->getRender()->getStackLast('pages')->getAutoId();
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
