<?php

class CM_Db_ClientTest extends CMTest_TestCase {

	public function testConstruct() {
		$config = CM_Config::get()->CM_Db_Db;
		$client = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password);
		$this->assertTrue($client->isConnected());
	}

	public function testConstructSelectDb() {
		$config = CM_Config::get()->CM_Db_Db;
		$client = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, $config->db);
		$this->assertTrue($client->isConnected());

		try {
			new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, 'nonexistent');
			$this->fail('Could select nonexistent DB');
		} catch (CM_Db_Exception $e) {
			$this->assertContains('nonexistent', $e->getMessage());
		}
	}

	public function testConnectDisconnect() {
		$config = CM_Config::get()->CM_Db_Db;
		$client = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password);
		$this->assertTrue($client->isConnected());

		$client->disconnect();
		$this->assertFalse($client->isConnected());
		$client->disconnect();
		$this->assertFalse($client->isConnected());

		$client->connect();
		$this->assertTrue($client->isConnected());
		$client->connect();
		$this->assertTrue($client->isConnected());
	}

	public function testGetLastInsertId() {
		$config = CM_Config::get()->CM_Db_Db;
		$client = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, $config->db);
		$client->createStatement('CREATE TABLE `test` (`id` INT(10) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`))')->execute();
		$this->assertSame(null, $client->getLastInsertId());

		$client->createStatement('INSERT INTO `test` VALUES()')->execute();
		$this->assertSame('1', $client->getLastInsertId());

		$client->createStatement('DROP TABLE `test`')->execute();
	}

	public function testReconnectTimeout(){
		$config = CM_Config::get()->CM_Db_Db;
		$client = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, $config->db, 5);
		$firstTime = $client->getLastConnectTime();
		$timeForward = 100;
		CMTest_TH::timeForward($timeForward);
		$client->createStatement('SELECT 1')->execute();
		$this->assertSameTime($firstTime+$timeForward, $client->getLastConnectTime());
		CMTest_TH::timeForward($timeForward);
		$client->createStatement('SELECT 1')->execute();
		$this->assertSameTime($firstTime+(2*$timeForward), $client->getLastConnectTime());
	}
}
