<?php

abstract class CM_Provision_Script_Migration extends CM_Provision_Script_Abstract {

    use CM_Provision_Script_IsLoadedTrait;

    /** @var CM_Model_Migration|null */
    private $_record;

    public function __construct(CM_Service_Manager $serviceManager) {
        $this->_record = null;
        parent::__construct($serviceManager);
    }

    public function load(CM_OutputStream_Interface $output) {
        if ($this->shouldBeLoaded()) {
            $output->write(sprintf('- execute "%s" update script…', $this->getName()));
            try {
                $this->up();
            } catch (Exception $e) {
                $output->writeln('failed');
                throw $e;
            }
            $output->writeln('done');
            $this->_getRecord()->setExecStamp((new DateTime())->setTimestamp(time()));
        } else {
            $output->writeln(sprintf('- "%s" already loaded', $this->getName()));
        }
    }

    abstract public function up();

    protected function _isLoaded() {
        return $this->_getRecord()->hasExecStamp();
    }

    /**
     * @return CM_Model_Migration
     */
    protected function _getRecord() {
        if (!$this->_record) {
            $this->_record = $this->_fetchRecord();
        }
        return $this->_record;
    }

    /**
     * @return CM_Model_Migration
     */
    protected function _fetchRecord() {
        $record = CM_Model_Migration::findByName($this->getName());
        if (!$record) {
            $record = CM_Model_Migration::create($this->getName());
        }
        return $record;
    }
}
