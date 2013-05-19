<?php

abstract class CM_CodeGenerator_Abstract {

	/**
	 * @param string $className
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	protected function _getParentClassName($className) {
		$parts = explode('_', $className);
		$classNamespace = array_shift($parts);
		$type = array_shift($parts);
		$namespaces = array_reverse(CM_Bootloader::getInstance()->getNamespaces());
		$position = array_search($classNamespace, $namespaces);
		if (false === $position) {
			throw new CM_Exception_Invalid('Namespace `' . $classNamespace . '` not found within `' . implode(', ', $namespaces) . '` namespaces.');
		}
		$namespaces = array_splice($namespaces, $position);
		foreach ($namespaces as $namespace) {
			$className = $namespace . '_' . $type . '_Abstract';
			if ($this->_classExists($className)) {
				return $className;
			}
		}
		return 'CM_Class_Abstract';
	}

	/**
	 * @param string $className
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	protected function _getClassDirectory($className) {
		$namespace = CM_Util::getNamespace($className);
		$libraryNamespacePaths = CM_Bootloader::getInstance()->getNamespacePathsLibrary();
		if (!array_key_exists($namespace, $libraryNamespacePaths)) {
			throw new CM_Exception_Invalid('Cannot generate code for non-library namespace');
		}
		return DIR_ROOT . $libraryNamespacePaths[$namespace];
	}

	/**
	 * @param string $className
	 * @return bool
	 */
	protected function _classExists($className) {
		$namespace = CM_Util::getNamespace($className);
		$classPath = CM_Util::getNamespacePath($namespace) . 'library/' . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		return CM_File::exists($classPath);
	}
}
