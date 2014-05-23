<?php

class CM_Process_ForkHandler {

    /** @var Closure */
    private $_workload;

    /**
     * @param Closure $workload
     */
    public function __construct(Closure $workload) {
        $this->_workload = $workload;
    }

    /**
     * @return Closure
     */
    public function getWorkload() {
        return $this->_workload;
    }
}
