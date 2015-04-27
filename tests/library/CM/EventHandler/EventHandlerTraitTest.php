<?php

class CM_EventHandler_EventHandlerTraitTest extends CMTest_TestCase {

    public function testBind() {
        $counter = 0;
        $eventHandler = $this->mockTrait('CM_EventHandler_EventHandlerTrait')->newInstance();
        /** @var CM_EventHandler_EventHandlerTrait $eventHandler */
        $eventHandler->bind('foo', function ($value) use (&$counter) {
            $counter += $value;
        });
        $eventHandler->trigger('bar');
        $this->assertSame(0, $counter);
        $eventHandler->trigger('foo', 1);
        $this->assertSame(1, $counter);
        $eventHandler->bind('foo', function () use (&$counter) {
            $counter--;
        });
        $eventHandler->trigger('foo', 2);
        $this->assertSame(2, $counter);
    }

    /**
     * @expectedException ErrorException
     * @expectedExceptionMessage Missing argument 1
     */
    public function testBindMissingArguments() {
        $eventHandler = $this->mockTrait('CM_EventHandler_EventHandlerTrait')->newInstance();
        /** @var CM_EventHandler_EventHandlerTrait $eventHandler */
        $eventHandler->bind('foo', function ($requiredArgument) {
            // Do nothing
        });
        $eventHandler->trigger('foo');
    }

    public function testUnbind() {
        $counter = 0;
        $eventHandler = $this->mockTrait('CM_EventHandler_EventHandlerTrait')->newInstance();
        /** @var CM_EventHandler_EventHandlerTrait $eventHandler */
        $callback = function () use (&$counter) {
            $counter++;
        };
        $eventHandler->bind('foo', $callback);
        $eventHandler->trigger('foo');
        $this->assertSame(1, $counter);

        $eventHandler->unbind('foo', function () {
        });
        $eventHandler->trigger('foo');
        $this->assertSame(2, $counter);

        $eventHandler->unbind('foo', $callback);
        $eventHandler->trigger('foo');
        $this->assertSame(2, $counter);
    }

    public function testUnbindAll() {
        $counter = 0;
        $eventHandler = $this->mockTrait('CM_EventHandler_EventHandlerTrait')->newInstance();
        /** @var CM_EventHandler_EventHandlerTrait $eventHandler */
        $eventHandler->bind('foo', function () use (&$counter) {
            $counter++;
        });
        $this->assertSame(0, $counter);
        $eventHandler->trigger('foo');
        $this->assertSame(1, $counter);
        $eventHandler->unbind('foo');
        $eventHandler->trigger('foo');
        $this->assertSame(1, $counter);
    }
}
