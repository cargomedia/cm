<?php

class CM_Migration_Cli extends CM_Cli_Runnable_Abstract {

    public function all() {
        $this->_getStreamOutput()->writeln('Running migration scripts…');
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

    public static function getPackageName() {
        return 'migrate';
    }

    /**
     * @param CM_Migration_Script $script
     * @throws Exception
     */
    protected function _loadScript(CM_Migration_Script $script) {
        $output = $this->_getStreamOutput();
        $output->write(sprintf('- load "%s" update script…', $script->getName()));
        try {
            $script->load();
        } catch (Exception $e) {
            $output->writeln('failed');
            throw $e;
        }
        $output->writeln('done');
    }
}
