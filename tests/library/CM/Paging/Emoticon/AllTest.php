<?php

class CM_Paging_Emoticon_AllTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testAll() {
		$emoticonIdList = array();
		$emoticonIdList[] = CM_Db_Db::insert('cm_emoticon', array('code' => ':smiley:', 'codeAdditional' => ':),:-)', 'file' => '1.png'));
		$emoticonIdList[] = CM_Db_Db::insert('cm_emoticon', array('code' => ':<', 'file' => '2.png'));

		$paging = new CM_Paging_Emoticon_All();
		$emoticonList = $paging->getItems();
		$this->assertEquals(array(':smiley:', ':)', ':-)'), $emoticonList[0]['codes']);
		$this->assertEquals(array(':<'), $emoticonList[1]['codes']);
	}
}
