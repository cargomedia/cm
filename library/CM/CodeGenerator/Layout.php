<?php

class CM_CodeGenerator_Layout extends CM_CodeGenerator_Abstract {

	/**
	 * @param string $className
	 * @return CM_File
	 */
	public function createTemplateFile($className) {
		$templateFile = $this->_createLayoutFile($className, 'default.tpl');

		$reflectionClass = new ReflectionClass($className);
		if ($reflectionClass->isSubclassOf('CM_Page_Abstract')) {
			$content = $this->_getPageContent($reflectionClass);
			$templateFile->write($content);
		}
		return $templateFile;
	}

	/**
	 * @param string $className
	 * @return CM_File
	 */
	public function createStylesheetFile($className) {
		return $this->_createLayoutFile($className, 'default.less');
	}

	/**
	 * @param string $className
	 * @param string $templateBasename
	 * @throws CM_Exception_Invalid
	 * @return CM_File
	 */
	private function _createLayoutFile($className, $templateBasename) {
		if (!$this->_classExists($className)) {
			throw new CM_Exception_Invalid('Cannot create layout for non-existing class `' . $className . '`');
		}
		$templateDirectory = $this->_getTemplateDirectory($className);
		CM_Util::mkDir($templateDirectory);
		return CM_File::create($templateDirectory . $templateBasename);
	}

	/**
	 * @param string $className
	 * @return string
	 */
	private function _getTemplateDirectory($className) {
		return $this->_getClassDirectory($className) . 'layout/default/' . $this->_getTemplateDirectoryRelative($className);
	}

	/**
	 * @param string $className
	 * @return string
	 */
	private function _getTemplateDirectoryRelative($className) {
		$pathParts = explode('_', $className, 3);
		array_shift($pathParts);
		return implode('/', $pathParts) . '/';
	}

	/**
	 * @param ReflectionClass $reflection
	 * @return null|string
	 */
	private function _getPageContent(ReflectionClass $reflection) {
		if ($reflection->isSubclassOf('CM_Page_Abstract')) {
			$parentClassName = $reflection->getParentClass()->getName();
			$content = "{extends file=\$render->getLayoutPath('" . $this->_getTemplateDirectoryRelative($parentClassName) . "default.tpl'";
			if ($reflection->isAbstract()) {
				$namespace = CM_Util::getNamespace($parentClassName);
				$content .= ", '" . $namespace . "'";
			}
			$content .= ")}\n";
			return $content;
		}
		return null;
	}
}
