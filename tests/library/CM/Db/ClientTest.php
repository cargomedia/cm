<?php

class CM_Db_ClientTest extends CMTest_TestCase {

    public function testConstruct() {
        $config = CM_Service_Manager::getInstance()->getDatabases()->getMaster()->getConfig();
        unset($config['db']);
        $client = new CM_Db_Client($config);
        $this->assertFalse($client->isConnected());
        $client->connect();
        $this->assertTrue($client->isConnected());
    }

    public function testConstructSelectDb() {
        $config = CM_Service_Manager::getInstance()->getDatabases()->getMaster()->getConfig();
        $client = new CM_Db_Client($config);
        $this->assertFalse($client->isConnected());
        $client->connect();
        $this->assertTrue($client->isConnected());

        $config = $client->getConfig();
        $config['db'] = 'nonexistent';
        $client = new CM_Db_Client($config);
        try {
            $client->connect();
            $this->fail('Could select nonexistent DB');
        } catch (CM_Db_Exception $e) {
            $this->assertContains('nonexistent', $e->getMetaInfo()['originalExceptionMessage']);
        }
    }

    public function testConstructReconnectTimeout() {
        $config = CM_Service_Manager::getInstance()->getDatabases()->getMaster()->getConfig();

        unset($config['reconnectTimeout']);
        $client = new CM_Db_Client($config);
        $this->assertSame(300, $client->getReconnectTimeout());

        $config['reconnectTimeout'] = 123;
        $client = new CM_Db_Client($config);
        $this->assertSame(123, $client->getReconnectTimeout());
    }

    public function testConnectDisconnect() {
        $config = CM_Service_Manager::getInstance()->getDatabases()->getMaster()->getConfig();
        unset($config['db']);
        $client = new CM_Db_Client($config);
        $this->assertFalse($client->isConnected());
        $client->connect();
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
        $client = CM_Service_Manager::getInstance()->getDatabases()->getMaster();
        $client->createStatement('CREATE TABLE `test` (`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`))')->execute();
        $this->assertSame(null, $client->getLastInsertId());

        $client->createStatement('INSERT INTO `test` VALUES()')->execute();
        $this->assertSame('1', $client->getLastInsertId());

        $client->createStatement('DROP TABLE `test`')->execute();
    }

    public function testReconnectTimeout() {
        $config = CM_Service_Manager::getInstance()->getDatabases()->getMaster()->getConfig();
        $config['reconnectTimeout'] = 5;
        $client = new CM_Db_Client($config);
        $client->connect();
        $firstTime = $client->getLastConnect();
        $timeForward = 100;
        CMTest_TH::timeForward($timeForward);
        $client->createStatement('SELECT 1')->execute();
        $this->assertSameTime($firstTime + $timeForward, $client->getLastConnect(), 5);
        CMTest_TH::timeForward($timeForward);
        $client->createStatement('SELECT 1')->execute();
        $this->assertSameTime($firstTime + (2 * $timeForward), $client->getLastConnect(), 5);
    }
}
