<?php

class CM_Migration_Cli extends CM_Cli_Runnable_Abstract {

    public function all() {
        $paths = $this->_getMigrationPaths();
        $loader = new CM_Migration_Loader($this->getServiceManager(), $paths);
        foreach ($loader->getScriptList() as $script) {
            if ($script->shouldBeLoaded()) {
                $this->_loadScript($script);
            }
        }
    }

    /**
     * @param string $name
     */
    public function load($name) {
        $paths = $this->_getMigrationPaths();
        $loader = new CM_Migration_Loader($this->getServiceManager(), $paths);
        if ($script = $loader->findScript($name)) {
            $this->_loadScript($script);
        } else {
            $this->_getStreamError()->writeln(sprintf('Migration script "%s" not found', $name));
        }
    }

    /**
     * @param string|null $namespace
     * @param string|null $name
     */
    public function add($namespace = null, $name = null) {
        if (null === $name) {
            $name = CM_Util::exec('git rev-parse --abbrev-ref HEAD');
        }
        $adapter = new CM_File_Filesystem_Adapter_Local($this->_getMigrationPathByModule($namespace));
        $filesystem = new CM_File_Filesystem($adapter);
        $generator = new CM_Migration_Generator($filesystem);
        $file = $generator->save($name);
        $this->_getStreamOutput()->writeln(sprintf('`%s` generated', $file->getPathOnLocalFilesystem()));
    }

    /**
     * @param CM_Migration_Script $script
     * @throws Exception
     */
    protected function _loadScript(CM_Migration_Script $script) {
        $output = $this->_getStreamOutput();
        $output->write(sprintf('- %s', $script->getName()));
        if ($desc = $script->getDescription()) {
            $output->write(sprintf(': %s', $desc));
        }
        try {
            $script->load();
        } catch (Exception $e) {
            $output->write(" \e[31m×\e[0m\n");
            throw $e;
        }
        $output->write(" \e[32m✓\e[0m\n");
    }

    /**
     * @return array
     */
    protected function _getMigrationPaths() {
        $paths = [];
        foreach (CM_Bootloader::getInstance()->getModules() as $moduleName) {
            $paths[] = $this->_getMigrationPathByModule($moduleName);
        }
        return $paths;
    }

    /**
     * @param string|null $moduleName
     * @return string
     */
    protected function _getMigrationPathByModule($moduleName = null) {
        $modulePath = null !== $moduleName ? CM_Util::getModulePath((string) $moduleName) : DIR_ROOT;
        return join(DIRECTORY_SEPARATOR, [$modulePath, 'resources', 'migration']);
    }

    public static function getPackageName() {
        return 'migrate';
    }
}
