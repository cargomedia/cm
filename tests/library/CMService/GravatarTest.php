<?php

class CMService_GravatarTest extends CMTest_TestCase {

	public function testGetUrl() {
		$this->assertSame('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0', CMService_Gravatar::getUrl('test@example.com'));
		$this->assertSame('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0?s=140', CMService_Gravatar::getUrl('test@example.com', 140));
		$this->assertSame('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0?d=http%3A%2F%2Fexample.com%2Fdefault.jpg', CMService_Gravatar::getUrl('test@example.com', null, 'http://example.com/default.jpg'));
		$this->assertSame('https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0?s=140&d=http%3A%2F%2Fexample.com%2Fdefault.jpg', CMService_Gravatar::getUrl('test@example.com', 140, 'http://example.com/default.jpg'));

		$this->assertSame('', CMService_Gravatar::getUrl(null));
		$this->assertSame('', CMService_Gravatar::getUrl(null, 140));
		$this->assertSame('http://example.com/default.jpg', CMService_Gravatar::getUrl(null, null, 'http://example.com/default.jpg'));
		$this->assertSame('http://example.com/default.jpg', CMService_Gravatar::getUrl(null, 140, 'http://example.com/default.jpg'));
	}
}
