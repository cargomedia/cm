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
		$this->assertInstanceOf('CM_Db_Client', $client);
		$this->assertTrue(true);

		try {
			new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, 'nonexistent');
			$this->fail('Could select nonexistent DB');
		} catch (CM_Exception $e) {
			$this->assertContains('Unknown database', $e->getMessage());
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
}
