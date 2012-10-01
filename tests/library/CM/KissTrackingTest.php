<?php

require_once __DIR__ . '/../../TestCase.php';

class CM_KissTrackingTest extends TestCase {

	public function tearDown() {
		TH::clearEnv();
	}

	public function testProcess() {
		$filePath = DIR_TMP . 'kisstracking.csv';

		/** @var CM_KissTracking $kissTracking */
		$kissTracking = $this->getMock('CM_KissTracking', array('_uploadCsv', '_getFileName'));
		$kissTracking->expects($this->any())->method('_getFileName')->will($this->returnValue($filePath));

		$testRecords1 = array(array('test_event_2', 1, null, array('Smart' => true, 'Hired' => 'yes')));
		$time = time();
		foreach ($testRecords1 as $testRecord) {
			CM_KissTracking::getInstance()->track($testRecord[0], $testRecord[1], $testRecord[2], $testRecord[3]);
		}

		$this->assertFileNotExists($filePath);

		$generatedFile = $kissTracking->generateCsv();
		$this->assertFileExists($filePath);
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
}

