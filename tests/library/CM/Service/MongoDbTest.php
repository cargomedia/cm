<?php

class CM_Service_MongoDbTest extends CMTest_TestCase {

    public function tearDown() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        foreach ($mongoDb->listCollectionNames() as $collection) {
            $mongoDb->drop($collection);
        }
    }

    public function testInsert() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'insert';
        $name = 'Bob';
        $userId = 123;
        $mongoDb->insert($collectionName, array('userId' => $userId, 'name' => $name));
        $res = $mongoDb->findOne($collectionName, array('userId' => $userId));
        $this->assertSame($res['name'], $name);
    }

    public function testBatchInsert() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'batchInsert';
        $mongoDb->batchInsert($collectionName, array(
                array('userId' => 1 , 'name' => 'Bob'),
                array('userId' => 2, 'name' => 'Alice'),
            )
        );
        $res = $mongoDb->findOne($collectionName, array('userId' => 1));
        $this->assertSame($res['name'], 'Bob');
        $res = $mongoDb->findOne($collectionName, array('userId' => 2));
        $this->assertSame($res['name'], 'Alice');
    }

    public function testCreateCollection() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $this->assertEmpty($mongoDb->listCollectionNames());
        $collectionName = 'test';
        $mongoDb->createCollection($collectionName);
        $this->assertSame([$collectionName], $mongoDb->listCollectionNames());
    }

    public function testCreateIndex() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'test';
        $mongoDb->createCollection('' . $collectionName . '');
        $this->assertFalse($mongoDb->hasIndex($collectionName, 'foo'));
        $mongoDb->createIndex($collectionName, ['foo' => 1]);
        $this->assertTrue($mongoDb->hasIndex($collectionName, 'foo'));
        $this->assertFalse($mongoDb->hasIndex($collectionName, ['foo', 'bar']));
        $mongoDb->createIndex($collectionName, ['foo' => 1, 'bar' => -1]);
        $this->assertTrue($mongoDb->hasIndex($collectionName, ['bar', 'foo']));
        $this->assertFalse($mongoDb->hasIndex($collectionName, ['bar']));
    }

    public function testUpdate() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'update';
        $name = 'Bob';
        $userId = 123;
        $mongoDb->insert($collectionName, array('userId' => $userId, 'name' => $name));
        $res = $mongoDb->findOne($collectionName, array('userId' => $userId));
        $this->assertSame($res['name'], $name);

        $mongoDb->update($collectionName, array('userId' => $userId), array('$set' => array('name' => 'Alice')));
        $res = $mongoDb->findOne($collectionName, array('userId' => $userId));
        $this->assertSame($res['name'], 'Alice');

        $collectionName = 'update2';
        $mongoDb->insert($collectionName, array('messageId'  => 1,
                                                'recipients' => array(array('userId' => 1, 'read' => 0), array('userId' => 2, 'read' => 0))));
        $mongoDb->update($collectionName, array('messageId' => 1, 'recipients.userId' => 2), array('$set' => array('recipients.$.read' => 1)));

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
        $collectionName = 'find';

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
        $collectionName = 'count';
        $this->assertSame(0, $mongoDb->count($collectionName));
        $mongoDb->insert($collectionName, array('userId' => 1, 'name' => 'alice'));
        $mongoDb->insert($collectionName, array('userId' => 2, 'name' => 'steve'));
        $mongoDb->insert($collectionName, array('userId' => 3, 'name' => 'bob'));
        $this->assertSame(3, $mongoDb->count($collectionName));
    }

    public function testRemove() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'remove';
        $mongoDb->insert($collectionName, array('userId' => 1, 'name' => 'alice'));
        $mongoDb->insert($collectionName, array('userId' => 2, 'name' => 'steve'));
        $mongoDb->insert($collectionName, array('userId' => 3, 'name' => 'bob'));
        $this->assertSame(3, $mongoDb->count($collectionName));

        $mongoDb->remove($collectionName, array('userId' => 2));

        $this->assertSame(2, $mongoDb->count($collectionName));
        $this->assertSame(0, $mongoDb->find($collectionName, array('userId' => 2))->count());

        $mongoDb->remove($collectionName);
        $this->assertSame(0, $mongoDb->count($collectionName));
    }
}
