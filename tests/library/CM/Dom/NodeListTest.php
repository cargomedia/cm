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

	public function testGetAttribute() {
		$list = new CM_Dom_NodeList('<div foo="bar"><div foo="foo"></div></div>');
		$this->assertSame('bar', $list->findElement('div')->getAttribute('foo'));
		$this->assertNull($list->getAttribute('bar'));
	}

	public function testGetAttributeList() {
		$list = new CM_Dom_NodeList('<div foo="bar" bar="foo"></div>');
		$this->assertSame(array('foo' => 'bar', 'bar' => 'foo'), $list->findElement('div')->getAttributeList());
	}

	public function testFindElement(){
		$list = new CM_Dom_NodeList('<div foo="bar">lorem ipsum dolor <p foo="foo">lorem ipsum</p></div>');
		$this->assertSame('lorem ipsum', $list->findElement('p')->getText());
	}

	public function testGetChildren(){
		$list = new CM_Dom_NodeList('<div><span foo="bar">lorem ipsum dolor</span><p foo="foo">lorem ipsum</p><span>test</span><a></a></div>');
		$children = $list->findElement('div')->getChildren();
		$this->assertSame('lorem ipsum dolor', $children[0]->getText());
		$this->assertSame('lorem ipsum', $children[1]->getText());
		$this->assertSame('test', $children[2]->getText());
		$this->assertSame('', $children[3]->getText());
	}
}
