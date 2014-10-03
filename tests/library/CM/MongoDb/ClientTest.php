<?php

class CM_Mongo_ClientTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearDb();
    }

    public function testInsert() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'insert';

        $data = ['userId' => 1, 'name' => 'Bob'];
        $id = $mongoDb->insert($collectionName, $data);
        $this->assertEquals(['userId' => 1, 'name' => 'Bob'], $data);
        $doc1 = $mongoDb->findOne($collectionName, ['userId' => 1]);
        $this->assertEquals(['_id' => $id] + $data, $doc1);

        $data = ['_id' => 1, 'userId' => 2, 'name' => 'Alice'];
        $id = $mongoDb->insert($collectionName, $data);
        $this->assertSame(1, $id);
        $doc2 = $mongoDb->findOne($collectionName, ['_id' => 1]);
        $this->assertSame($data, $doc2);

        $data = ['userId' => 3, 'name' => 'Dexter'];
        $id = $mongoDb->insert($collectionName, $data, ['w' => 0]);
        $this->assertInstanceOf('MongoId', $id);

    }

    public function testBatchInsert() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'batchInsert';
        $data1 = ['userId' => 1, 'name' => 'Bob'];
        list($id1, $id2) = $mongoDb->batchInsert($collectionName, array(
                $data1,
                ['_id' => 1, 'userId' => 2, 'name' => 'Alice'],
            )
        );
        $this->assertSame(1, $id2);
        $this->assertEquals(['userId' => 1, 'name' => 'Bob'], $data1);
        $doc1 = $mongoDb->findOne($collectionName, ['userId' => 1]);
        $this->assertEquals(['_id' => $id1] + $data1, $doc1);
        $doc2 = $mongoDb->findOne($collectionName, ['userId' => 2]);
        $this->assertEquals(['_id' => $id2, 'userId' => 2, 'name' => 'Alice'], $doc2);
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
        $doc1 = array('_id' => 1, 'name' => 'Bob', 'groupId' => 1);
        $doc2 = array('_id' => 2, 'name' => 'Alice', 'groupId' => 1);
        $doc3 = array('_id' => 3, 'name' => 'Dexter', 'groupId' => 2);
        $mongoDb->insert($collectionName, $doc1);
        $mongoDb->insert($collectionName, $doc2);
        $mongoDb->insert($collectionName, $doc3);
        $this->assertSame($doc1, $mongoDb->findOne($collectionName, array('_id' => 1)));

        $res = $mongoDb->update($collectionName, array('_id' => 1), array('$set' => array('name' => 'Klaus')));
        $this->assertSame(1, $res);
        $this->assertSame(['_id' => 1, 'name' => 'Klaus', 'groupId' => 1], $mongoDb->findOne($collectionName, array('_id' => 1)));
        $res = $mongoDb->update($collectionName, ['groupId' => 1], ['$set' => ['groupId' => 3]], ['multiple' => true]);
        $this->assertSame(2, $res);

        $this->assertTrue($mongoDb->update($collectionName, ['_id' => 4], ['name' => 'Martin'], ['w' => 0]));

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
        $result = $mongoDb->findAndModify($collectionName, ['userId' => 1], ['$inc' => ['score' => 1]], ['_id' => 0], ['upsert' => true,
                                                                                                                       'new'    => true]);
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
        $mongoDb->insert($collectionName, array('userId' => 1, 'name' => 'alice', 'groupId' => 1));
        $mongoDb->insert($collectionName, array('userId' => 2, 'name' => 'steve', 'groupId' => 1));
        $mongoDb->insert($collectionName, array('userId' => 3, 'name' => 'bob', 'groupId' => 1));
        $mongoDb->insert($collectionName, array('userId' => 4, 'name' => 'dexter', 'groupId' => 2));
        $this->assertSame(4, $mongoDb->count($collectionName));

        $this->assertSame(1, $mongoDb->remove($collectionName, array('userId' => 2)));

        $this->assertSame(3, $mongoDb->count($collectionName));
        $this->assertSame(0, $mongoDb->find($collectionName, array('userId' => 2))->count());

        $this->assertSame(2, $mongoDb->remove($collectionName, ['groupId' => 1]));
        $this->assertSame(1, $mongoDb->count($collectionName));

        $this->assertSame(1, $mongoDb->remove($collectionName));

        $this->assertSame(0, $mongoDb->count($collectionName));

        $this->assertTrue($mongoDb->remove($collectionName, null, ['w' => 0]));
    }
}
