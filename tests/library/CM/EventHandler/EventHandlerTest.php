<?php

class CM_EventHandler_EventHandlerTest extends CMTest_TestCase {

  public static $_foo;
  public static $_counter;

  public function testBind() {
    $counter = 0;
    $eventHandler = new CM_EventHandler_EventHandler();
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
    $eventHandler = new CM_EventHandler_EventHandler();
    $eventHandler->bind('foo', function ($requiredArgument) {
      // Do nothing
    });
    $eventHandler->trigger('foo');
  }

  public function testUnbind() {
    $counter = 0;
    $eventHandler = new CM_EventHandler_EventHandler();
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
    $eventHandler = new CM_EventHandler_EventHandler();
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

  public function testBindJob() {
    $eventHandler = new CM_EventHandler_EventHandler();
    self::$_foo = '';
    $eventHandler->bindJob('foo', new CM_JobMock_1(), array('text' => 'bar'));
    $eventHandler->trigger('foo');
    $this->assertEquals('bar', self::$_foo);

    self::$_counter = 0;
    $eventHandler->bindJob('foo', new CM_JobMock_2());
    $eventHandler->bindJob('foo', new CM_JobMock_3(), array('i' => 2));
    $eventHandler->bindJob('foo', new CM_JobMock_4(), array('a' => 4));
    $eventHandler->trigger('foo', array('i' => 8));
    $this->assertEquals('barbar', self::$_foo);
    $this->assertEquals(13, self::$_counter);

    $eventHandler->trigger('foo', array('text' => 'eclan'));
    $this->assertEquals(20, self::$_counter);
    $this->assertEquals('barbareclan', self::$_foo);

    try {
      $eventHandler->trigger('nonExistentEvent');
      $this->assertTrue(true);
    } catch (Exception $ex) {
      $this->fail('Cant trigger nonexistent events');
    }
  }
}

class CM_JobMock_1 extends CM_Jobdistribution_Job_Abstract {

  protected function _execute(CM_Params $params) {
    CM_EventHandler_EventHandlerTest::$_foo .= $params->getString('text');
  }
}

class CM_JobMock_2 extends CM_Jobdistribution_Job_Abstract {

  protected function _execute(CM_Params $params) {
    CM_EventHandler_EventHandlerTest::$_counter++;
  }
}

class CM_JobMock_3 extends CM_Jobdistribution_Job_Abstract {

  protected function _execute(CM_Params $params) {
    CM_EventHandler_EventHandlerTest::$_counter += $params->getInt('i');
  }
}

class CM_JobMock_4 extends CM_Jobdistribution_Job_Abstract {

  protected function _execute(CM_Params $params) {
    CM_EventHandler_EventHandlerTest::$_counter += $params->getInt('a');
  }
}
