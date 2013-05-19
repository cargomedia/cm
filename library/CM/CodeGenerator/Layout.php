<?php

class CM_CodeGenerator_Layout extends CM_CodeGenerator_Abstract {

	/**
	 * @param string $className
	 * @return CM_File
	 */
	public function createTemplateFile($className) {
		return $this->_createLayoutFile($className, 'default.tpl');
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
	 * @return CM_File
	 * @throws CM_Exception_Invalid
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
		$pathParts = explode('_', $className, 3);
		array_shift($pathParts);
		return $this->_getClassDirectory($className) . 'layout/default/' . implode('/', $pathParts) . '/';
	}
}
