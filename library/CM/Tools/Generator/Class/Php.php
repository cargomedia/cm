<?php

class CM_Tools_Generator_Class_Php extends CM_Tools_Generator_Class_Abstract {

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
     * @throws CM_Exception_Invalid
     * @return CodeGenerator\ClassBlock
     */
    public function createClass($className) {
        if (class_exists($className)) {
            throw new CM_Exception_Invalid('Class `' . $className . '` already exists');
        }
        $parentClassName = $this->getParentClassName($className);
        $class = new CodeGenerator\ClassBlock($className, $parentClassName);
        if ($this->_isAbstractClassName($className)) {
            $class->setAbstract(true);
        } else {
            $reflection = new ReflectionClass($parentClassName);
            foreach ($reflection->getMethods(ReflectionMethod::IS_ABSTRACT) as $reflectionMethod) {
                $method = CodeGenerator\MethodBlock::buildFromReflection($reflectionMethod);
                $method->setAbstract(false);
                $method->setDocBlock(null);
                $method->setCode('// TODO: Implement method body');
                $class->addMethod($method);
            }
        }
        return $class;
    }

    /**
     * @param CodeGenerator\ClassBlock $classBlock
     * @return CM_File
     */
    public function createClassFileFromClass(CodeGenerator\ClassBlock $classBlock) {
        $fileBlock = new CodeGenerator\FileBlock();
        $fileBlock->addBlock($classBlock);
        $classPath = $this->_getClassPath($classBlock->getName());
        $classFile = new CM_File($classPath, $this->_appInstallation->getFilesystem());
        $this->_filesystemHelper->createFile($classFile, $fileBlock->dump());
        require_once($this->_appInstallation->getFilesystem()->getAdapter()->getPathPrefix() . '/' . $classPath);
        return $classFile;
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
