<?php

class CM_Response_Resource_JS extends CM_Response_Resource_Abstract {

	public function process() {
		$this->setHeader('Content-Type', 'application/x-javascript');
		$this->enableCache();

		if ($this->getRequest()->getPath() == '/internal.js') {
			return $this->_getInternal();
		}
		if ($this->getRequest()->getPath() == '/init.js') {
			$content = '';
			foreach (CM_Util::rglob('*.js', DIR_PUBLIC . 'static/js/init/') as $path) {
				$content .= new CM_File($path) . ';' . PHP_EOL;
			}
			return $content;
		}
		if ($this->getRequest()->getPath() == '/library.js') {
			$content = '';
			foreach (CM_Util::rglob('*.js', DIR_PUBLIC . 'static/js/library/') as $path) {
				$content .= new CM_File($path) . ';' . PHP_EOL;
			}
			if (!$this->getRender()->isDebug()) {
				$content = $this->minify($content);
			}
			return $content;
		}
		if ($this->getRequest()->getPathPart(0) == 'translations') {
			$language = $this->getRender()->getLanguage();
			if (!$language) {
				throw new CM_Exception_Invalid('Render has no language');
			}
			$translations = array();
			foreach (new CM_Paging_Translation_Language($language, null, null, null, true) as $translation) {
				$translations[$translation['key']] = $language->getTranslation($translation['key']);
			}
			return 'cm.language.setAll(' . CM_Params::encode($translations, true) . ');';
		}
		if (file_exists(DIR_PUBLIC . 'static/js/' . $this->getRequest()->getPath())) {
			return (string) new CM_File(DIR_PUBLIC . 'static/js/' . $this->getRequest()->getPath());
		}
		throw new CM_Exception_Invalid('Invalid filename: `' . $this->getRequest()->getPath() . '`');
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

		return $this->minify($content);
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'js';
	}

	/**
	 * @param string $content
	 * @return string
	 */
	private function minify($content) {
		return CM_Util::exec('uglifyjs --no-copyright', null, $content);
	}

}
