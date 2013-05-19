<?php

class CM_CodeGenerator_Smarty extends CM_CodeGenerator_Abstract {

	/**
	 * @param string $className
	 * @throws CM_Exception_Invalid
	 * @return CM_File
	 */
	public function createTemplateFile($className) {
		if (!$this->_classExists($className)) {
			throw new CM_Exception_Invalid('Cannot create template for non-existing class `' . $className . '`');
		}
		$templatePath = $this->_getTemplatePath($className);
		CM_Util::mkDir(dirname($templatePath));
		return CM_File::create($templatePath);
	}

	/**
	 * @param string $className
	 * @return string
	 */
	private function _getTemplatePath($className) {
		$pathParts = explode('_', $className, 3);
		array_shift($pathParts);
		return $this->_getClassDirectory($className) . 'layout/default/' . implode('/', $pathParts) . '/default.tpl';
	}
}
