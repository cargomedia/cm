<?php

class CM_Response_Resource_JS extends CM_Response_Resource_Abstract {

	public function process() {
		$this->setHeader('Content-Type', 'application/x-javascript');
		$this->enableCache();

		if ($this->_getPath() == 'internal.js') {
			return $this->_getInternal();
		}
		if ($this->_getPath() == 'init.js') {
			$content = '';
			foreach (CM_Util::rglob('*.js', DIR_PUBLIC . 'static/js/init/') as $path) {
				$content .= new CM_File($path) . ';' . PHP_EOL;
			}
			return $content;
		}
		if ($this->_getPath() == 'library.js') {
			$content = '';
			foreach (CM_Util::rglob('*.js', DIR_PUBLIC . 'static/js/library/') as $path) {
				$content .= new CM_File($path) . ';' . PHP_EOL;
			}
			return $content;
		}
		if ($this->_getPath(0) == 'translations') {
			$language = new CM_Model_Language($this->_getPath(2));
			$translations = array();
			foreach (new CM_Paging_Translation_Language($language, null, null, null, true) as $translation) {
				$translations[$translation['key']] = $language->getTranslation($translation['key']);
			}
			return 'cm.language.setAll(' . CM_Params::encode($translations, true) . ');';
		}
		if (file_exists(DIR_PUBLIC . 'static/js/' . $this->_getPath())) {
			return (string) new CM_File(DIR_PUBLIC . 'static/js/' . $this->_getPath());
		}
		throw new CM_Exception_Invalid('Invalid filename: `' . $this->_getPath() . '`');
	}

	/**
	 * @return string
	 */
	private function _getInternal() {
		$paths = array();
		foreach (array_reverse(self::getSite()->getNamespaces()) as $namespace) {
			if (is_file($path = DIR_PUBLIC . 'static/js/' . $namespace . '.js')) {
				$paths[] = $path;
			}
		}
		$paths[] = DIR_ROOT . 'config/js/internal.js';
		foreach (CM_View_Abstract::getClasses($this->getSite()->getNamespaces(), CM_View_Abstract::CONTEXT_JAVASCRIPT) as $path => $className) {
			$paths[] = preg_replace('/\.php$/', '.js', $path);
		}

		$content = '';
		foreach ($paths as $path) {
			$content .= new CM_File($path);
		}
		return $content;
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'js';
	}
}
