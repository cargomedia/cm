<?php

class CM_Service_MongoDbTest extends CMTest_TestCase {

    private $_collectionPrefix = 'UnitTest_';

    public function testInsert() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = $this->_getEmptyCollectionName('insert');
        $name = 'Bob';
        $userId = 123;
        $mongoDb->insert($collectionName, array('userId' => $userId, 'name' => $name));
        $res = $mongoDb->findOne($collectionName, array('userId' => $userId));
        $this->assertSame($res['name'], $name);
    }

    /**
     * Generate a name of a collection and ensure it's empty
     *
     * @param string $testName
     * @return string
     */
    private function _getEmptyCollectionName($testName) {
        $collectionName = $this->_collectionPrefix . $testName;
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        if ($mongoDb->collectionExists($testName)) {
            $mongoDb->drop($collectionName);
        }
        return $collectionName;
    }

    public function testCollectionExists() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $nonExistentCollectionName = md5(uniqid());
        $this->assertFalse($mongoDb->collectionExists($nonExistentCollectionName));

        $existingCollectionName = $this->_getEmptyCollectionName('foo');
        $mongoDb->insert($existingCollectionName, array('foo' => 'bar'));
        $this->assertTrue($mongoDb->collectionExists($existingCollectionName));
    }

    public function testUpdate() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = $this->_getEmptyCollectionName('update');
        $name = 'Bob';
        $userId = 123;
        $mongoDb->insert($collectionName, array('userId' => $userId, 'name' => $name));
        $res = $mongoDb->findOne($collectionName, array('userId' => $userId));
        $this->assertSame($res['name'], $name);

        $mongoDb->update($collectionName, array('userId' => $userId), array('$set' => array('name' => 'Alice')));
        $res = $mongoDb->findOne($collectionName, array('userId' => $userId));
        $this->assertSame($res['name'], 'Alice');

        $collectionName = $this->_getEmptyCollectionName('update2');
        $mongoDb->insert($collectionName, array('messageId'  => 1,
                                                'recipients' => array(
                                                    array('userId' => 1, 'read' => 0),
                                                    array('userId' => 2, 'read' => 0)
                                                )
        ));
        $mongoDb->update($collectionName, array('messageId'         => 1,
                                                'recipients.userId' => 2),
            array('$set' => array('recipients.$.read' => 1)));

        $message = $mongoDb->findOne($collectionName, array('messageId' => 1));
        $this->assertNotEmpty($message);
        foreach ($message['recipients'] as $recipient) {
            if ($recipient['userId'] == 1) {
                $this->assertSame(0, $recipient['read']);
            } else {
                if ($recipient['userId'] == 2) {
                    $this->assertSame(1, $recipient['read']);
                } else {
                    $this->fail('Unexpected recipient id.');
                }
            }
        }
    }

    /**
     * NOTE: this one actually tests if the returned id isn't empty rather than if it's unique... which would be hard to test.
     */
    public function testGetNewId() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $id1 = $mongoDb->getNewId();
        $id2 = $mongoDb->getNewId();
        $this->assertNotSame($id1, $id2);
    }

    public function testFind() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = $this->_getEmptyCollectionName('find');

        $mongoDb->insert($collectionName, array('userId' => 1, 'groupId' => 1, 'name' => 'alice'));
        $mongoDb->insert($collectionName, array('userId' => 2, 'groupId' => 2, 'name' => 'steve'));
        $mongoDb->insert($collectionName, array('userId' => 3, 'groupId' => 1, 'name' => 'bob'));
        $users = $mongoDb->find($collectionName, array('groupId' => 1));
        $this->assertSame(2, $users->count());
        $expectedNames = array('alice', 'bob');
        foreach ($users as $user) {
            $expectedNames = array_diff($expectedNames, array($user['name']));
        }
        $this->assertEmpty($expectedNames);
    }

    public function testCount() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = $this->_getEmptyCollectionName('count');
        $this->assertSame(0, $mongoDb->count($collectionName));
        $mongoDb->insert($collectionName, array('userId' => 1, 'name' => 'alice'));
        $mongoDb->insert($collectionName, array('userId' => 2, 'name' => 'steve'));
        $mongoDb->insert($collectionName, array('userId' => 3, 'name' => 'bob'));
        $this->assertSame(3, $mongoDb->count($collectionName));
    }

    public function testRemove() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = $this->_getEmptyCollectionName('remove');
        $mongoDb->insert($collectionName, array('userId' => 1, 'name' => 'alice'));
        $mongoDb->insert($collectionName, array('userId' => 2, 'name' => 'steve'));
        $mongoDb->insert($collectionName, array('userId' => 3, 'name' => 'bob'));
        $this->assertSame(3, $mongoDb->count($collectionName));

        $mongoDb->remove($collectionName, array('userId' => 2));

        $this->assertSame(2, $mongoDb->count($collectionName));
        $this->assertSame(0, $mongoDb->find($collectionName, array('userId' => 2))->count());
    }

    public function testCreateDeleteIndex() {
        $indexName = 'foo';
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = $this->_getEmptyCollectionName('createDeleteIndex');
        $mongoDb->createIndex($collectionName, array('indexedField' => 1), array('name' => $indexName));

        $indexInfoList = $mongoDb->getIndexInfo($collectionName);
        $this->assertCount(2, $indexInfoList);
        $this->assertSame($indexInfoList[1]['key']['indexedField'], 1);
        $this->assertSame($indexInfoList[1]['name'], $indexName);

        $mongoDb->deleteIndex($collectionName, $indexName);
        $indexInfoList = $mongoDb->getIndexInfo($collectionName);
        $this->assertCount(1, $indexInfoList);
    }

    /**
     * @expectedException CM_Exception
     */
    public function testDeleteNonExistentIndex() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = $this->_getEmptyCollectionName('deleteNonExistentIndex');
        $mongoDb->deleteIndex($collectionName, 'foo');
    }
}
