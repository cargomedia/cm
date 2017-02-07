<?php

namespace CM\Transactions;

use CM\Functional\FunctionSequence;

class Rollback {

    /**
     * @var FunctionSequence
     */
    private $_sequence;

    /**
     * @param FunctionSequence|null $sequence
     */
    public function __construct(FunctionSequence $sequence = null) {
        if (null === $sequence) {
            $sequence = new FunctionSequence();
        }
        $this->_sequence = $sequence;
    }

    /**
     * @param callable $rollback
     */
    public function add(callable $rollback) {
        $this->_sequence->add($rollback);
    }

    public function run() {
        $this->_sequence->runInReverse();
    }
}
