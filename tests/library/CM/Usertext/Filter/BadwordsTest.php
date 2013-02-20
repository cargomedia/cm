<?php

class CM_Usertext_Filter_BadwordsTest extends CMTest_TestCase {

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testProcess() {
		$badwords = new CM_Paging_ContentList_Badwords();
		$badwords->add('foo');

		$filter = new CM_Usertext_Filter_Badwords();
		$actual = $filter->transform('Stop the foo here!');
		$this->assertEquals('Stop the … here!', $actual);
	}

}
