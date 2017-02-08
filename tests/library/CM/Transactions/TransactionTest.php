<?php

namespace CM\Test\Transactions;

use CM\Functional\FunctionSequence;
use CM\Transactions\Transaction;
use Mocka\FunctionMock;

class TransactionTest extends \CMTest_TestCase {

    public function testAdd() {
        $sequence = $this->mockObject(FunctionSequence::class);
        /** @var Transaction $transaction */
        $transaction = $this->mockObject(Transaction::class, [$sequence]);

        $add = $sequence->mockMethod('add');
        $function = new FunctionMock();
        $transaction->addRollback($function);
        $this->assertSame(1, $add->getCallCount());
        $this->assertSame([$function], $add->getLastCall()->getArguments());
    }

    public function testRollback() {
        $sequence = $this->mockObject(FunctionSequence::class);
        /** @var Transaction $transaction */
        $transaction = $this->mockObject(Transaction::class, [$sequence]);

        $runInReverse = $sequence->mockMethod('runInReverse');
        $transaction->rollback();
        $this->assertSame(1, $runInReverse->getCallCount());
    }
    
} 
