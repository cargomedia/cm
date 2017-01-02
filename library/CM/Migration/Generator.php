<?php

class CM_Migration_Generator {

    /** @var string */
    private $_parentClassName;

    /** @var CM_File_Filesystem */
    private $_filesystem;

    /**
     * CM_Migration_Generator constructor.
     * @param CM_File_Filesystem $filesystem
     * @param null               $parentClassName
     */
    public function __construct(CM_File_Filesystem $filesystem, $parentClassName = null) {
        $parentClassName = null !== $parentClassName ? (string) $parentClassName : 'CM_Migration_Script';
        if (!class_exists($parentClassName)) {
            throw new CM_Exception_Invalid('Parent migration class does not exist', null, [
                'parentClassName' => $parentClassName,
            ]);
        }
        $this->_filesystem = $filesystem;
        $this->_parentClassName = $parentClassName;
    }

    /**
     * @param string $name
     * @return CM_File
     */
    public function save($name) {
        $fileName = sprintf('%s_%s', time(), $this->_sanitize($name));
        $className = sprintf('%s_%s', $this->_getParentClassName(), $fileName);
        $fileNameWithExtension = sprintf('%s.php', $fileName);
        $file = new CM_File($fileNameWithExtension, $this->_getFilesystem());
        $fileBlock = new CodeGenerator\FileBlock();
        $fileBlock->addBlock($this->_getClassBlock($className));
        $file->ensureParentDirectory();
        $file->write($fileBlock->dump());
        return $file;
    }

    /**
     * @param string $name
     * @return string
     *
     */
    protected function _sanitize($name) {
        $camelized = CM_Util::camelize(trim((string) $name));
        if (!preg_match('/^[a-z0-9_]+$/i', $camelized)) {
            throw new CM_Exception_Invalid('Invalid migration script name', null, [
                'scriptName' => $name,
            ]);
        }
        return $camelized;
    }

    /**
     * @return CM_File_Filesystem
     */
    protected function _getFilesystem() {
        return $this->_filesystem;
    }

    /**
     * @return string
     */
    protected function _getParentClassName() {
        return $this->_parentClassName;
    }

    /**
     * @param $className
     * @return \CodeGenerator\ClassBlock
     */
    protected function _getClassBlock($className) {
        $class = new CodeGenerator\ClassBlock($className, $this->_getParentClassName());
        $reflection = new ReflectionClass($this->_getParentClassName());
        $method = CodeGenerator\MethodBlock::buildFromReflection($reflection->getMethod('up'));
        $method->setAbstract(false);
        $method->setDocBlock(join(PHP_EOL, [
            '/**',
            ' * Describe the migration script',
            ' */'
        ]));
        $method->setCode('// TODO: Implement the migration script');
        $class->addMethod($method);
        return $class;
    }
}
