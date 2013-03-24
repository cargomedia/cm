<?php

class CM_Response_Resource_Css_Vendor extends CM_Response_Resource_Css_Abstract {

	protected function _process() {
		switch ($this->getRequest()->getPath()) {
			case '/all.css':
				$content = '';
				foreach (CM_Util::rglob('*.css', DIR_PUBLIC . 'static/css/library/') as $path) {
					$content .= new CM_File($path);
				}

				foreach (CM_Util::rglob('*.less', DIR_PUBLIC . 'static/css/library/') as $path) {
					$css = new CM_Css(new CM_File($path));
					$content .= $css->compile($this->getRender());
				}
				$this->_setContent($content);
				break;
			default:
				throw new CM_Exception_Invalid();
		}
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'vendor-css';
	}
}
