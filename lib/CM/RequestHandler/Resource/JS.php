<?php

class CM_RequestHandler_Resource_JS extends CM_RequestHandler_Resource_Abstract {

	public function process() {
		$this->setHeader('Content-Type', 'application/x-javascript');
		$this->enableCache();

		if ($this->_getFilename() == 'internal.js') {
			$content = new CM_File(DIR_PUBLIC . 'static/js/interface.js') . ';' . PHP_EOL;

			$namespaces = self::getSite()->getNamespaces();

			$componentJs = array();
			$formFieldJs = array();
			$formJs = array();
			$renderableJs = array();

			foreach ($namespaces as $namespace) {
				$renderableJs = array_merge($renderableJs, rglob('*.php', DIR_INTERNALS . $namespace . '/Renderable/'));
				$componentJs = array_merge($componentJs, rglob('*.php', DIR_INTERNALS . $namespace . '/Component/'));
				$formFieldJs = array_merge($formFieldJs, rglob('*.php', DIR_INTERNALS . $namespace . '/FormField/'));
				$formJs = array_merge($formJs, rglob('*.php', DIR_INTERNALS . $namespace . '/Form/'));
			}

			$classes = array_merge($renderableJs, $componentJs, $formFieldJs, $formJs);

			foreach ($this->_getClasses($classes) as $class) {
				$jsPath = preg_replace('/\.php$/', '.js', $class['path']);
				$properties = file_exists($jsPath) ? new CM_File($jsPath) : null;
				$content .= $this->_printClass($class['name'], $class['parent'], $properties);
			}
		} elseif ($this->_getFilename() == 'library.js') {
			$content = '';
			$content .= new CM_File('static/js/library/json2.js');
			$content .= new CM_File('static/js/library/jquery.js');
			$content .= new CM_File('static/js/library/underscore.js');
			$content .= new CM_File('static/js/library/backbone.js');
			$content .= new CM_File('static/js/library/socket.io.js');
			$content .= new CM_File('static/js/library/fileuploader.js');
			foreach (glob(DIR_PUBLIC . 'static/js/library/jquery-plugins/*.js') as $path) {
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
