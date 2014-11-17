<?php

class CM_Tools_Generator_Class_Layout extends CM_Tools_Generator_Class_Abstract {

    /**
     * @param string $className
     */
    public function createTemplateFile($className) {
        $reflectionClass = new ReflectionClass($className);
        if ($reflectionClass->isSubclassOf('CM_Form_Abstract')) {
            return;
        }
        $templateDirectory = new CM_File($this->_getTemplatePath($className), $this->_appInstallation->getFilesystem());
        $this->_filesystemHelper->createDirectory($templateDirectory);

        $content = '';
        if ($reflectionClass->isSubclassOf('CM_Page_Abstract')) {
            $content = $this->_generatePageContent($reflectionClass);
        }
        $this->_createLayoutFile($className, 'default.tpl', $content);
    }

    /**
     * @param string $className
     */
    public function createStylesheetFile($className) {
        $reflectionClass = new ReflectionClass($className);
        if (!$reflectionClass->isSubclassOf('CM_Form_Abstract')) {
            $this->_createLayoutFile($className, 'default.less');
        }
    }

    /**
     * @param string $className
     * @param string $templateBasename
     * @param string $content
     * @throws CM_Exception_Invalid
     * @return CM_File
     */
    private function _createLayoutFile($className, $templateBasename, $content = null) {
        if (!$this->_classExists($className)) {
            throw new CM_Exception_Invalid('Cannot create layout for non-existing class `' . $className . '`');
        }
        $templatePath = $this->_getTemplatePath($className) . $templateBasename;
        $templateFile = new CM_File($templatePath, $this->_appInstallation->getFilesystem());
        $this->_filesystemHelper->createFile($templateFile, $content);
    }

    /**
     * @param string $className
     * @return string
     */
    protected function _getClassPath($className) {
        return $this->_getTemplatePath($className);
    }

    /**
     * @param string $className
     * @return string
     */
    protected function _getTemplatePath($className) {
        $moduleName = CM_Util::getNamespace($className);
        $modulePath = $this->_appInstallation->getModulePath($moduleName);
        return $modulePath . 'layout/default/' . $this->_extractTemplateName($className);
    }

    /**
     * @param string $className
     * @return string
     */
    protected function _extractTemplateName($className) {
        $pathParts = explode('_', $className, 3);
        array_shift($pathParts);
        return implode('/', $pathParts) . '/';
    }

    /**
     * @param ReflectionClass $reflection
     * @return null|string
     */
    protected function _generatePageContent(ReflectionClass $reflection) {
        if ($reflection->isSubclassOf('CM_Page_Abstract')) {
            $parentClassName = $reflection->getParentClass()->getName();
            $content = "{extends file=\$render->getLayoutPath('" . $this->_extractTemplateName($parentClassName) . "default.tpl'";
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
