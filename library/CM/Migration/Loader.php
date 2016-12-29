<?php

class CM_Migration_Loader implements CM_Service_ManagerAwareInterface {

    const MIGRATION_DIR = 'migration';

    use CM_Service_ManagerAwareTrait;

    /**
     * @param CM_Service_Manager $serviceManager
     */
    public function __construct(CM_Service_Manager $serviceManager) {
        $this->setServiceManager($serviceManager);
    }

    /**
     * @param string $name
     * @return CM_Migration_Script|null
     */
    public function findScript($name) {
        return \Functional\first($this->getScriptList(), function (CM_Migration_Script $script) use ($name) {
            return $name === $script->getName();
        });
    }

    /**
     * return CM_Migration_ScriptIterator
     */
    public function getScriptList() {
        $scripts = CM_Util::getResourceFiles(self::MIGRATION_DIR . DIRECTORY_SEPARATOR . '*.php');
        $this->_loadMigrationScripts($scripts);
        return new CM_Migration_ScriptIterator($scripts, function (CM_File $script) {
            return $this->_getMigrationScript($script);
        });
    }

    /**
     * @param CM_File $script
     * @return CM_Migration_Script
     */
    protected function _getMigrationScript(CM_File $script) {
        $className = sprintf('CM_Migration_Script_%s', $script->getFileNameWithoutExtension());
        return new $className($this->getServiceManager());
    }

    /**
     * @param CM_File[] $scripts
     */
    protected function _loadMigrationScripts(array $scripts) {
        foreach ($scripts as $script) {
            require_once($script->getPath());
        }
    }
}
