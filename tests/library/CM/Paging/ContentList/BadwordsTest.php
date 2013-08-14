<?php

class CM_Paging_ContentList_BadwordsTest extends CMTest_TestCase {

	/** @var CM_Paging_ContentList_Badwords */
	private $_paging;

	public function setUp() {
		$this->_paging = new CM_Paging_ContentList_Badwords();
		$this->_paging->add('bad.com');
		$this->_paging->add('superbad');
		$this->_paging->add('foo*bar');
	}

	public function testIsMatch(){
		$this->assertTrue($this->_paging->isMatch('bad.com'));
		$this->assertTrue($this->_paging->isMatch('BAD.com'));
		$this->assertTrue($this->_paging->isMatch('sub.bad.com'));
		$this->assertTrue($this->_paging->isMatch('bad.com-foo.de'));
		$this->assertFalse($this->_paging->isMatch('evil.com'));
		$this->assertTrue($this->_paging->isMatch('foo-bar'));

		$this->_paging->add('evil.com');
		$this->assertTrue($this->_paging->isMatch('evil.com'));
	}

	public function testGetMatch(){
		$this->assertSame('foobar', $this->_paging->getMatch('hallo foo-bar world.'));
	}

	public function testReplaceMatch(){
		$this->assertSame('hallo … world.', $this->_paging->replaceMatch('hallo foo-bar world.', '…'));
		$this->assertSame('hallo $1 \1 world.', $this->_paging->replaceMatch('hallo foo-bar world.', '$1 \1'));
	}
}
