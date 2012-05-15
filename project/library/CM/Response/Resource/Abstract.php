<?php

abstract class CM_Response_Resource_Abstract extends CM_Response_Abstract {

	/**
	 * @var string
	 */
	private $_filename;

	/**
	 * @param CM_Request_Abstract $request
	 * @param int                 $siteId OPTIONAL
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
	 * @return array Ordered class infos, each an array with keys 'classNames' and 'path'
	 * @throws CM_Exception
	 */
	protected function _getClasses(array $paths) {
		$classes = array();
		$regexp = '#class\s+(?<name>.+?)\s+(extends\s+(?<parent>.+?))?\s*{#';

		// Detect class names and parents
		foreach ($paths as $path) {
			$file = new CM_File($path);

			if (!preg_match($regexp, $file->read(), $match)) {
				throw new CM_Exception('Cannot detect php-class inheritance of `' . $path . '`');
			}

			$classHierarchy = array_values(class_parents($match['name']));
			array_unshift($classHierarchy, $match['name']);
			if ('CM_Class_Abstract' == end($classHierarchy)) {
				array_pop($classHierarchy);
			}
			$classes[] = array('classNames' => $classHierarchy, 'path' => $path);
		}

		// Order classes by inheritance
		for ($i1 = 0; $i1 < count($classes); $i1++) {
			$class1 = $classes[$i1];
			for ($i2 = $i1 + 1; $i2 < count($classes); $i2++) {
				$class2 = $classes[$i2];
				if (isset($class1['classNames'][1]) && $class1['classNames'][1] == $class2['classNames'][0]) {
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
