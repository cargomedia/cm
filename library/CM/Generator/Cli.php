<?php

class CM_Generator_Cli extends CM_Cli_Runnable_Abstract {

	/**
	 * @param string $viewClassName
	 * @throws CM_Exception_Invalid
	 */
	public function scaffold($viewClassName) {
		if (class_exists($viewClassName)) {
			throw new CM_Exception_Invalid('`' . $viewClassName . '` already exists');
		}
		$this->_generateClasses($viewClassName);
		$this->_generateLayouts($viewClassName);
	}

	public function createJavascriptFiles() {
		$viewClasses = CM_View_Abstract::getClasses(CM_Bootloader::getInstance()->getNamespaces(), CM_View_Abstract::CONTEXT_JAVASCRIPT);
		foreach ($viewClasses as $path => $className) {
			$jsPath = preg_replace('/\.php$/', '.js', $path);
			if (!file_exists($jsPath)) {
				$jsFile = CM_File_Javascript::createLibraryClass($className);
				echo 'create  ' . $jsFile->getPath() . PHP_EOL;
			}
		}
	}

	/**
	 * @param string $className
	 */
	private function _generateClasses($className) {
		$parts = explode('_', $className);
		$namespace = array_shift($parts);
		$type = array_shift($parts);
		$parentClass = $this->_getParentClass($namespace, $type);
		$file = CM_File_Php::createLibraryClass($className, $parentClass);
		$reflectionClass = new ReflectionClass($parentClass);
		foreach ($reflectionClass->getMethods() as $method) {
			if ($method->isAbstract()) {
				$file->copyMethod($method);
			}
		}
		$this->_echo('create ' . $file->getPath());
		$file = CM_File_Javascript::createLibraryClass($className);
		$this->_echo('create ' . $file->getPath());

	}

	/**
	 * @param string $className
	 */
	private function _generateLayouts($className) {
		$parts = explode('_', $className);
		$namespace = array_shift($parts);
		$pathRelative = implode('/', $parts);
		$layoutPath = CM_Util::getNamespacePath($namespace) . 'layout/' . $pathRelative . '/';
		CM_Util::mkDir($layoutPath);
		$file = CM_File::create($layoutPath . 'default.tpl');
		$this->_echo('create ' . $file->getPath());
		$file = CM_File::create($layoutPath . 'default.less');
		$this->_echo('create ' . $file->getPath());
	}

	/**
	 * @param string $viewNamespace
	 * @param string $type
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	private function _getParentClass($viewNamespace, $type) {
		$viewNamespaceQualified = false;
		$namespaces = CM_Bootloader::getInstance()->getNamespaces();
		foreach ($namespaces as $namespace) {
			if ($namespace === $viewNamespace) {
				$viewNamespaceQualified = true;
			}
			$className = $namespace . '_' . $type . '_Abstract';
			if ($viewNamespaceQualified && class_exists($className)) {
				return $className;
			}
		}
		throw new CM_Exception_Invalid('No abstract class found for `' . $type . '` type within `' . implode(', ', $namespaces) . '` mamespaces.');
	}



	public static function getPackageName() {
		return 'generator';
	}

}