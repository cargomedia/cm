<?php

class CM_Provision_Loader {

    /** @var CM_Provision_Script_Abstract[] */
    private $_scriptList;

    public function __construct() {
        $this->_scriptList = [];
    }

    /**
     * @param CM_Provision_Script_Abstract $script
     */
    public function registerScript(CM_Provision_Script_Abstract $script) {
        $this->_scriptList[] = $script;
    }

    /**
     * @param string[]           $scriptClassNames
     * @param CM_Service_Manager $serviceManager
     */
    public function registerScriptFromClassNames(array $scriptClassNames, CM_Service_Manager $serviceManager) {
        foreach ($scriptClassNames as $scriptClassName) {
            $this->registerScript(new $scriptClassName($serviceManager));
        }
    }

    public function load(CM_OutputStream_Interface $output) {
        $scriptList = $this->_getScriptList();
        foreach ($scriptList as $setupScript) {
            if ($setupScript->shouldBeLoaded()) {
                $output->writeln('  Loading ' . $setupScript->getName() . '…');
                $setupScript->load($output);
            }
        }
    }

    public function unload(CM_OutputStream_Interface $output) {
        $scriptList = array_reverse($this->_getScriptList());
        foreach ($scriptList as $setupScript) {
            if ($setupScript instanceof CM_Provision_Script_UnloadableInterface && $setupScript->shouldBeUnloaded()) {
                /** @var $setupScript CM_Provision_Script_Abstract|CM_Provision_Script_UnloadableInterface */
                $output->writeln('  Unloading ' . $setupScript->getName() . '…');
                $setupScript->unload($output);
            }
        }
    }

    public function reload(CM_OutputStream_Interface $output) {
        $scriptList = $this->_getScriptList();
        foreach ($scriptList as $setupScript) {
            if ($setupScript->shouldBeLoaded()) {
                $output->writeln('  Loading ' . $setupScript->getName() . '…');
                $setupScript->load($output);
            } elseif ($setupScript instanceof CM_Provision_Script_UnloadableInterface) {
                /** @var $setupScript CM_Provision_Script_Abstract */
                $output->writeln('  Reloading ' . $setupScript->getName() . '…');
                $setupScript->reload($output);
            }
        }
    }

    /**
     * @return CM_Provision_Script_Abstract[]
     */
    protected function _getScriptList() {
        $scriptList = $this->_scriptList;
        $runLevelList = \Functional\invoke($scriptList, 'getRunLevel');
        array_multisort($runLevelList, $scriptList);
        return $scriptList;
    }
}
