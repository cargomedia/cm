<?php

class CM_Migration_Generator {

    /** @var string */
    private $_prefixClassName;

    /** @var CM_File_Filesystem */
    private $_filesystem;

    /**
     * @param CM_File_Filesystem $filesystem
     * @param string|null        $prefixClassName
     * @throws CM_Exception_Invalid
     */
    public function __construct(CM_File_Filesystem $filesystem, $prefixClassName = null) {
        $prefixClassName = null !== $prefixClassName ? (string) $prefixClassName : 'Migration_';
        $this->_filesystem = $filesystem;
        $this->_prefixClassName = $prefixClassName;
    }

    /**
     * @param string $name
     * @return CM_File
     */
    public function save($name) {
        $fileName = sprintf('%s_%s', time(), $this->_sanitize($name));
        $className = sprintf('%s%s', $this->_getPrefixClassName(), $fileName);
        $fileNameWithExtension = sprintf('%s.php', $fileName);
        $file = new CM_File($fileNameWithExtension, $this->_getFilesystem());
        if ($file->exists()) {
            throw new CM_Exception_Invalid('A migration script with the same name already exists.', null, [
                'file' => $file->getPathOnLocalFilesystem(),
            ]);
        }
        $fileBlock = new CodeGenerator\FileBlock();
        $fileBlock->addBlock($this->_getClassBlock($className));
        $file->ensureParentDirectory();
        $file->write($fileBlock->dump());
        return $file;
    }

    /**
     * @param string $name
     * @return string
     * @throws CM_Exception_Invalid
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
    protected function _getPrefixClassName() {
        return $this->_prefixClassName;
    }

    /**
     * @param $className
     * @return \CodeGenerator\ClassBlock
     */
    protected function _getClassBlock($className) {
        $class = new CodeGenerator\ClassBlock($className, null, [
            CM_Migration_UpgradableInterface::class,
            CM_Service_ManagerAwareInterface::class,
        ]);
        $class->addUse(new CodeGenerator\TraitBlock(CM_Service_ManagerAwareTrait::class));

        $reflection = new ReflectionClass(CM_Migration_UpgradableInterface::class);
        $method = CodeGenerator\MethodBlock::buildFromReflection($reflection->getMethod('up'));
        $method->setAbstract(false);
        $method->setDocBlock(null);
        $method->setCode('// TODO: Implement the migration script');
        $class->addMethod($method);
        return $class;
    }
}
