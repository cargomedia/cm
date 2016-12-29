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

    public static function getPackageName() {
        return 'migrate';
    }

    /**
     * @param CM_Migration_Script $script
     * @throws Exception
     */
    protected function _loadScript(CM_Migration_Script $script) {
        $output = $this->_getStreamOutput();
        $output->write(sprintf("- %s", $script->getName()));
        if ($desc = $script->getDescription()) {
            $output->write(sprintf(": %s", $desc));
        }
        try {
            $script->load();
        } catch (Exception $e) {
            $output->write(" \e[31m×\e[0m\n");
            throw $e;
        }
        $output->write(" \e[32m✓\e[0m\n");
    }
}