<?php

class CM_ParamsTest extends CMTest_TestCase {

	public function testHas() {
		$params = new CM_Params(array('1' => 0, '2' => 'ababa', '3' => new stdClass(), '4' => null, '5' => false));

		$this->assertTrue($params->has('1'));
		$this->assertTrue($params->has('2'));
		$this->assertTrue($params->has('3'));
		$this->assertFalse($params->has('4'));
		$this->assertTrue($params->has('5'));
		$this->assertFalse($params->has('6'));
	}

	public function testGetString() {
		$text = "Foo Bar, Bar Foo";
		$notText = new stdClass();
		$params = new CM_Params(array('text1' => CM_Params::encode($text), 'text2' => $text, 'text3' => $notText));

		$this->assertEquals($text, $params->getString('text1'));
		$this->assertEquals($text, $params->getString('text2'));
		try {
			$params->getString('text3');
			$this->fail('invalid param. should not exist');
		} catch (CM_Exception_InvalidParam $e) {
			$this->assertTrue(true);
		}
		$this->assertEquals('foo', $params->getString('text4', 'foo'));
	}

	public function testGetStringArray() {

		$params = new CM_Params(array('k1' => 9, 'k2' => array(99, '121', '72', 0x3f), 'k3' => array('4', '8' . '3', '43', 'pong')));

		$this->assertSame(array('4', '83', '43', 'pong'), $params->getStringArray('k3'));

		try {
			$params->getStringArray('k1');
			$this->fail('Is not an array of strings!');
		} catch (CM_Exception_InvalidParam $e) {
			$this->assertTrue(true);
		}

		try {
			$params->getStringArray('k2');
			$this->fail('Is not an array of strings!');
		} catch (CM_Exception_InvalidParam $e) {
			$this->assertTrue(true);
		}
	}

	public function testGetInt() {
		$number1 = 12345678;
		$number2 = '12345678';
		$number3 = 'foo';
		$params = new CM_Params(array('number1' => $number1, 'number2' => CM_Params::encode($number2), 'number3' => $number3));

		$this->assertEquals($number1, $params->getInt('number1'));
		$this->assertEquals($number2, $params->getInt('number2'));
		try {
			$params->getInt('number3');
			$this->fail('invalid param. should not exist');
		} catch (CM_Exception_InvalidParam $e) {
			$this->assertTrue(true);
		}
		$this->assertEquals(4, $params->getInt('number4', 4));
	}

	public function testGetIntArray() {

		$params = new CM_Params(array('k1' => '7', 'k2' => array('99', '121', 72, 0x3f), 'k3' => array(4, 88, '43', 'pong')));

		$this->assertSame(array(99, 121, 72, 63), $params->getIntArray('k2'));

		try {
			$params->getIntArray('k1');
			$this->fail('Is not an array of integers!');
		} catch (CM_Exception_InvalidParam $e) {
			$this->assertTrue(true);
		}

		try {
			$params->getIntArray('k3');
			$this->fail('Is not an array of integers!');
		} catch (CM_Exception_InvalidParam $e) {
			$this->assertTrue(true);
		}
	}

	public function testGetFloat() {
		$params = new CM_Params(array('1' => 34.20, '2' => '6543.123', '3' => '1.2.3', '4' => 0.0, 5 => '0.0', '5' => 4));
		$params->getFloat('1');
		$params->getFloat('2');
		try {
			$params->getFloat('3');
			$this->fail('Is no float');
		} catch (CM_Exception_InvalidParam $ex) {
			$this->assertTrue(true);
		}
		$params->getFloat('4');
		try {
			$params->getFloat('5');
			$this->assertTrue(true);
		} catch (CM_Exception_InvalidParam $ex) {
			$this->fail('Is float');
		}
	}

	public function testGetObject() {
		$language = CM_Model_Language::create(array('name' => 'English', 'abbreviation' => 'en', 'enabled' => '1'));
		$params = new CM_Params(array('language' => $language, 'languageId' => $language->getId(), 'no-object-param' => 'xyz'));
		$this->assertEquals($language, $params->getLanguage('language'));
		$this->assertEquals($language, $params->getLanguage('languageId'));
		try {
			$params->getLanguage('no-object-param');
			$this->fail('getObject should fail and throw exception');
		} catch (CM_Exception $e) {
			$this->assertContains(get_class($language), $e->getMessage());
		}
	}

	public function testGetFile() {
		$file = CM_File::createTmp();
		$params = new CM_Params(array('file' => $file, 'filename' => $file->getPath()));
		$this->assertEquals($file, $params->getFile('file'));
		$this->assertEquals($file, $params->getFile('filename'));
	}

	/**
	 * @expectedException CM_Exception_InvalidParam
	 * @expectedExceptionMessage does not exist or is not a file
	 */
	public function testGetFileException() {
		$params = new CM_Params(array('nonexistent' => 'foo/bar'));
		$params->getFile('nonexistent');
	}

	public function testGetFileGeoPoint() {
		$point = new CM_Geo_Point(1, 2);
		$params = new CM_Params(array('point' => $point));
		$value = $params->getGeoPoint('point');
		$this->assertInstanceOf('CM_Geo_Point', $value);
		$this->assertSame(1.0, $value->getLatitude());
		$this->assertSame(2.0, $value->getLongitude());
	}

	/**
	 * @expectedException ErrorException
	 * @expectedExceptionMessage Missing argument 2
	 */
	public function testGetGeoPointException() {
		$params = new CM_Params(array('point' => 'foo'));
		$params->getGeoPoint('point');
	}
}
