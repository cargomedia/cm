<?php

class CM_Setup_Loader implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var CM_OutputStream_Interface */
    private $_output;

    /**
     * @param CM_OutputStream_Interface|null $output
     */
    public function __construct(CM_OutputStream_Interface $output = null) {
        if (null === $output) {
            $output = new CM_OutputStream_Null();
        }
        $this->_output = $output;
    }

    public function load() {
        $setupScriptsGrouped = \Functional\group($this->_getSetupScriptList(), function (CM_Setup_Script_Abstract $setupScript) {
            return $setupScript->getNamespace();
        });

        /** @var CM_Setup_Script_Abstract[] $moduleSetupScriptList */
        foreach ($setupScriptsGrouped as $moduleSetupScriptList) {
            $moduleSetupScriptOrder = \Functional\invoke($moduleSetupScriptList, 'getOrder');
            array_multisort($moduleSetupScriptOrder, $moduleSetupScriptList);

            foreach ($moduleSetupScriptList as $setupScript) {
                $this->_output->writeln('Loading ' . $setupScript->getName() . '...');
                $setupScript->load($this->getServiceManager());
            }
        }
    }

    /**
     * @return CM_Setup_Script_Abstract[]
     */
    protected function _getSetupScriptList() {
        $setupScriptClassNameList = CM_Util::getClassChildren('CM_Setup_Script_Abstract');
        return \Functional\map($setupScriptClassNameList, function ($className) {
            return new $className();
        });
    }
}
