<?php

class CMService_Amazon_S3Test extends CMTest_TestCase {

	public function setUp() {
		if (!empty($_ENV['TRAVIS'])) {
			$this->markTestSkipped('Disabled on Travis because of a connection issue');
		}
	}

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testConstructor() {
		$config = CM_Config::get();
		$config->CMService_Amazon_Abstract->accessKey = 'accessKey';
		$config->CMService_Amazon_Abstract->secretKey = 'secretKey';
		CM_Config::set($config);
		new CMService_Amazon_S3();
		$this->assertTrue(true);
	}

	/**
	 * @expectedException CM_Exception_Invalid
	 * @expectedExceptionMessage Amazon S3 `accessKey` not set
	 */
	public function testConstructorMissingAccessKey() {
		new CMService_Amazon_S3();
	}

	/**
	 * @expectedException CM_Exception_Invalid
	 * @expectedExceptionMessage Amazon S3 `secretKey` not set
	 */
	public function testConstructorMissingSecretKey() {
		$config = CM_Config::get();
		$config->CMService_Amazon_Abstract->accessKey = 'accessKey';
		CM_Config::set($config);
		new CMService_Amazon_S3();
	}
}
