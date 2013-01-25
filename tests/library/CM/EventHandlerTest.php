<?php

class CM_EventHandlerTest extends CMTest_TestCase{

	public static $_foo;
	public static $_counter;

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function test() {
		$eventHandler = new CM_EventHandler();
		self::$_foo = 'bar';
		$eventHandler->bind('foo', function(CM_Params $params = null) {
			$text = $params->getString('text');
			CM_EventHandlerTest::$_foo .= $text;
		}, array('text' => 'bar'));
		$eventHandler->trigger('foo');
		$this->assertEquals('barbar', self::$_foo);


		self::$_counter = 0;
		$eventHandler->bind('foo', function(CM_Params $params = null) {
			CM_EventHandlerTest::$_counter++;
		});
		$eventHandler->bind('foo', function(CM_Params $params = null) {
			CM_EventHandlerTest::$_counter += $params->getInt('i');
		}, array('i' => 2));
		$eventHandler->bind('foo', function(CM_Params $params = null) {
			CM_EventHandlerTest::$_counter += $params->getInt('a');
		}, array('a' => 4));
		$eventHandler->trigger('foo', array('i' => 8));
		$this->assertEquals('barbarbar', self::$_foo);
		$this->assertEquals(13, self::$_counter);


		$eventHandler->trigger('foo');
		$this->assertEquals(20, self::$_counter);

		try {
			$eventHandler->trigger('nonExistentEvent');
			$this->assertTrue(true);
		} catch (Exception $ex) {
			$this->fail('Cant trigger nonexistent events');
		}

	}

}
