<?php

class CM_Provision_Loader implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var CM_OutputStream_Interface */
    private $_output;

    /** @var CM_Provision_Script_Abstract[] */
    private $_scriptList;

    /**
     * @param CM_OutputStream_Interface|null $output
     */
    public function __construct(CM_OutputStream_Interface $output = null) {
        if (null === $output) {
            $output = new CM_OutputStream_Null();
        }
        $this->_output = $output;
        $this->_scriptList = [];
    }

    /**
     * @param CM_Provision_Script_Abstract $script
     */
    public function registerScript(CM_Provision_Script_Abstract $script) {
        $this->_scriptList[] = $script;
    }

    /**
     * @param string[] $scriptClassNames
     */
    public function registerScriptFromClassNames(array $scriptClassNames) {
        foreach ($scriptClassNames as $scriptClassName) {
            $this->registerScript(new $scriptClassName($this->getServiceManager()));
        }
    }

    public function load() {
        $scriptList = $this->_getScriptList();
        foreach ($scriptList as $setupScript) {
            if ($setupScript->shouldBeLoaded()) {
                $this->_output->writeln('  Loading ' . $setupScript->getName() . '…');
                $setupScript->load($this->_output);
            }
        }
    }

    public function unload() {
        $scriptList = $this->_getScriptListUnloadable();
        foreach ($scriptList as $setupScript) {
            if ($setupScript->shouldBeUnloaded()) {
                $this->_output->writeln('  Unloading ' . $setupScript->getName() . '…');
                $setupScript->unload($this->_output);
            }
        }
    }

    public function reload() {
        $scriptList = $this->_getScriptList();
        foreach ($scriptList as $setupScript) {
            if ($setupScript->shouldBeLoaded()) {
                $this->_output->writeln('  Loading ' . $setupScript->getName() . '…');
                $setupScript->load($this->_output);
            } elseif ($setupScript instanceof CM_Provision_Script_UnloadableInterface) {
                /** @var $setupScript CM_Provision_Script_Abstract */
                $this->_output->writeln('  Reloading ' . $setupScript->getName() . '…');
                $setupScript->reload($this->_output);
            }
        }
    }

    /**
     * @return CM_Provision_Script_Abstract[]
     */
    protected function _getScriptList() {
        $scriptList = $this->_scriptList;
        $runLevelList = \Functional\map($this->_scriptList, function (CM_Provision_Script_Abstract $script) {
            return $script->getRunLevel();
        });
        array_multisort($runLevelList, $scriptList);

        return $scriptList;
    }

    /**
     * @return CM_Provision_Script_UnloadableInterface[]|CM_Provision_Script_Abstract[]
     */
    protected function _getScriptListUnloadable() {
        $scriptList = $this->_getScriptList();
        $scriptList = \Functional\select($scriptList, function (CM_Provision_Script_Abstract $script) {
            return $script instanceof CM_Provision_Script_UnloadableInterface;
        });
        return array_reverse($scriptList);
    }
}
