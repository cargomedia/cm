<?php

namespace CM\Test\Functional;

use CM\Functional\FunctionSequence;
use Mocka\FunctionMock;

class FunctionSequenceTest extends \CMTest_TestCase {

    public function testRunCallables() {
        $sequence = $this->mockObject(FunctionSequence::class);
        $function1 = (new FunctionMock())->set(1);
        $function2 = (new FunctionMock())->set(2);

        $results = $this->callProtectedMethod($sequence, '_runCallables', [[$function1, $function2, $function1]]);
        $this->assertSame([1, 2, 1], $results);
        $this->assertSame(2, $function1->getCallCount());
        $this->assertSame(1, $function2->getCallCount());
    }

    public function testAdd() {
        /** @var FunctionSequence $sequence */
        $sequence = $this->mockObject(FunctionSequence::class);
        $function = new FunctionMock();
        $sequence->add($function);
        $this->assertSame(0, $function->getCallCount());
        $sequence->run();
        $this->assertSame(1, $function->getCallCount());
    }

    public function testRunAndRunInReverse() {
        $sequence = $this->mockObject(FunctionSequence::class);
        $runCallables = $sequence->mockMethod('_runCallables');

        /** @var FunctionSequence $sequence */
        $function1 = new FunctionMock();
        $sequence->add($function1);
        $function2 = new FunctionMock();
        $sequence->add($function2);

        $sequence->run();
        $this->assertSame(1, $runCallables->getCallCount());
        $this->assertSame([$function1, $function2] ,$runCallables->getLastCall()->getArgument(0));

        $sequence->runInReverse();
        $this->assertSame(2, $runCallables->getCallCount());
        $this->assertSame([$function2, $function1], $runCallables->getLastCall()->getArgument(0));
    }
}
