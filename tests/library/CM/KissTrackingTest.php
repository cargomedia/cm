<?php

class CM_KissTrackingTest extends CMTest_TestCase {

  /** @var string */
  private $_filePath;

  public function setUp() {
    CM_Config::get()->CM_KissTracking->enabled = true;
    CM_Config::get()->CM_KissTracking->awsBucketName = 'foo';
    CM_Config::get()->CM_KissTracking->awsFilePrefix = 'bar';

    $this->_filePath = CM_Bootloader::getInstance()->getDirTmp() . 'kisstracking.csv';
  }

  public function tearDown() {
    CMTest_TH::clearEnv();
  }

  public function testProcess() {
    if (getenv('TRAVIS')) {
      $this->markTestSkipped('Disabled on Travis because of a connection issue');
    }
    $kissTracking = $this->getMock('CM_KissTracking', array('_uploadCsv', '_getFileName'));
    $kissTracking->expects($this->any())->method('_getFileName')->will($this->returnValue($this->_filePath));
    /** @var CM_KissTracking $kissTracking */

    $testRecords1 = array(array('test_event_2', 1, null, array('Smart' => true, 'Hired' => 'yes')));
    $time = time();
    foreach ($testRecords1 as $testRecord) {
      CM_KissTracking::getInstance()->track($testRecord[0], $testRecord[1], $testRecord[2], $testRecord[3]);
    }

    $this->assertFileNotExists($this->_filePath);

    $generatedFile = $kissTracking->generateCsv();
    $this->assertFileExists($this->_filePath);
    $string = <<<EOD
Identity,Alias,Timestamp,Event,Prop:Smart,Prop:Hired
1,,{$time},test_event_2,1,yes

EOD;

    $this->assertSame($string, $generatedFile->read());

    $testRecords2 = array(array('test_event_4', 1, null, array('Smart' => true, 'PHP' => 'rocks')));
    $time2 = time();
    foreach ($testRecords2 as $testRecord) {
      CM_KissTracking::getInstance()->track($testRecord[0], $testRecord[1], $testRecord[2], $testRecord[3]);
    }

    $string2 = <<<EOD
Identity,Alias,Timestamp,Event,Prop:Smart,Prop:Hired,Prop:PHP
1,,{$time},test_event_2,1,yes,
1,,{$time2},test_event_4,1,,rocks

EOD;
    $kissTracking->generateCsv();
    $this->assertSame($string2, $generatedFile->read());
  }

  public function testTrackUser() {
    if (getenv('TRAVIS')) {
      $this->markTestSkipped('Disabled on Travis because of a connection issue');
    }
    $user = CMTest_TH::createUser();
    $kissTracking = $this->getMockBuilder('CM_KissTracking')->setMethods(array('track'))->getMock();
    $kissTracking->expects($this->once())->method('track')->with($this->equalTo('foo'), $this->equalTo($user->getId()));
    /** @var CM_KissTracking $kissTracking */
    $kissTracking->trackUser('foo', $user);
  }

  public function testExportEvents() {
    if (getenv('TRAVIS')) {
      $this->markTestSkipped('Disabled on Travis because of a connection issue');
    }
    $kissTracking = $this->getMock('CM_KissTracking', array('_uploadCsv', '_getFileName'));
    $kissTracking->expects($this->any())->method('_getFileName')->will($this->returnValue($this->_filePath));
    $kissTracking->expects($this->once())->method('_uploadCsv')->will($this->returnValue(true));
    /** @var CM_KissTracking $kissTracking */
    CM_KissTracking::getInstance()->track('event', 1, null, array('Viewed' => true));
    $kissTracking->exportEvents();
    $kissTracking->exportEvents();
  }

  public function testExportEventsTwice() {
    if (getenv('TRAVIS')) {
      $this->markTestSkipped('Disabled on Travis because of a connection issue');
    }
    $kissTracking = $this->getMock('CM_KissTracking', array('_uploadCsv', '_getFileName'));
    $kissTracking->expects($this->any())->method('_getFileName')->will($this->returnValue($this->_filePath));
    $kissTracking->expects($this->exactly(2))->method('_uploadCsv')->will($this->returnValue(true));
    /** @var CM_KissTracking $kissTracking */
    CM_KissTracking::getInstance()->track('event', 1, null, array('Viewed' => true));
    $kissTracking->exportEvents();

    CMTest_TH::timeForward(CM_KissTracking::UPLOAD_INTERVAL + 1);
    CM_KissTracking::getInstance()->track('event', 1, null, array('Viewed' => true));
    $kissTracking->exportEvents();
  }

  public function testExportEventsEmpty() {
    if (getenv('TRAVIS')) {
      $this->markTestSkipped('Disabled on Travis because of a connection issue');
    }
    $kissTracking = $this->getMock('CM_KissTracking', array('_uploadCsv', '_getFileName'));
    $kissTracking->expects($this->any())->method('_getFileName')->will($this->returnValue($this->_filePath));
    $kissTracking->expects($this->never())->method('_uploadCsv')->will($this->returnValue(true));
    /** @var CM_KissTracking $kissTracking */
    $kissTracking->exportEvents();
  }
}
