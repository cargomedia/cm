<?php

namespace CM\Transactions;

use CM\Functional\FunctionSequence;

class Transaction {

    /** @var FunctionSequence */
    private $_rollbacks;

    /**
     * @param FunctionSequence|null $rollbacks
     */
    public function __construct(FunctionSequence $rollbacks = null) {
        if (null === $rollbacks) {
            $rollbacks = new FunctionSequence();
        }
        $this->_rollbacks = $rollbacks;
    }

    /**
     * @param callable $rollback
     */
    public function addRollback(callable $rollback) {
        $this->_rollbacks->add($rollback);
    }

    public function rollback() {
        $this->_rollbacks->runInReverse();
    }
}
