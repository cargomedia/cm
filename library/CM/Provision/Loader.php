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
            $this->registerScript(new $scriptClassName());
        }
    }

    public function load() {
        foreach ($this->_getScriptList() as $setupScript) {
            $this->_output->writeln('  Loading ' . $setupScript->getName() . '...');
            $setupScript->load($this->getServiceManager(), $this->_output);
        }
    }

    public function unload() {
        /** @var CM_Provision_Script_Abstract[] $scriptList */
        $scriptList = array_reverse($this->_getScriptList());
        foreach ($scriptList as $setupScript) {
            $this->_output->writeln('  Unloading ' . $setupScript->getName() . '...');
            $setupScript->unload($this->getServiceManager(), $this->_output);
        }
    }

    /**
     * @return bool
     */
    public function isAnyScriptLoaded() {
        return \Functional\some($this->_getScriptList(), function (CM_Provision_Script_Abstract $script) {
            return $script->isLoaded($this->getServiceManager());
        });
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
}
