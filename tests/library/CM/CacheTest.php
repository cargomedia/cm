<?php
require_once dirname(__FILE__) . '/../../TestCase.php';

class CM_CacheTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}
	
	public function testKeys() {
		CM_Cache::set('key1', 'data1');
		CM_Cache::set('key2', 'data2');
		$this->assertEquals('data1', CM_Cache::get('key1'));
		$this->assertEquals('data2', CM_Cache::get('key2'));
		
		$this->assertFalse(CM_Cache::get('keyNonexistent'));
		
		CM_Cache::delete('key1');
		$this->assertFalse(CM_Cache::get('key1'));
		$this->assertEquals('data2', CM_Cache::get('key2'));
	}

	public function testTagged() {
		CM_Cache::setTagged('tag1', 'key1', 'data1');
		CM_Cache::setTagged('tag1', 'key2', 'data2');
		CM_Cache::setTagged('tag2', 'key3', 'data3');
		$this->assertEquals('data1', CM_Cache::getTagged('tag1', 'key1'));
		$this->assertEquals('data2', CM_Cache::getTagged('tag1', 'key2'));
		$this->assertEquals('data3', CM_Cache::getTagged('tag2', 'key3'));
		
		$this->assertFalse(CM_Cache::getTagged('tag1', 'keyNonexistent'));
		$this->assertFalse(CM_Cache::getTagged('tagNonexistent', 'key1'));
		
		CM_Cache::deleteTag('tag1');
		$this->assertFalse(CM_Cache::getTagged('tag1', 'key1'));
		$this->assertFalse(CM_Cache::getTagged('tag1', 'key2'));
		$this->assertEquals('data3', CM_Cache::getTagged('tag2', 'key3'));
	}
}
