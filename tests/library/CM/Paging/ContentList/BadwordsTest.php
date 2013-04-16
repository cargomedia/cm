<?php

class CM_Paging_ContentList_BadwordsTest extends CMTest_TestCase {

	public function testAll() {
		$paging = new CM_Paging_ContentList_Badwords();

		$paging->add('bad.com');

		$this->assertTrue($paging->contains('bad.com'));
		$this->assertTrue($paging->contains('BAD.com'));
		$this->assertFalse($paging->contains('sub.bad.com'));
		$this->assertFalse($paging->contains('bad.com-foo.de'));
		$this->assertFalse($paging->contains('evil.com'));

		$this->assertTrue($paging->contains('bad.com', '/\Q$item\E$/i'));
		$this->assertTrue($paging->contains('BAD.com', '/\Q$item\E$/i'));
		$this->assertTrue($paging->contains('sub.bad.com', '/\Q$item\E$/i'));
		$this->assertFalse($paging->contains('bad.com-foo.de', '/\Q$item\E$/i'));
		$this->assertFalse($paging->contains('evil.com', '/\Q$item\E$/i'));
	}
}
