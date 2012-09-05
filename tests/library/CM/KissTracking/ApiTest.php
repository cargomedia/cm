<?php

require_once __DIR__ . '/../../../TestCase.php';

class CM_KissTracking_ApiTest extends TestCase {
	private static $_filePath = '/tmp/file_test.csv';

	public static function setupBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
		$cmFile = new CM_File(self::$_filePath);
		$cmFile->delete();
	}

	public function testProcess() {
		$user = TH::createUser();
		$kissTracking = $this->getMock('CM_KissTracking_Api', array('_uploadCsv', '_getFile'));
		$filePath = self::$_filePath	;
		/**
		 * @var CM_KissTracking_Api $kissTracking
		 */
		$kissTracking->expects($this->any())->method('_getFile')->will($this->returnValue($filePath));

		$testRecords1 = array (
			array('test_event_2', 1, null, array('Smart' => true, 'Hired' => 'yes'))
		);
		$time = time();
		foreach ($testRecords1 as $testRecord) {
			CM_KissTracking_Api::getInstance()->track($testRecord[0], $testRecord[1], $testRecord[2], $testRecord[3]);
		}

		$this->assertFileNotExists($filePath);

		$kissTracking->generateCsv();
		$this->assertFileExists($filePath);
		$string = <<<EOD
Identity,Alias,Timestamp,Event,Prop:Smart,Prop:Hired
1,,{$time},test_event_2,1,yes

EOD;
		$generatedFile = new CM_File($filePath);

		$this->assertSame($string, $generatedFile->read());

		$testRecords2 = array (
			array('test_event_4', 1, null, array('Smart' => true, 'PHP' => 'rocks'))
		);
		$time2 = time();
		foreach ($testRecords2 as $testRecord) {
			CM_KissTracking_Api::getInstance()->track($testRecord[0], $testRecord[1], $testRecord[2], $testRecord[3]);
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

