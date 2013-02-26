<?php

class CM_Paging_Emoticon_AllTest extends CMTest_TestCase {

	private static $_emoticonIdList = array();

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testAll() {
		self::$_emoticonIdList[] = CM_Mysql::insert(TBL_CM_EMOTICON, array('code' => ':smiley:', 'codeAdditional' => ':),:-)', 'file' => '1.png'));
		self::$_emoticonIdList[] = CM_Mysql::insert(TBL_CM_EMOTICON, array('code' => ':<', 'file' => '2.png'));

		$paging = new CM_Paging_Emoticon_All();
		$emoticonList = $paging->getItems();
		$this->assertEquals(array(':smiley:',':)',':-)'), $emoticonList[0]['codes']);
		$this->assertEquals(array(':<'), $emoticonList[1]['codes']);
	}

	public function testNoIntersection() {
		$paging = new CM_Paging_Emoticon_All();
		$codes = array();
		foreach ($paging as $emoticon) {
			$codes = array_merge($codes, $emoticon['codes']);
		}
		for ($i = 0; $i < count($codes); $i++) {
			for ($j = $i + 1; $j < count($codes); $j++) {
				if (false !== strpos($codes[$i], $codes[$j])) {
					$this->fail('Emoticon `' . $codes[$i] . '` in `' . $codes[$j] . '`');
				}
			}
		}
	}
}
