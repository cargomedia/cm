<?php

abstract class CM_Migration_Script extends CM_Provision_Script_Abstract {

    use CM_Provision_Script_IsLoadedTrait;

    /** @var CM_Migration_Model|null */
    private $_record;

    public function __construct(CM_Service_Manager $serviceManager) {
        $this->_record = null;
        parent::__construct($serviceManager);
    }

    /**
     * @param CM_OutputStream_Interface $output
     * @param boolean|null              $force
     * @throws Exception
     */
    public function load(CM_OutputStream_Interface $output, $force = null) {
        $force = (bool) $force;
        if ($force || $this->shouldBeLoaded()) {
            if ($force && !$this->shouldBeLoaded()) {
                $output->write(sprintf('- reload "%s" update script…', $this->getName()));
            } else {
                $output->write(sprintf('- load "%s" update script…', $this->getName()));
            }
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
