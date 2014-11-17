<?php

abstract class CM_Tools_Generator_Class_Abstract {

    /**
     * @param string $className
     * @return string
     */
    abstract protected function _getClassPath($className);

    /** @var CM_Tools_AppInstallation */
    protected $_appInstallation;

    /** @var CM_Tools_Generator_FilesystemHelper */
    protected $_filesystemHelper;

    /**
     * @param CM_Tools_AppInstallation $appInstallation
     * @param CM_OutputStream_Interface $output
     */
    public function __construct(CM_Tools_AppInstallation $appInstallation, CM_OutputStream_Interface $output) {
        $this->_appInstallation = $appInstallation;
        $this->_filesystemHelper = new CM_Tools_Generator_FilesystemHelper($output);
    }

    /**
     * @param string $className
     * @throws CM_Exception_Invalid
     * @return string
     */
    public function getParentClassName($className) {
        $parts = explode('_', $className);
        $classNamespace = array_shift($parts);
        $type = array_shift($parts);
        $namespaces = array_reverse($this->_appInstallation->getModuleNames());
        $position = array_search($classNamespace, $namespaces);
        if (false === $position) {
            throw new CM_Exception_Invalid('Namespace module `' . $classNamespace . '` not found within `' . implode(', ', $namespaces) .
            '` modules.');
        }
        $namespaces = array_splice($namespaces, $position);
        foreach ($namespaces as $namespace) {
            $parentClassName = $namespace . '_' . $type . '_Abstract';
            if ($this->_classFileExists($parentClassName)) {
                return $parentClassName;
            }
        }
        return 'CM_Class_Abstract';
    }

    /**
     * @param string $className
     * @return bool
     */
    protected function _classFileExists($className) {
        return $this->_appInstallation->getFilesystem()->exists($this->_getClassPath($className));
    }
}
