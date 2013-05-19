<?php

class CM_CodeGenerator_Php {

	/**
	 * @param string $className
	 * @return CM_File
	 */
	public function createClassFile($className) {
		$class = $this->createClass($className);
		return $this->createClassFileFromClass($class);
	}

	/**
	 * @param string $className
	 * @return CG_Class
	 */
	public function createClass($className) {
		$parentClassName = $this->_getParentClassName($className);
		$class = new CG_Class($className, $parentClassName);
		$reflection = new ReflectionClass($parentClassName);
		foreach ($reflection->getMethods(ReflectionMethod::IS_ABSTRACT) as $reflectionMethod) {
			$method = CG_Method::buildFromReflection($reflectionMethod);
			$method->setDocBlock(null);
			$method->setCode('// TODO: Implement method body');
			$class->addMethod($method);
		}
		return $class;
	}

	/**
	 * @param CG_Class $class
	 * @return CM_File
	 */
	public function createClassFileFromClass(CG_Class $class) {
		$classPath = $this->_getClassPath($class->getName());
		$file = new CG_File();
		$file->addBlock($class);
		CM_Util::mkDir(dirname($classPath));
		return CM_File::create($classPath, $file->dump());
	}

	/**
	 * @param string $className
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	private function _getClassPath($className) {
		$namespace = substr($className, 0, strpos($className, '_'));
		$libraryNamespacePaths = CM_Bootloader::getInstance()->getNamespacePathsLibrary();
		if (!array_key_exists($namespace, $libraryNamespacePaths)) {
			throw new CM_Exception_Invalid('Cannot generate class for non-library namespace');
		}
		return DIR_ROOT . $libraryNamespacePaths[$namespace] . DIR_LIBRARY. str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	}

	/**
	 * @param string $className
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	private function _getParentClassName($className) {
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
			if (class_exists($className)) {
				return $className;
			}
		}
		return 'CM_Class_Abstract';
	}
}
