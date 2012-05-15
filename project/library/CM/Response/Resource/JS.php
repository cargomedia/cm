<?php

class CM_Response_Resource_JS extends CM_Response_Resource_Abstract {

	public function process() {
		$this->setHeader('Content-Type', 'application/x-javascript');
		$this->enableCache();

		if ($this->_getFilename() == 'internal.js') {
			$content = '';

			foreach (array_reverse(self::getSite()->getNamespaces()) as $namespace) {
				$path = DIR_PUBLIC . 'static/js/' . $namespace . '.js';
				if (is_file($path)) {
					$content .= new CM_File($path) . ';' . PHP_EOL;
				}
			}

			$modelTypes = CM_Config::get()->CM_Model_Abstract->types;
			if (is_array($modelTypes)) {
				$content .= 'cm.model.types = ' . CM_Params::encode(array_flip($modelTypes), true) . ';' . PHP_EOL;
			}

			$viewPaths = array();
			foreach ($this->getSite()->getNamespaces() as $namespace) {
				$viewPaths = array_merge($viewPaths, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/View/'));
				$viewPaths = array_merge($viewPaths, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/Component/'));
				$viewPaths = array_merge($viewPaths, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/FormField/'));
				$viewPaths = array_merge($viewPaths, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/Form/'));
			}

			foreach ($this->_getClasses($viewPaths) as $viewClass) {
				$jsPath = preg_replace('/\.php$/', '.js', $viewClass['path']);
				$properties = file_exists($jsPath) ? new CM_File($jsPath) : null;
				$content .= $this->_printClass($viewClass['path'], $viewClass['classNames'], $properties);
			}
		} elseif ($this->_getFilename() == 'init.js') {
			$content = '';
			foreach (CM_Util::rglob('*.js', DIR_PUBLIC . 'static/js/init/') as $path) {
				$content .= new CM_File($path) . ';' . PHP_EOL;
			}
		} elseif ($this->_getFilename() == 'library.js') {
			$content = '';
			foreach (CM_Util::rglob('*.js', DIR_PUBLIC . 'static/js/library/') as $path) {
				$content .= new CM_File($path) . ';' . PHP_EOL;
			}
		} elseif (file_exists(DIR_PUBLIC . 'static/js/' . $this->_getFilename())) {
			$content = new CM_File(DIR_PUBLIC . 'static/js/' . $this->_getFilename());
		} else {
			throw new CM_Exception_Invalid('Invalid filename: `' . $this->_getFilename() . '`');
		}
		return $content;
	}

	/**
	 * @param string   $path
	 * @param string[] $classNames
	 * @param string   $properties JSON
	 * @return string
	 */
	private function _printClass($path, array $classNames, $properties = null) {
		$parentClass = isset($classNames[1]) ? $classNames[1] : 'Backbone.View';
		$str = 'var ' . $classNames[0] . ' = ' . $parentClass . '.extend({';
		$str .= '_class:"' . $classNames[0] . '"';
		if (!empty($properties)) {
			$str .= ',' . PHP_EOL . trim($properties) . PHP_EOL;
		}
		$str .= '});' . PHP_EOL;
		return $str;
	}
}
