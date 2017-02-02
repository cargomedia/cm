<?php

class CM_Migration_Manager implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var string[] */
    private $_modules;

    /**
     * @param CM_Service_Manager $serviceManager
     * @param string[]           $modules
     */
    public function __construct(CM_Service_Manager $serviceManager, array $modules) {
        $this->setServiceManager($serviceManager);
        $this->_modules = $modules;
    }

    /**
     * @return CM_Migration_Loader
     */
    public function getLoader() {
        return new CM_Migration_Loader($this->getServiceManager(), $this->_getMigrationPaths());
    }

    /**
     * @return array
     */
    protected function _getMigrationPaths() {
        $paths = [
            self::getMigrationPathByModule(),
        ];
        foreach ($this->_modules as $moduleName) {
            $paths[] = self::getMigrationPathByModule($moduleName);
        }
        return $paths;
    }

    /**
     * @param string|null $moduleName
     * @return string
     */
    public static function getMigrationPathByModule($moduleName = null) {
        $modulePath = null !== $moduleName ? CM_Util::getModulePath((string) $moduleName) : DIR_ROOT;
        return join(DIRECTORY_SEPARATOR, [$modulePath, 'resources', 'migration']);
    }
}
