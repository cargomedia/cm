<?php

class CM_CacheTest extends CMTest_TestCase {

	public function testKeys() {
		CM_Cache_Shared::set('key1', 'data1');
		CM_Cache_Shared::set('key2', 'data2');
		$this->assertEquals('data1', CM_Cache_Shared::get('key1'));
		$this->assertEquals('data2', CM_Cache_Shared::get('key2'));

		$this->assertFalse(CM_Cache_Shared::get('keyNonexistent'));

		CM_Cache_Shared::delete('key1');
		$this->assertFalse(CM_Cache_Shared::get('key1'));
		$this->assertEquals('data2', CM_Cache_Shared::get('key2'));
	}

	public function testTagged() {
		CM_Cache_Shared::setTagged('tag1', 'key1', 'data1');
		CM_Cache_Shared::setTagged('tag1', 'key2', 'data2');
		CM_Cache_Shared::setTagged('tag2', 'key3', 'data3');
		$this->assertEquals('data1', CM_Cache_Shared::getTagged('tag1', 'key1'));
		$this->assertEquals('data2', CM_Cache_Shared::getTagged('tag1', 'key2'));
		$this->assertEquals('data3', CM_Cache_Shared::getTagged('tag2', 'key3'));

		$this->assertFalse(CM_Cache_Shared::getTagged('tag1', 'keyNonexistent'));
		$this->assertFalse(CM_Cache_Shared::getTagged('tagNonexistent', 'key1'));

		CM_Cache_Shared::deleteTag('tag1');
		$this->assertFalse(CM_Cache_Shared::getTagged('tag1', 'key1'));
		$this->assertFalse(CM_Cache_Shared::getTagged('tag1', 'key2'));
		$this->assertEquals('data3', CM_Cache_Shared::getTagged('tag2', 'key3'));
	}
}
