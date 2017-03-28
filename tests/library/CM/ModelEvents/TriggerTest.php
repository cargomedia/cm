<?php

class CM_ModelEvents_TriggerTest extends CMTest_TestCase {

    public function testTriggerModelCreated() {
        /** @var CM_Model_Abstract|\Mocka\AbstractClassTrait $model */
        $model = $this->mockObject(CM_Model_Abstract::class);
        $model->mockMethod('getId')->set(10);

        /** @var CM_EventHandler_EventHandlerInterface|\Mocka\AbstractClassTrait $eventHandler */
        $eventHandler = $this->mockInterface(CM_EventHandler_EventHandlerInterface::class)->newInstanceWithoutConstructor();
        $mockTrigger = $eventHandler->mockMethod('trigger')->set(function ($event, $param1 = null, $param2 = null) use ($model) {
            $mockClassname = get_class($model);
            $this->assertSame("model-{$mockClassname}-created", $event);
            $this->assertEquals($model, $param1);
            $this->assertSame([], $param2);
        });

        $trigger = new CM_ModelEvents_Trigger();
        $trigger->triggerModelCreated($eventHandler, $model);
        $this->assertSame(1, $mockTrigger->getCallCount());
    }

    public function testTriggerModelChanged() {
        /** @var CM_Model_Abstract|\Mocka\AbstractClassTrait $model */
        $model = $this->mockObject(CM_Model_Abstract::class);
        $model->mockMethod('getId')->set(10);

        /** @var CM_EventHandler_EventHandlerInterface|\Mocka\AbstractClassTrait $eventHandler */
        $eventHandler = $this->mockInterface(CM_EventHandler_EventHandlerInterface::class)->newInstanceWithoutConstructor();
        $mockTrigger = $eventHandler->mockMethod('trigger')->set(function ($event, $param1 = null, $param2 = null) use ($model) {
            $mockClassname = get_class($model);
            $this->assertSame("model-{$mockClassname}-changed", $event);
            $this->assertEquals($model, $param1);
            $this->assertSame([], $param2);
        });

        $trigger = new CM_ModelEvents_Trigger();
        $trigger->triggerModelChanged($eventHandler, $model);
        $this->assertSame(1, $mockTrigger->getCallCount());
    }

    public function testTriggerModelChangedProperty() {
        /** @var CM_Model_Abstract|\Mocka\AbstractClassTrait $model */
        $model = $this->mockObject(CM_Model_Abstract::class);
        $model->mockMethod('getId')->set(10);

        /** @var CM_EventHandler_EventHandlerInterface|\Mocka\AbstractClassTrait $eventHandler */
        $eventHandler = $this->mockInterface(CM_EventHandler_EventHandlerInterface::class)->newInstanceWithoutConstructor();
        $mockTrigger = $eventHandler->mockMethod('trigger')->set(function ($event, $param1 = null, $param2 = null) use ($model) {
            $mockClassname = get_class($model);
            $this->assertSame("model-{$mockClassname}-changed-foo", $event);
            $this->assertEquals($model, $param1);
            $this->assertSame(
                [
                    'valueOld' => 'baz',
                    'valueNew' => 'bar',
                ],
                $param2
            );
        });

        $trigger = new CM_ModelEvents_Trigger();
        $trigger->triggerModelChanged($eventHandler, $model, 'foo', 'bar', 'baz');
        $this->assertSame(1, $mockTrigger->getCallCount());
    }

    public function testTriggerModelDeleted() {
        /** @var CM_Model_Abstract|\Mocka\AbstractClassTrait $model */
        $model = $this->mockObject(CM_Model_Abstract::class);
        $model->mockMethod('getId')->set(10);

        /** @var CM_EventHandler_EventHandlerInterface|\Mocka\AbstractClassTrait $eventHandler */
        $eventHandler = $this->mockInterface(CM_EventHandler_EventHandlerInterface::class)->newInstanceWithoutConstructor();
        $mockTrigger = $eventHandler->mockMethod('trigger')->set(function ($event, $param1 = null, $param2 = null) use ($model) {
            $mockClassname = get_class($model);
            $this->assertSame("model-{$mockClassname}-deleted", $event);
            $this->assertEquals($model, $param1);
            $this->assertSame([], $param2);
        });

        $modelTrigger = new CM_ModelEvents_Trigger();
        $modelTrigger->triggerModelDeleted($eventHandler, $model);
        $this->assertSame(1, $mockTrigger->getCallCount());
    }
}
