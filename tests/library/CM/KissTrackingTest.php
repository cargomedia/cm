<?php

class CM_KissTrackingTest extends CMTest_TestCase {

	/** @var CM_KissTracking $kissTracking */
	private $_kissTracking;

	/** @var string */
	private $_filePath;

	public function setUp() {
		CM_Config::get()->CM_KissTracking->enabled = true;
		CM_Config::get()->CM_KissTracking->awsBucketName = 'foo';
		CM_Config::get()->CM_KissTracking->awsFilePrefix = 'bar';

		$this->_filePath = DIR_TMP . 'kisstracking.csv';
		$this->_kissTracking = $this->getMock('CM_KissTracking', array('_uploadCsv', '_getFileName'));
		$this->_kissTracking->expects($this->any())->method('_getFileName')->will($this->returnValue($this->_filePath));
	}

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {

		$testRecords1 = array(array('test_event_2', 1, null, array('Smart' => true, 'Hired' => 'yes')));
		$time = time();
		foreach ($testRecords1 as $testRecord) {
			CM_KissTracking::getInstance()->track($testRecord[0], $testRecord[1], $testRecord[2], $testRecord[3]);
		}

		$this->assertFileNotExists($this->_filePath);

		$generatedFile = $this->_kissTracking->generateCsv();
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
		$this->_kissTracking->generateCsv();
		$this->assertSame($string2, $generatedFile->read());
	}

	public function testExportEvents() {
		CM_KissTracking::getInstance()->track('event', 1, null, array('Viewed' => true));
		$this->_kissTracking->expects($this->once())->method('_uploadCsv')->will($this->returnValue(true));
		$this->_kissTracking->exportEvents();
		$this->_kissTracking->exportEvents();
	}

	public function testExportEventsTwice() {
		CM_KissTracking::getInstance()->track('event', 1, null, array('Viewed' => true));
		$this->_kissTracking->expects($this->exactly(2))->method('_uploadCsv')->will($this->returnValue(true));
		$this->_kissTracking->exportEvents();

		CMTest_TH::timeForward(CM_KissTracking::UPLOAD_INTERVAL + 1);
		CM_KissTracking::getInstance()->track('event', 1, null, array('Viewed' => true));
		$this->_kissTracking->exportEvents();
	}

	public function testExportEventsEmpty() {
		$this->_kissTracking->expects($this->never())->method('_uploadCsv')->will($this->returnValue(true));
		$this->_kissTracking->exportEvents();
	}
}

