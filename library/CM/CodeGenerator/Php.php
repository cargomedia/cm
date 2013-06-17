<?php

class CM_CodeGenerator_Php extends CM_CodeGenerator_Abstract {

	/**
	 * @param string $className
	 * @return CM_File
	 */
	public function createClassFile($className) {
		$class = $this->createClass($className);
		$file = $this->createClassFileFromClass($class);
		require_once($file->getPath());
		return $file;
	}

	/**
	 * @param string $className
	 * @return CG_Class
	 */
	public function createClass($className) {
		$parentClassName = $this->_getParentClassName($className);
		$class = new CG_Class($className, $parentClassName);
		if ($this->_isAbstractClassName($className)) {
			$class->setAbstract(true);
		}
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
	 */
	private function _getClassPath($className) {
		return $this->_getClassDirectory($className) . 'library/' . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	}

	/**
	 * @param string $className
	 * @return bool
	 */
	private function _isAbstractClassName($className) {
		$parts = explode('_', $className);
		return 'Abstract' === end($parts);
	}
}
