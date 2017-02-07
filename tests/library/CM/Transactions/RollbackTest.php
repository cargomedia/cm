<?php

namespace CM\Test\Transactions;

use CM\Functional\FunctionSequence;
use CM\Transactions\Rollback;
use Mocka\FunctionMock;

class RollbackTest extends \CMTest_TestCase {

    public function testAdd() {
        $sequence = $this->mockObject(FunctionSequence::class);
        /** @var Rollback $rollback */
        $rollback = $this->mockObject(Rollback::class, [$sequence]);

        $add = $sequence->mockMethod('add');
        $function = new FunctionMock();
        $rollback->add($function);
        $this->assertSame(1, $add->getCallCount());
        $this->assertSame([$function], $add->getLastCall()->getArguments());
    }

    public function testRun() {
        $sequence = $this->mockObject(FunctionSequence::class);
        $rollback = new Rollback($sequence);

        $runInReverse = $sequence->mockMethod('runInReverse');
        $rollback->run();
        $this->assertSame(1, $runInReverse->getCallCount());
    }
}
