<?php

abstract class CM_Migration_Script extends CM_Provision_Script_Abstract {

    use CM_Provision_Script_IsLoadedTrait;

    /** @var CM_Migration_Model|null */
    private $_record;

    public function __construct(CM_Service_Manager $serviceManager) {
        $this->_record = null;
        parent::__construct($serviceManager);
    }

    abstract public function up();

    /**
     * @param CM_OutputStream_Interface|null $output
     */
    public function load(CM_OutputStream_Interface $output = null) {
        $this->up();
        $this->_getRecord()->setExecutedAt(new DateTime());
    }

    /**
     * @return string|null
     */
    public function getDescription() {
        $reflector = new ReflectionClass($this);
        $doc = $reflector->getMethod('up')->getDocComment();
        if ($doc && preg_match('/\*[ ]+([\S ]+)/', $doc, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function getName() {
        return str_replace('CM_Migration_Script_', '', parent::getName());
    }

    protected function _isLoaded() {
        return $this->_getRecord()->hasExecutedAt();
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
        $record = CM_Migration_Model::findByName($this->getName());
        if (!$record) {
            $record = CM_Migration_Model::create($this->getName());
        }
        return $record;
    }
}
