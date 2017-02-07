<?php

namespace CM\Transactions;

class Transaction {

    /** @var Rollback */
    private $_rollback;

    /**
     * @param Rollback|null $rollback
     */
    public function __construct(Rollback $rollback = null) {
        if (null === $rollback) {
            $rollback = new Rollback();
        }
        $this->_rollback = $rollback;
    }

    /**
     * @param callable $rollback
     */
    public function addRollback(callable $rollback) {
        $this->_rollback->add($rollback);
    }

    public function rollback() {
        $this->_rollback->run();
    }
}
