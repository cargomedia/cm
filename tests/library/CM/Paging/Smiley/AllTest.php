<?php

class CM_Paging_Emoticon_AllTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {

	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
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
