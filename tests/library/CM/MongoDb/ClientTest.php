<?php

class CM_Mongo_ClientTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearDb();
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
                array('userId' => 1, 'name' => 'Bob'),
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
        $collectionName = 'createCollection';
        $this->assertFalse($mongoDb->existsCollection($collectionName));
        $mongoDb->createCollection($collectionName);
        $this->assertTrue($mongoDb->existsCollection($collectionName));
    }

    public function testCreateIndex() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'createIndex';
        $mongoDb->createCollection('' . $collectionName . '');
        $this->assertFalse($mongoDb->hasIndex($collectionName, ['foo' => 1]));
        $mongoDb->createIndex($collectionName, ['foo' => 1]);
        $this->assertTrue($mongoDb->hasIndex($collectionName, ['foo' => 1]));
        $this->assertTrue($mongoDb->hasIndex($collectionName, ['foo' => 1.0]));
        $this->assertFalse($mongoDb->hasIndex($collectionName, ['foo' => -1]));
        $this->assertFalse($mongoDb->hasIndex($collectionName, ['foo' => 1, 'bar' => -1]));
        $mongoDb->createIndex($collectionName, ['foo' => 1, 'bar' => -1]);
        $this->assertTrue($mongoDb->hasIndex($collectionName, ['foo' => 1, 'bar' => -1]));
        $this->assertFalse($mongoDb->hasIndex($collectionName, ['bar' => -1]));
        $this->assertFalse($mongoDb->hasIndex($collectionName, ['bar' => -1, 'foo' => 1]));
        $this->assertFalse($mongoDb->hasIndex($collectionName, ['foo' => 1, 'bar' => 1]));
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

    public function testGetNewId() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $id1 = $mongoDb->getNewId();
        $id2 = $mongoDb->getNewId();
        $this->assertNotSame((string) $id1, (string) $id2);
    }

    public function testFind() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'find';

        $mongoDb->insert($collectionName, array('userId' => 1, 'groupId' => 1, 'name' => 'alice', 'foo' => [1]));
        $mongoDb->insert($collectionName, array('userId' => 2, 'groupId' => 2, 'name' => 'steve', 'foo' => [1, 2]));
        $mongoDb->insert($collectionName, array('userId' => 3, 'groupId' => 1, 'name' => 'bob', 'foo' => [1, 2, 3]));
        $users = $mongoDb->find($collectionName, array('groupId' => 1));
        $this->assertSame(2, $users->count());
        $expectedNames = array('alice', 'bob');
        foreach ($users as $user) {
            $expectedNames = array_diff($expectedNames, array($user['name']));
        }
        $this->assertEmpty($expectedNames);

        $result = $mongoDb->find($collectionName, ['groupId' => 1], ['_id' => 0, 'foo' => 1], [['$unwind' => '$foo']]);
        $actual = \Functional\map($result, function ($val) {
            return $val;
        });
        $this->assertEquals([['foo' => 1], ['foo' => 1], ['foo' => 2], ['foo' => 3]], $actual);
    }

    public function testFindAndModify() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'findAndModify';

        $this->assertNull($mongoDb->findOne($collectionName, ['userId' => 1]));
        $result = $mongoDb->findAndModify($collectionName, ['userId' => 1], ['$inc' => ['score' => 1]], ['_id' => 0], ['upsert' => true, 'new' => true]);
        $this->assertSame(['userId' => 1, 'score' => 1], $result);
        $this->assertSame($result, $mongoDb->findOne($collectionName, ['userId' => 1], ['_id' => 0]));
        $this->assertNull($mongoDb->findAndModify($collectionName, ['userId' => 2], ['$inc' => ['score' => 1]], ['_id' => 0], ['new' => true]));
    }

    public function testFindOne() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'findOne';

        $mongoDb->insert($collectionName, ['userId' => 1, 'groupId' => 1, 'name' => 'alice', 'foo' => [1]]);
        $mongoDb->insert($collectionName, ['userId' => 2, 'groupId' => 2, 'name' => 'steve', 'foo' => [2, 3]]);
        $mongoDb->insert($collectionName, ['userId' => 3, 'groupId' => 1, 'name' => 'bob', 'foo' => [4, 5, 6]]);

        $user = $mongoDb->findOne($collectionName, array('groupId' => 1), ['_id' => 0]);
        $this->assertSame(['userId' => 1, 'groupId' => 1, 'name' => 'alice', 'foo' => [1]], $user);

        $this->assertSame(['foo' => 4], $mongoDb->findOne($collectionName, ['userId' => 3], ['_id' => 0, 'foo' => 1], [['$unwind' => '$foo']]));

        $this->assertNull($mongoDb->findOne($collectionName, array('groupId' => 3), ['_id' => 0]));
        $this->assertNull($mongoDb->findOne($collectionName, ['userId' => 4], ['_id' => 0, 'foo' => 1], [['$unwind' => '$foo']]));
    }

    public function testCount() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'count';
        $this->assertSame(0, $mongoDb->count($collectionName));
        $mongoDb->insert($collectionName, array('userId' => 1, 'groupId' => 1, 'name' => 'alice', 'foo' => [1]));
        $mongoDb->insert($collectionName, array('userId' => 2, 'groupId' => 2, 'name' => 'steve', 'foo' => [1, 2]));
        $mongoDb->insert($collectionName, array('userId' => 3, 'groupId' => 1, 'name' => 'bob', 'foo' => [1, 2, 3]));
        $this->assertSame(3, $mongoDb->count($collectionName));
        $this->assertSame(2, $mongoDb->count($collectionName, array('groupId' => 1)));
        $this->assertSame(6, $mongoDb->count($collectionName, null, [['$unwind' => '$foo']]));
        $this->assertSame(2, $mongoDb->count($collectionName, null, null, 2));
        $this->assertSame(1, $mongoDb->count($collectionName, null, null, null, 2));
        $this->assertSame(0, $mongoDb->count($collectionName, null, null, null, 4));
        $this->assertSame(1, $mongoDb->count($collectionName, null, null, 3, 2));
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
