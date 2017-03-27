<?php

class CM_ModelEvents_BindingTest extends CMTest_TestCase {

    public function testBindModelCreated() {
        $model = $this->mockObject(CM_Model_Abstract::class);
        $eventHandler = new CM_EventHandler_EventHandler();
        $classname = get_class($model);
        $counter = 0;

        $binding = new CM_ModelEvents_Binding();
        $binding->bindModelCreated($eventHandler, $classname, function () use (&$counter) {
            $counter++;
        });
        $eventHandler->trigger("model-{$classname}-created");
        $this->assertSame(1, $counter);

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($binding, $eventHandler) {
            $binding->bindModelCreated($eventHandler, 'InvalidClass', function () {
            });
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Not a model', $exception->getMessage());
        $this->assertSame(['className' => 'InvalidClass'], $exception->getMetaInfo());
    }

    public function testBindModelChanged() {
        $model = $this->mockObject(CM_Model_Abstract::class);
        $eventHandler = new CM_EventHandler_EventHandler();
        $classname = get_class($model);
        $counter1 = 0;

        $binding = new CM_ModelEvents_Binding();
        $binding->bindModelChanged($eventHandler, $classname, function () use (&$counter1) {
            $counter1++;
        });
        $eventHandler->trigger("model-{$classname}-changed");
        $this->assertSame(1, $counter1);

        $counter2 = 0;
        $binding->bindModelChanged($eventHandler, $classname, function () use (&$counter2) {
            $counter2++;
        }, 'foo');
        $eventHandler->trigger("model-{$classname}-changed-foo");
        $this->assertSame(1, $counter2);

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($binding, $eventHandler) {
            $binding->bindModelChanged($eventHandler, 'InvalidClass', function () {
            });
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Not a model', $exception->getMessage());
        $this->assertSame(['className' => 'InvalidClass'], $exception->getMetaInfo());
    }

    public function testBindModelDeleted() {
        $model = $this->mockObject(CM_Model_Abstract::class);
        $eventHandler = new CM_EventHandler_EventHandler();
        $classname = get_class($model);
        $counter = 0;

        $binding = new CM_ModelEvents_Binding();
        $binding->bindModelDeleted($eventHandler, $classname, function () use (&$counter) {
            $counter++;
        });
        $eventHandler->trigger("model-{$classname}-deleted");
        $this->assertSame(1, $counter);

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($binding, $eventHandler) {
            $binding->bindModelDeleted($eventHandler, 'InvalidClass', function () {
            });
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Not a model', $exception->getMessage());
        $this->assertSame(['className' => 'InvalidClass'], $exception->getMetaInfo());
    }
}
