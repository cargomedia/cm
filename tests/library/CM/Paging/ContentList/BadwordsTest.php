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
		$this->assertSame('#\b(?:\S*bad\.com\S*|\S*foo[^A-Za-z]*bar\S*|\S*superbad\S*)\b#i', $this->_paging->toRegex());

		$this->_paging->remove('bad.com');
		$this->_paging->remove('superbad');
		$this->_paging->remove('foo*bar');

		$this->assertSame('#\z.#', $this->_paging->toRegex());
	}

	public function testToRegexList() {
		$this->assertSame(array(
			'bad.com'  => '#\b\S*bad\.com\S*\b#i',
			'foo*bar'  => '#\b\S*foo[^A-Za-z]*bar\S*\b#i',
			'superbad' => '#\b\S*superbad\S*\b#i',
		), $this->_paging->toRegexList());
	}
}
