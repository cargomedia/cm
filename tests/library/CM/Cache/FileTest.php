<?php

class CM_Cache_FileTest extends CMTest_TestCase {

	public function testGetSet() {
		$this->assertFalse(CM_Cache_File::get('foo'));

		CM_Cache_File::set('foo', 'bar');
		$this->assertSame('bar', CM_Cache_File::get('foo'));
	}

	/**
	 * @expectedException CM_Exception_NotImplemented
	 */
	public function testSetLifetime() {
		CM_Cache_File::set('foo', 'bar', 500);
	}

	public function testDelete() {
		CM_Cache_File::set('foo', 'bar');
		$this->assertSame('bar', CM_Cache_File::get('foo'));

		CM_Cache_File::delete('foo');
		$this->assertFalse(CM_Cache_File::get('foo'));
	}

	public function testFlush() {
		CM_Cache_File::set('foo', 'bar');
		$this->assertSame('bar', CM_Cache_File::get('foo'));

		CM_Cache_File::flush();
		$this->assertFalse(CM_Cache_File::get('foo'));
	}
}
