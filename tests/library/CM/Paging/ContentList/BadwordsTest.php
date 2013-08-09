<?php

class CM_Paging_ContentList_BadwordsTest extends CMTest_TestCase {

	/** @var CM_Paging_ContentList_Badwords */
	private $_paging;

	public function setUp() {
		$this->_paging = new CM_Paging_ContentList_Badwords();
		$this->_paging->add('bad.com');
		$this->_paging->add('*superbad*');
	}

	public function testAll() {
		$this->assertTrue($this->_paging->contains('bad.com'));
		$this->assertTrue($this->_paging->contains('BAD.com'));
		$this->assertFalse($this->_paging->contains('sub.bad.com'));
		$this->assertFalse($this->_paging->contains('bad.com-foo.de'));
		$this->assertFalse($this->_paging->contains('evil.com'));

		$this->assertTrue($this->_paging->contains('bad.com', '/\Q$item\E$/i'));
		$this->assertTrue($this->_paging->contains('BAD.com', '/\Q$item\E$/i'));
		$this->assertTrue($this->_paging->contains('sub.bad.com', '/\Q$item\E$/i'));
		$this->assertFalse($this->_paging->contains('bad.com-foo.de', '/\Q$item\E$/i'));
		$this->assertFalse($this->_paging->contains('evil.com', '/\Q$item\E$/i'));
	}

	public function testToRegex() {
		$regex = $this->_paging->toRegex();
		$this->assertSame('#\b(?:[^\s]*superbad[^\s]*|bad\.com)\b#i', $regex);
	}

	public function testToRegexList() {
		$regex = $this->_paging->toRegexList();
		$this->assertSame(array('*superbad*' => '#\b[^\s]*superbad[^\s]*\b#i', 'bad.com' => '#\bbad\.com\b#i'), $regex);
	}
}
