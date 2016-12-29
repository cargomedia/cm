<?php

class CM_Migration_Generator {

    const MIGRATION_CLASSNAME = 'CM_Migration_Script';

    /** @var string */
    private $_name;

    /** @var string|null */
    private $_namespace;

    /** @var string */
    private $_fileName;

    /** @var CM_File */
    private $_file;

    /**
     * @param string      $name
     * @param string|null $namespace
     */
    public function __construct($name, $namespace = null) {
        $this->_name = $name;
        $this->_namespace = $namespace;
        $this->_fileName = sprintf('%s_%s', time(), CM_Util::camelize(trim($name)));
        $this->_file = new CM_File($this->_getPath());
    }

    public function save() {
        $file = $this->getFile();
        $fileBlock = new CodeGenerator\FileBlock();
        $fileBlock->addBlock($this->_getClassBlock());
        $file->ensureParentDirectory();
        $file->write($fileBlock->dump());
    }

    /**
     * return CM_File
     */
    public function getFile() {
        return $this->_file;
    }

    /**
     * @return string
     */
    public function getFileName() {
        return $this->_fileName;
    }

    /**
     * @return string
     */
    public function getClassName() {
        return sprintf('%s_%s', self::MIGRATION_CLASSNAME, $this->getFileName());
    }

    /**
     * @return string
     */
    protected function _getPath() {
        $modulePath = $this->_namespace ? CM_Util::getModulePath($this->_namespace) : DIR_ROOT;
        return join(DIRECTORY_SEPARATOR, [
            $modulePath, 'resources', CM_Migration_Loader::MIGRATION_DIR, $this->getFileName() . '.php'
        ]);
    }

    /**
     * @return \CodeGenerator\ClassBlock
     * @throws CM_Exception_Invalid
     */
    protected function _getClassBlock() {
        $className = $this->getClassName();
        if (class_exists($className)) {
            throw new CM_Exception_Invalid('Class `' . $className . '` already exists');
        }

        $class = new CodeGenerator\ClassBlock($className, self::MIGRATION_CLASSNAME);
        $reflection = new ReflectionClass(self::MIGRATION_CLASSNAME);
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
