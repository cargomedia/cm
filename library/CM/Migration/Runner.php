<?php

class CM_Migration_Runner implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var CM_Migration_UpgradableInterface */
    private $_script;

    /** @var CM_Migration_Model|null */
    private $_record;

    /**
     * @param CM_Migration_UpgradableInterface $script
     * @param CM_Service_Manager               $serviceManager
     */
    public function __construct(CM_Migration_UpgradableInterface $script, CM_Service_Manager $serviceManager) {
        $this->setServiceManager($serviceManager);
        $this->_script = $script;
        $this->_record = null;
    }

    /**
     * @param CM_OutputStream_Interface $output
     */
    public function load(CM_OutputStream_Interface $output) {
        $this->_getScript()->up($output);
        $this->_getRecord()->setExecutedAt(new DateTime());
    }

    /**
     * @return string
     */
    public function getName() {
        $reflector = new ReflectionClass($this->_getScript());
        $file = new CM_File($reflector->getFileName());
        return $file->getFileNameWithoutExtension();
    }

    /**
     * @return string|null
     */
    public function getDescription() {
        $reflector = new ReflectionClass($this->_getScript());
        $doc = $reflector->getMethod('up')->getDocComment();
        if ($doc && preg_match('/\*[ ]+([\S ]+)/', $doc, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * @return string
     */
    public function getScriptClassName() {
        return get_class($this->_getScript());
    }

    /**
     * @return bool
     */
    public function shouldBeLoaded() {
        return !$this->_isLoaded();
    }

    /**
     * @return bool
     */
    protected function _isLoaded() {
        return $this->_getRecord()->hasExecutedAt();
    }

    /**
     * @return CM_Migration_UpgradableInterface
     */
    protected function _getScript() {
        return $this->_script;
    }

    /**
     * @return CM_Migration_Model
     */
    protected function _getRecord() {
        if (!$this->_record) {
            $this->_record = $this->_fetchRecord();
        }
        return $this->_record;
    }

    /**
     * @return CM_Migration_Model
     */
    protected function _fetchRecord() {
        $name = $this->getName();
        $record = CM_Migration_Model::findByName($name);
        if (!$record) {
            $record = CM_Migration_Model::create($name);
        }
        return $record;
    }
}
