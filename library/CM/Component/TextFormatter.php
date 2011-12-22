<?php

abstract class CM_Component_TextFormatter extends CM_Component_Abstract {
	public function prepare() {
		$targetName = $this->_params->getString('targetName');
		$controls = $this->_params->getStringArray('controls', array('bold', 'italic', 'underline', 'quote', 'link', 'emoticon', 'image'));

		$sections = array();
		$sections[1] = array('rel' => '', 'label' => 'Smiley', 'active' => true);

		$emoticons = array();
		foreach ($sections as $sectionId => $section) {
			$emoticons[$sectionId] = new CM_Paging_Smiley_Section($sectionId);
		}

		$this->_js->targetName = $targetName;

		$this->setTplParam('sections', $sections);
		$this->setTplParam('emoticons', $emoticons);
		$this->setTplParam('controls', $controls);
	}
}
