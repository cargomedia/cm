<?php

abstract class CM_Jobdistribution_Job_Abstract extends CM_Class_Abstract {

    /** @var CM_Params */
    private $_params;

    /**
     * @param CM_Params|null $params
     */
    public function __construct(CM_Params $params = null) {
        if (null === $params) {
            $params = CM_Params::factory();
        }
        $this->_params = $params;
    }

    /**
     * @param CM_Params $params
     * @return mixed
     */
    abstract protected function _execute(CM_Params $params);

    /**
     * @return CM_Params
     */
    public function getParams() {
        return $this->_params;
    }

    /**
     * @return CM_Jobdistribution_Priority
     */
    public function getPriority() {
        return new CM_Jobdistribution_Priority('normal');
    }

    /**
     * @return mixed
     * @throws Exception
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

    public function queue() { //TODO delete it

    }

    public function run() { //TODO delete it

    }
}
