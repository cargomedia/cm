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

	public function testGetText() {
		$list = new CM_Dom_NodeList('<div>hello<strong>world</strong></div>');
		$this->assertSame('helloworld', $list->getText());
	}

	public function testGetTextEncoding() {
		$list = new CM_Dom_NodeList('<div>hello繁體字<strong>world</strong></div>');
		$this->assertSame('hello繁體字world', $list->getText());
	}
}
