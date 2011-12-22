<?php

class CM_RenderAdapter_Component extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		$components = $this->getRender()->getStack('components');
		$parentComponentId = null;

		if (!isset($params['parent']) && !empty($components)) {
			$parentComponentId = $this->getRender()->getStackLast('components')->auto_id;
		} elseif (isset($params['parent'])) {
			$parentComponentId = $params['parent'];
		}

		/** @var CM_Component_Abstract $component */
		$component = $this->_getObject();
		$component->auto_id = 'cmp' . uniqid();

		$this->getRender()->pushStack('components', $component);

		$this->getLayout()->assign($component->getTplParams());
		$this->getLayout()->assign('viewer', $component->getViewer());

		$tplPath = $this->_getTplPath($component->getTpl());

		$cssClass = implode(' ', $component->getClassHierarchy());
		if (preg_match('#/([^/]+)\.tpl$#', $tplPath, $match)) {
			if ($match[1] != 'default') {
				$cssClass .= ' ' . $match[1]; // Include special-tpl name in class (e.g. 'mini')
			}
		}

		$html = '<div id="' . $component->auto_id . '" class="' . $cssClass . '">';
		$html .= $this->getRender()->getLayout()->fetch($tplPath);
		$html .= '</div>';

		$this->getRender()->getJs()->registerComponent($component, $parentComponentId);
		$this->getRender()->popStack('components');

		return $html;
	}
}
