<?php

class CM_Migration_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param string|null $name
     * @throws CM_Exception_Invalid
     */
    public function run($name = null) {
        $migrationPaths = CM_Util::getMigrationPaths();
        $loader = new CM_Migration_Loader($this->getServiceManager(), $migrationPaths);
        if (null === $name) {
            foreach ($loader->getRunnerList() as $runner) {
                if ($runner->shouldBeLoaded()) {
                    $this->_load($runner);
                }
            }
        } else {
            if ($runner = $loader->findRunner($name)) {
                $this->_load($runner);
            } else {
                throw new CM_Exception_Invalid('Migration script not found', null, [
                    'scriptName' => $name,
                ]);
            }
        }
    }

    /**
     * @param string|null $namespace
     * @param string|null $name
     */
    public function add($namespace = null, $name = null) {
        if (null === $name) {
            $defaultName = trim(CM_Util::exec('git rev-parse --abbrev-ref HEAD'));
            $name = $this->_getStreamInput()->read(sprintf('Migration script name [%s]:', $defaultName), $defaultName);
        }
        $migrationPath = CM_Util::getMigrationPathByModule($namespace);
        $adapter = new CM_File_Filesystem_Adapter_Local($migrationPath);
        $filesystem = new CM_File_Filesystem($adapter);
        $generator = new CM_Migration_Generator($filesystem);
        $file = $generator->save($name);
        $this->_getStreamOutput()->writeln(sprintf('`%s` generated', $file->getPathOnLocalFilesystem()));
    }

    /**
     * @param CM_Migration_Runner $runner
     * @throws Exception
     */
    protected function _load(CM_Migration_Runner $runner) {
        $output = $this->_getStreamOutput();
        $output->write(sprintf('- %s', $runner->getName()));
        if ($desc = $runner->getDescription()) {
            $output->write(sprintf(': %s', $desc));
        }
        $output->writeln("…");
        $runner->load($output);
    }

    public static function getPackageName() {
        return 'migration';
    }
}
