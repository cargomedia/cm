<?php

class CM_Response_Resource_JS extends CM_Response_Resource_Abstract {

	protected function _process() {
		if ($this->getRequest()->getPath() == '/internal.js') {
			$content = $this->_processInternal($this->_getInternal());
		} elseif ($this->getRequest()->getPath() == '/init.js') {
			$content = '';
			foreach (CM_Util::rglob('*.js', DIR_PUBLIC . 'static/js/init/') as $path) {
				$content .= new CM_File($path) . ';' . PHP_EOL;
			}
			$content = $this->_processInternal($content);
		} elseif ($this->getRequest()->getPath() == '/library.js') {
			$content = '';
			foreach (CM_Util::rglob('*.js', DIR_PUBLIC . 'static/js/library/') as $path) {
				$content .= new CM_File($path) . ';' . PHP_EOL;
			}
			$content = $this->_processInternal($content);
		} elseif ($this->getRequest()->getPathPart(0) == 'translations') {
			$language = $this->getRender()->getLanguage();
			if (!$language) {
				throw new CM_Exception_Invalid('Render has no language');
			}
			$translations = array();
			foreach (new CM_Paging_Translation_Language($language, null, null, null, true) as $translation) {
				$translations[$translation['key']] = $language->getTranslation($translation['key']);
			}
			$content = 'cm.language.setAll(' . CM_Params::encode($translations, true) . ');';
		} elseif (file_exists(DIR_PUBLIC . 'static/js/' . $this->getRequest()->getPath())) {
			$content = (string) new CM_File(DIR_PUBLIC . 'static/js/' . $this->getRequest()->getPath());
		} else {
			throw new CM_Exception_Invalid('Invalid filename: `' . $this->getRequest()->getPath() . '`');
		}

		$this->enableCache();
		$this->setHeader('Content-Type', 'application/x-javascript');
		$this->_setContent($content);
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

	/**
	 * @param string $content
	 * @return string
	 */
	private function _processInternal($content) {
		if (!$this->getRender()->isDebug()) {
			$md5 = md5($content);
			$cacheKey = CM_CacheConst::Response_Resource_JS . '_md5:' . $md5;

			if (false === ($contentMinified = CM_CacheLocal::get($cacheKey))) {
				$lock = new CM_Lock($cacheKey);
				$lock->waitUntilUnlocked();

				if (false === ($contentMinified = CM_CacheLocal::get($cacheKey))) {
					$lock->lock();
					$contentMinified = CM_Util::exec('uglifyjs --no-copyright', null, $content);
					CM_CacheLocal::set($cacheKey, $contentMinified);
					$lock->unlock();
				}
			}

			$content = $contentMinified;
		}
		return $content;
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'js';
	}
}
