<?php

class CM_Dom_NodeListTest extends CMTest_TestCase {

	public function testConstructor() {
		new CM_Dom_NodeList('<html><body><p>hello</p></body></html>');
		new CM_Dom_NodeList('<p>hello</p>');
		new CM_Dom_NodeList('<p>hello');
		$this->assertTrue(true);
	}

	/**
	 * @expectedException CM_Exception_Invalid
	 */
	public function testConstructorInvalid() {
		new CM_Dom_NodeList('<%%%%===**>>> foo');
	}

	public function getText() {
		$list = new CM_Dom_NodeList('<div>hello<strong>world</strong></div>');
		$this->assertSame('hello world', $list->getText());
	}
}
