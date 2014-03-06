<?php

class CM_Cache_Storage_FileTest extends CMTest_TestCase {

  public function testGetSet() {
    $cache = new CM_Cache_Storage_File();
    $this->assertFalse($cache->get('foo'));

    $cache->set('foo', 'bar');
    $this->assertSame('bar', $cache->get('foo'));
  }

  /**
   * @expectedException CM_Exception_NotImplemented
   */
  public function testSetLifetime() {
    $cache = new CM_Cache_Storage_File();
    $cache->set('foo', 'bar', 500);
  }

  public function testDelete() {
    $cache = new CM_Cache_Storage_File();
    $cache->set('foo', 'bar');
    $this->assertSame('bar', $cache->get('foo'));

    $cache->delete('foo');
    $this->assertFalse($cache->get('foo'));
  }

  public function testFlush() {
    $cache = new CM_Cache_Storage_File();
    $cache->set('foo', 'bar');
    $this->assertSame('bar', $cache->get('foo'));

    $cache->flush();
    $this->assertFalse($cache->get('foo'));
  }
}
