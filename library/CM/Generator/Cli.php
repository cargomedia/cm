<?php

class CM_Generator_Cli extends CM_Cli_Runnable_Abstract {

	/**
	 * @param string $className
	 * @throws CM_Exception_Invalid
	 */
	public function createView($className) {
		if (class_exists($className)) {
			throw new CM_Exception_Invalid('`' . $className . '` already exists');
		}
		$this->_generateClassFilePhp($className);
		$this->_generateClassFileJavascript($className);
		$this->_generateViewLayout($className);
	}

	/**
	 * @param string $className
	 */
	public function createClass($className) {
		$this->_generateClassFilePhp($className);
	}

	/**
	 * @param string $namespace
	 */
	public function createNamespace($namespace) {
		$this->_createNamespaceDirectories($namespace);
		$this->_generateClassFilePhp($namespace . '_Site', 'CM_Site_Abstract');
		$bootloaderFile = $this->_generateClassFilePhp($namespace . '_Bootloader', 'CM_Bootloader');
		$namespaces = array_merge(array($namespace), CM_Bootloader::getInstance()->getNamespaces());
		$bootloaderFile->addMethod('public', 'getNamespaces', array(), "return array('" . implode("', '", $namespaces) . "');");
	}

	/**
	 * @param string $namespace
	 */
	private function _createNamespaceDirectories($namespace) {
		$paths = array();
		$paths[] = DIR_ROOT . DIR_LIBRARY . $namespace . '/library/' . $namespace;
		$paths[] = DIR_ROOT . DIR_LIBRARY . $namespace . '/layout/default';
		foreach ($paths as $path) {
			CM_Util::mkDir($path);
			$this->_getOutput()->writeln('Created `'  . $path . '`');
		}
	}

	/**
	 * @param string        $className
	 * @param string|null   $parentClass
	 * @return CM_File_Php
	 */
	private function _generateClassFilePhp($className, $parentClass = null) {
		$parts = explode('_', $className);
		$namespace = array_shift($parts);
		$type = array_shift($parts);
		if (!$parentClass) {
			$parentClass = $this->_getParentClass($namespace, $type);
		}
		$file = CM_File_Php::createLibraryClass($className, $parentClass);
		$this->_getOutput()->writeln('Created `' . $file->getPath() . '`');
		$reflectionClass = new ReflectionClass($parentClass);
		foreach ($reflectionClass->getMethods() as $method) {
			if ($method->isAbstract()) {
				$file->copyMethod($method);
			}
		}
		return $file;
	}

	/**
	 * @param string $className
	 * @return CM_File_Javascript
	 */
	private function _generateClassFileJavascript($className) {
		$file = CM_File_Javascript::createLibraryClass($className);
		$this->_getOutput()->writeln('Created `' . $file->getPath() . '`');
		return $file;
	}

	/**
	 * @param string $className
	 */
	private function _generateViewLayout($className) {
		$parts = explode('_', $className);
		$namespace = array_shift($parts);
		$pathRelative = implode('_', $parts);
		$layoutPath = CM_Util::getNamespacePath($namespace) . 'layout/' . $pathRelative . '/';
		CM_Util::mkDir($layoutPath);
		$file = CM_File::create($layoutPath . 'default.tpl');
		$this->_getOutput()->writeln('Created `' . $file->getPath() . '`');
		$file = CM_File::create($layoutPath . 'default.less');
		$this->_getOutput()->writeln('Created `' . $file->getPath() . '`');
	}

	/**
	 * @param string $viewNamespace
	 * @param string $type
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	private function _getParentClass($viewNamespace, $type) {
		$namespaces = array_reverse(CM_Bootloader::getInstance()->getNamespaces());
		if (!in_array($viewNamespace, $namespaces)) {
			throw new CM_Exception_Invalid('Namespace `' . $viewNamespace . '` not found within `' . implode(', ', $namespaces) . '` namespaces.');
		}
		$position = array_search($viewNamespace, $namespaces);
		$namespaces = array_splice($namespaces, $position);
		foreach ($namespaces as $namespace) {
			$className = $namespace . '_' . $type . '_Abstract';
			if (class_exists($className)) {
				return $className;
			}
		}
		throw new CM_Exception_Invalid('No abstract class found for `' . $type . '` type within `' . implode(', ', $namespaces) . '` namespaces.');
	}

	public static function getPackageName() {
		return 'generator';
	}

}