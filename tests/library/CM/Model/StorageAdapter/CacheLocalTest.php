<?php

class CM_Model_StorageAdapter_CacheLocalTest extends CMTest_TestCase {

  public static function setupBeforeClass() {
  }

  public function tearDown() {
    CM_Cache_Local::getInstance()->flush();
  }

  public function testLoadMultiple() {
    if (!ini_get('apc.enable_cli')) {
      $this->markTestSkipped('APC must be enabled for the cli for this test to work');
    }
    CM_Config::get()->CM_Model_Abstract = new stdClass();
    CM_Config::get()->CM_Model_Abstract->types = array(
      1 => 'CMTest_ModelMock_1',
      2 => 'CMTest_ModelMock_2',
    );
    $adapter = new CM_Model_StorageAdapter_CacheLocal();
    $id1 = 1;
    $id2 = 2;
    $id3 = 3;
    $id4 = 1;
    $id5 = 2;
    $dataSet = array();
    $dataSet[] = array('type' => 1, 'id' => array('id' => $id1), 'data' => array('foo' => 'foo1', 'bar' => 1));
    $dataSet[] = array('type' => 1, 'id' => array('id' => $id2), 'data' => array('foo' => 'foo2', 'bar' => 2));
    $dataSet[] = array('type' => 1, 'id' => array('id' => $id3), 'data' => array('foo' => 'foo3', 'bar' => 3));
    $dataSet[] = array('type' => 2, 'id' => array('id' => $id4), 'data' => array('foo' => 'foo4', 'bar' => 4));
    $dataSet[] = array('type' => 2, 'id' => array('id' => $id5), 'data' => array('foo' => 'foo5', 'bar' => 5));
    foreach ($dataSet as $data) {
      $adapter->save($data['type'], $data['id'], $data['data']);
    }

    $idsTypes = array(
      'a'   => array('type' => 1, 'id' => array('id' => $id1)),
      2     => array('type' => 2, 'id' => array('id' => $id4)),
      'foo' => array('type' => 1, 'id' => array('id' => $id2)),
    );
    $expected = array(
      'a'   => array('foo' => 'foo1', 'bar' => 1),
      2     => array('foo' => 'foo4', 'bar' => 4),
      'foo' => array('foo' => 'foo2', 'bar' => 2));

    $values = $adapter->loadMultiple($idsTypes);
    $this->assertSame(3, count($values));
    $this->assertSame($expected, $values);
  }
}
