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

			$classes = array();
			foreach (self::getSite()->getNamespaces() as $namespace) {
				$classes = array_merge($classes, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/View/'));
				$classes = array_merge($classes, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/Component/'));
				$classes = array_merge($classes, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/FormField/'));
				$classes = array_merge($classes, CM_Util::rglob('*.php', DIR_LIBRARY . $namespace . '/Form/'));
			}

			foreach ($this->_getClasses($classes) as $class) {
				$jsPath = preg_replace('/\.php$/', '.js', $class['path']);
				$properties = file_exists($jsPath) ? new CM_File($jsPath) : null;
				$content .= $this->_printClass($class['name'], $class['parent'], $properties);
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
	 * @param string $name
	 * @param string $parentName
	 * @param string $properties JSON
	 * @return string
	 */
	private function _printClass($name, $parentName, $properties = null) {
		$str = 'var ' . $name . ' = ' . $parentName . '.extend({';
		$str .= '_class:"' . $name . '"';
		//$str .= ',__super__:' . $parentName . '.prototype';
		if (!empty($properties)) {
			$str .= ',' . PHP_EOL . trim($properties) . PHP_EOL;
		}
		$str .= '});' . PHP_EOL;
		return $str;
	}
}
