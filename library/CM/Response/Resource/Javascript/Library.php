<?php

class CM_Response_Resource_Javascript_Library extends CM_Response_Resource_Javascript_Abstract {

	protected function _process() {
		if ($this->getRequest()->getPath() === '/library.js') {
			$content = '';
			$query = $this->getRequest()->getQuery();
			if (empty($query['debug'])) {
				$paths = self::getIncludedPaths($this->getSite());
				foreach ($paths as $path) {
					$content .= new CM_File($path);
				}
			}
			$content .= 'var app = new ' . $this->_getAppClassName() . '(), cm = app;' . PHP_EOL;
			$content .= new CM_File(DIR_ROOT . 'resources/config/js/internal.js');
			$this->_setContent($content);
			return;
		}
		if ($this->getRequest()->getPathPart(0) === 'translations') {
			$language = $this->getRender()->getLanguage();
			if (!$language) {
				throw new CM_Exception_Invalid('Render has no language');
			}
			$translations = array();
			foreach (new CM_Paging_Translation_Language($language, null, null, null, true) as $translation) {
				$translations[$translation['key']] = $language->getTranslation($translation['key']);
			}
			$this->_setContent('cm.language.setAll(' . CM_Params::encode($translations, true) . ');');
			return;
		}
		throw new CM_Exception_Invalid('Invalid path `' . $this->getRequest()->getPath() . '` provided');
	}

	/**
	 * @throws CM_Exception_Invalid
	 * @return string
	 */
	private function _getAppClassName() {
		foreach ($this->getSite()->getNamespaces() as $namespace) {
			$appClassFilename = DIR_ROOT . CM_Bootloader::getInstance()->getNamespacePath($namespace) . 'library/' . $namespace . '/App.js';
			if (file_exists($appClassFilename)) {
				return $namespace . '_App';
			}
		}
		throw new CM_Exception_Invalid('No App class found');
	}

	/**
	 * @param CM_Site_Abstract $site
	 * @return array
	 */
	public static function getIncludedPaths(CM_Site_Abstract $site) {
		$pathsUnsorted = CM_Util::rglobLibraries('*.js', $site);
		$paths = array_keys(CM_Util::getClasses($pathsUnsorted));

		// TODO: Move namespace libraries out of here
		foreach (array_reverse($site->getNamespaces()) as $namespace) {
			if (is_file($path = DIR_PUBLIC . 'static/js/' . $namespace . '.js')) {
				$paths[] = $path;
			}
		}
		return $paths;
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'library-js';
	}
}
