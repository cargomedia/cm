<?php

class CM_Response_Resource_Javascript_Library extends CM_Response_Resource_Javascript_Abstract {

	protected function _process() {
		if ($this->getRequest()->getPath() === '/library.js') {
			$paths = self::getIncludedPaths($this->getSite());

			// TODO: Remove internal.js from here (autogenerate it)
			$paths[] = DIR_ROOT . 'resources/config/js/internal.js';
			$content = '';
			foreach ($paths as $path) {
				$content .= new CM_File($path);
			}
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
