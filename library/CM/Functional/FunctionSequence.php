<?php

namespace CM\Functional;

class FunctionSequence {
    
    /** @var callable[] */
    private $_list;

    public function __construct() {
        $this->_list = [];
    }

    /**
     * @param callable $callable
     */
    public function add(callable $callable) {
        $this->_list[] = $callable;
    }

    /**
     * @return array
     */
    public function run() {
        return $this->_runCallables($this->_list);
    }

    /**
     * @return array
     */
    public function runInReverse() {
        return $this->_runCallables(array_reverse($this->_list));
    }

    /**
     * @param callable[] $callableList
     * @return array
     */
    protected function _runCallables($callableList) {
        $results = [];
        foreach ($callableList as $callable) {
            $results[] = call_user_func($callable);
        }
        return $results;
    }
}
