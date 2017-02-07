<?php

namespace CM\Test\Transactions;

use CM\Transactions\Rollback;
use CM\Transactions\Transaction;
use Mocka\FunctionMock;

class TransactionTest extends \CMTest_TestCase {

    public function testAdd() {
        $rollback = $this->mockObject(Rollback::class);
        /** @var Transaction $transaction */
        $transaction = $this->mockObject(Transaction::class, [$rollback]);

        $add = $rollback->mockMethod('add');
        $function = new FunctionMock();
        $transaction->addRollback($function);
        $this->assertSame(1, $add->getCallCount());
        $this->assertSame([$function], $add->getLastCall()->getArguments());
    }

    public function testRun() {
        $rollback = $this->mockObject(Rollback::class);
        /** @var Transaction $transaction */
        $transaction = $this->mockObject(Transaction::class, [$rollback]);

        $run = $rollback->mockMethod('run');
        $transaction->rollback();
        $this->assertSame(1, $run->getCallCount());
    }
    
} 
