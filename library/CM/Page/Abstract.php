<?php

abstract class CM_Page_Abstract extends CM_Component_Abstract {

	public final function checkAccessible() {
	}

	/**
	 * Checks if the page is viewable by the current user
	 *
	 * @return bool True if page is visible
	 */
	public function isViewable() {
		return true;
	}

	/**
	 * @param CM_Response_Page $response
	 */
	public function prepareResponse(CM_Response_Page $response) {
	}

	/**
	 * @param string $namespace
	 * @param string $path
	 * @return CM_Page_Abstract
	 */
	public static final function getClassnameByPath($namespace, $path) {
		$namespace = (string) $namespace;
		$path = (string) $path;

		$pathTokens = explode('/', $path);
		array_shift($pathTokens);

		// Rewrites code-of-honor to CodeOfHonor
		foreach ($pathTokens as &$pathToken) {
			$pathToken = CM_Util::camelize($pathToken);
		}

		return $namespace . '_Page_' . implode('_', $pathTokens);
	}

	/**
	 * @param array|null $params
	 * @return string
	 */
	public static function getPath(array $params = null) {
		$pageClassName = get_called_class();
		$list = explode('_', $pageClassName);

		// Remove first parts
		foreach ($list as $index => $entry) {
			unset($list[$index]);
			if ($entry == 'Page') {
				break;
			}
		}

		// Converts upper case letters to dashes: CodeOfHonor => code-of-honor
		foreach ($list as $index => $entry) {
			$list[$index] = preg_replace('/([A-Z])/', '-\1', lcfirst($entry));
		}

		$path = '/' . strtolower(implode('/', $list));
		if ($path == '/index') {
			$path = '/';
		}
		return CM_Util::link($path, $params);
	}

	/**
	 * @return CM_Layout_Abstract
	 */
	public function getLayout() {
		$layoutname = 'Default';
		$classname = self::_getClassNamespace() . '_Layout_' . $layoutname;
		return new $classname($this);
	}
}
