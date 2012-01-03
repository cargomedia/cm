<?php

abstract class CM_Response_Resource_Abstract extends CM_Response_Abstract {

	/**
	 * @var string
	 */
	private $_filename;

	/**
	 * @param CM_Request_Abstract $request
	 * @param int $siteId OPTIONAL
	 */
	public function __construct(CM_Request_Abstract $request, $siteId = null) {
		parent::__construct($request, $siteId);
		$path = explode('/', $request->getPath());
		$filenamePaths = array_filter(array_splice($path, 4), function ($dir) {
			return '..' != $dir;
		});
		$this->_filename = implode('/', $filenamePaths);
	}

	/**
	 * @return string
	 */
	protected function _getFilename() {
		return $this->_filename;
	}
	
	/**
	* @param array $paths
	* @param string $kind OPTIONAL
	* @return array Ordered class infos, each an array with keys 'name', 'parent' and 'path'
	* @throws CM_Exception
	*/
	protected function _getClasses(array $paths, $kind = 'php') {
		$classes = array();
	
		if ($kind == 'php') {
			$regexp = '#class\s+(?<name>.+?)\s+(extends\s+(?<parent>.+?))?\s*{#';
		} elseif ($kind = 'js') {
			$regexp = '#var (?<name>.+?) = (?<parent>.+?)\.extend\(#';
		} else {
			throw new CM_Exception('Invalid class kind `' . $kind . '`.');
		}
	
		// Detect class names and parents
		foreach ($paths as $path) {
			$file = new CM_File($path);
				
			if (!preg_match($regexp, $file->read(), $class)) {
				throw new CM_Exception('Cannot detect `' . $kind . '`-class inheritance of `' . $path . '`');
			}
				
			if (!isset($class['parent']) || $class['parent'] == 'CM_Class_Abstract') {
				$class['parent'] = 'Backbone.View';
			}
				
			$class['path'] = $path;
			$classes[] = $class;
		}
	
		// Order classes by inheritance
		for ($i1 = 0; $i1 < count($classes); $i1++) {
			$class1 = $classes[$i1];
			for ($i2 = $i1 + 1; $i2 < count($classes); $i2++) {
				$class2 = $classes[$i2];
				if ($class1['parent'] == $class2['name']) {
					$tmp = $classes[$i1];
					$classes[$i1] = $classes[$i2];
					$classes[$i2] = $tmp;
					$i1--;
					break;
				}
			}
		}
	
		return $classes;
	}
}
