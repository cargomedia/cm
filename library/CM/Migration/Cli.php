<?php

class CM_Migration_Cli extends CM_Cli_Runnable_Abstract {

    public function all() {
        $loader = new CM_Migration_Loader($this->getServiceManager());
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
        $loader = new CM_Migration_Loader($this->getServiceManager());
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
        $adapter = new CM_File_Filesystem_Adapter_Local($this->_getMigrationPath($namespace));
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
     * @param string|null $namespace
     * @return string
     */
    protected function _getMigrationPath($namespace = null) {
        $modulePath = $namespace ? CM_Util::getModulePath($namespace) : DIR_ROOT;
        return join(DIRECTORY_SEPARATOR, [
            $modulePath, 'resources', CM_Migration_Loader::MIGRATION_DIR
        ]);
    }

    public static function getPackageName() {
        return 'migrate';
    }
}
