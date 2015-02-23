<?php

abstract class CM_Jobdistribution_Job_Abstract extends CM_Class_Abstract {

    /** @var CM_Params */
    private $_params;

    /**
     * @param CM_Params $params
     * @return mixed
     */
    abstract protected function _execute(CM_Params $params);

    /**
     * @param array $params
     */
    public function __construct(array $params = null) {
        $this->_params = new CM_Params($params);
    }

    /**
     * @return CM_Params
     */
    public function getParams() {
        return $this->_params;
    }

    /**
     * @return mixed
     */
    public function execute() {
        return $this->_execute($this->getParams());
    }

    /**
     * @return string
     */
    public function getJobName() {
        return get_class($this);
    }
}
