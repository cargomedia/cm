<?php

abstract class CMTools_Generator_Class_Abstract {

    /** @var CMTools_AppInstallation */
    protected $_appInstallation;

    /** @var CMTools_Generator_FilesystemHelper */
    protected $_filesystemHelper;

    /**
     * @param CMTools_AppInstallation   $appInstallation
     * @param CM_OutputStream_Interface $output
     */
    public function __construct(CMTools_AppInstallation $appInstallation, CM_OutputStream_Interface $output) {
        $this->_appInstallation = $appInstallation;
        $this->_filesystemHelper = new CMTools_Generator_FilesystemHelper($this->_appInstallation->getFilesystem(), $output);
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
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _getClassDirectory($className) {
        $namespace = CM_Util::getNamespace($className);
        $namespaces = $this->_appInstallation->getNamespaces();
        if (!in_array($namespace, $namespaces)) {
            throw new CM_Exception_Invalid('Cannot find `' . $namespace . '` namespace');
        }
        return $this->_appInstallation->getNamespacePath($namespace);
    }

    /**
     * @param string $className
     * @return bool
     */
    protected function _classFileExists($className) {
        $namespace = CM_Util::getNamespace($className);
        $classPath = $this->_appInstallation->getNamespacePath($namespace) . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        return $this->_appInstallation->getFilesystem()->exists($classPath);
    }
}
