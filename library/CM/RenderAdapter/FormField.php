<?php

class CM_RenderAdapter_FormField extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		
		$tpl = $this->getTemplate();

		$tpl->assign($this->_getObject()->getTplParams());

		return $tpl->fetch();
	}
}
