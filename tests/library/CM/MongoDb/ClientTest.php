<?php

class CM_MongoDb_ClientTest extends CMTest_TestCase {

    public function tearDown() {
        CM_Service_Manager::getInstance()->getMongoDb()->dropDatabase();
        CMTest_TH::clearEnv();
    }

    public function testDatabaseExists() {
        $client = CM_Service_Manager::getInstance()->getMongoDb();
        $client->dropDatabase();
        $this->assertFalse($client->databaseExists());
        $client->createCollection('foo');
        $this->assertTrue($client->databaseExists());
    }

    public function testDeleteIndex() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'deleteIndex';
        $mongoDb->createCollection($collectionName);
        $this->assertFalse($mongoDb->hasIndex($collectionName, ['foo' => 1]));
        $this->assertFalse($mongoDb->hasIndex($collectionName, ['bar' => 1]));
        $mongoDb->createIndex($collectionName, ['foo' => 1]);
        $mongoDb->createIndex($collectionName, ['bar' => 1]);
        $this->assertTrue($mongoDb->hasIndex($collectionName, ['foo' => 1]));
        $this->assertTrue($mongoDb->hasIndex($collectionName, ['bar' => 1]));
        $mongoDb->deleteIndex($collectionName, 'bar_1');
        $this->assertTrue($mongoDb->hasIndex($collectionName, ['foo' => 1]));
        $this->assertFalse($mongoDb->hasIndex($collectionName, ['bar' => 1]));
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
        $this->assertInstanceOf('MongoDB\BSON\ObjectID', $id);
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
        $mongoDb->createCollection($collectionName);
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

    public function testUpdateOne() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'updateOne';
        $doc1 = ['_id' => 1, 'name' => 'Bob', 'groupId' => 1];
        $doc2 = ['_id' => 2, 'name' => 'Alice', 'groupId' => 1];
        $doc3 = ['_id' => 3, 'name' => 'Dexter', 'groupId' => 2];
        $mongoDb->insert($collectionName, $doc1);
        $mongoDb->insert($collectionName, $doc2);
        $mongoDb->insert($collectionName, $doc3);
        $this->assertSame($doc1, $mongoDb->findOne($collectionName, ['_id' => 1]));

        $result = $mongoDb->updateOne($collectionName, ['_id' => 1], ['$set' => ['name' => 'Klaus']]);
        $this->assertSame(1, $result);
        $this->assertSame(['_id' => 1, 'name' => 'Klaus', 'groupId' => 1], $mongoDb->findOne($collectionName, ['_id' => 1]));
        $result = $mongoDb->updateOne($collectionName, ['groupId' => 1], ['$set' => ['groupId' => 3]]);
        $this->assertSame(1, $result);
        $this->assertSame(['_id' => 1, 'name' => 'Klaus', 'groupId' => 3], $mongoDb->findOne($collectionName, ['_id' => 1]));

        $result = $mongoDb->updateOne($collectionName, ['groupId' => 1, 'name' => 'Alice'], ['$set' => ['groupId' => 4]]);
        $this->assertSame(1, $result);
        $this->assertSame(['_id' => 2, 'name' => 'Alice', 'groupId' => 4], $mongoDb->findOne($collectionName, ['_id' => 2]));

        $result = $mongoDb->updateOne($collectionName, ['groupId' => 1], ['$set' => ['groupId' => 4]]);
        $this->assertSame(0, $result);
    }

    public function testUpdateMany() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'updateMany';
        $doc1 = ['_id' => 1, 'name' => 'Bob', 'groupId' => 1];
        $doc2 = ['_id' => 2, 'name' => 'Alice', 'groupId' => 1];
        $doc3 = ['_id' => 3, 'name' => 'Dexter', 'groupId' => 2];
        $mongoDb->insert($collectionName, $doc1);
        $mongoDb->insert($collectionName, $doc2);
        $mongoDb->insert($collectionName, $doc3);
        $this->assertSame($doc1, $mongoDb->findOne($collectionName, ['_id' => 1]));

        $result = $mongoDb->updateMany($collectionName, ['_id' => 1], ['$set' => ['name' => 'Klaus']]);
        $this->assertSame(1, $result);
        $this->assertSame(['_id' => 1, 'name' => 'Klaus', 'groupId' => 1], $mongoDb->findOne($collectionName, ['_id' => 1]));
        $result = $mongoDb->updateMany($collectionName, ['groupId' => 1], ['$set' => ['groupId' => 3]]);
        $this->assertSame(2, $result);
        $this->assertSame(['_id' => 1, 'name' => 'Klaus', 'groupId' => 3], $mongoDb->findOne($collectionName, ['_id' => 1]));
        $this->assertSame(['_id' => 2, 'name' => 'Alice', 'groupId' => 3], $mongoDb->findOne($collectionName, ['_id' => 2]));

        $result = $mongoDb->updateMany($collectionName, ['groupId' => 3, 'name' => 'Alice'], ['$set' => ['groupId' => 4]]);
        $this->assertSame(1, $result);
        $this->assertSame(['_id' => 2, 'name' => 'Alice', 'groupId' => 4], $mongoDb->findOne($collectionName, ['_id' => 2]));

        $result = $mongoDb->updateMany($collectionName, ['groupId' => 1], ['$set' => ['groupId' => 4]]);
        $this->assertSame(0, $result);
    }

    public function testReplaceOne() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'replace';
        $doc1 = ['_id' => 1, 'name' => 'Bob', 'groupId' => 1];
        $doc2 = ['_id' => 2, 'name' => 'Alice', 'groupId' => 1];
        $doc3 = ['_id' => 1, 'name' => 'Dexter', 'groupId' => 2];
        $mongoDb->insert($collectionName, $doc1);
        $mongoDb->insert($collectionName, $doc2);
        $this->assertSame($doc1, $mongoDb->findOne($collectionName));

        $result = $mongoDb->replaceOne($collectionName, ['groupId' => 1], $doc3);
        $this->assertSame(1, $result);
        $this->assertSame($doc3, $mongoDb->findOne($collectionName));

        $result = $mongoDb->replaceOne($collectionName, ['groupId' => 3], $doc3);
        $this->assertSame(0, $result);
    }

    public function testGetNewId() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $id1 = $mongoDb->getObjectId();
        $id2 = $mongoDb->getObjectId();
        $this->assertNotSame((string) $id1, (string) $id2);
    }

    public function testFind() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'find';

        $doc1 = ['userId' => 1, 'groupId' => 1, 'name' => 'alice', 'foo' => [1]];
        $doc2 = ['userId' => 2, 'groupId' => 2, 'name' => 'steve', 'foo' => [1, 2]];
        $doc3 = ['userId' => 3, 'groupId' => 1, 'name' => 'bob', 'foo' => [1, 2, 3]];
        $mongoDb->insert($collectionName, $doc1);
        $mongoDb->insert($collectionName, $doc2);
        $mongoDb->insert($collectionName, $doc3);

        $users = $mongoDb->find($collectionName);
        $expected = [$doc1, $doc2, $doc3];
        $this->assertSame($expected, \Functional\map($users, function ($doc) {
            unset($doc['_id']);
            return $doc;
        }));

        // with criteria
        $expected = [$doc1, $doc3];
        $users = $mongoDb->find($collectionName, ['groupId' => 1]);
        $this->assertSame($expected, \Functional\map($users, function ($doc) {
            unset($doc['_id']);
            return $doc;
        }));

        // with projection
        $expected = [['name' => 'alice'], ['name' => 'steve'], ['name' => 'bob']];
        $this->assertSame($expected, $mongoDb->find($collectionName, null, ['name' => 1, '_id' => 0])->toArray());

        // skip
        $expected = [$doc2, $doc3];
        $this->assertSame($expected, $mongoDb->find($collectionName, null, ['_id' => 0], null, ['skip' => 1])->toArray());

        // limit
        $expected = [$doc1, $doc2];
        $this->assertSame($expected, $mongoDb->find($collectionName, null, ['_id' => 0], null, ['limit' => 2])->toArray());

        // sort
        $expected = [$doc3, $doc1, $doc2];
        $this->assertSame($expected, $mongoDb->find($collectionName, null, ['_id' => 0], null, ['sort' => ['groupId' => 1, 'userId'  => -1]])->toArray());

        // aggregation
        $result = $mongoDb->find($collectionName, ['groupId' => 1], ['_id' => 0, 'foo' => 1], [['$unwind' => '$foo']]);
        $this->assertEquals([['foo' => 1], ['foo' => 1], ['foo' => 2], ['foo' => 3]], $result->toArray());
    }

    public function testFindBatchSize() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'findBatchSize';
        CM_Config::get()->CM_MongoDb_Client->batchSize = null;

        $cursor = $mongoDb->find($collectionName);
        $this->assertSame(0, $cursor->info()['batchSize']);
        $cursor = $mongoDb->find($collectionName, null, null, ['$match' => ['foo' => 'bar']]);
        $this->assertSame(0, $cursor->info()['batchSize']);

        CM_Config::get()->CM_MongoDb_Client->batchSize = 10;

        $cursor = $mongoDb->find($collectionName);
        $this->assertSame(10, $cursor->info()['batchSize']);
        $cursor = $mongoDb->find($collectionName, null, null, ['$match' => ['foo' => 'bar']]);
        $this->assertSame(10, $cursor->info()['batchSize']);
        $this->assertSame(10, $cursor->info()['query']['cursor']['batchSize']);
    }

    public function testFindOneAndUpdate() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'findOneAndUpdate';

        $this->assertNull($mongoDb->findOne($collectionName, ['userId' => 1]));
        $options = ['returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER];
        $result = $mongoDb->findOneAndUpdate($collectionName, ['userId' => 1], ['$inc' => ['score' => 1]], ['_id' => 0], $options + ['upsert' => true]);
        $this->assertSame(['userId' => 1, 'score' => 1], $result);
        $this->assertSame($mongoDb->findOne($collectionName, ['userId' => 1], ['_id' => 0]), $result);

        $this->assertNull($mongoDb->findOneAndUpdate($collectionName, ['userId' => 2], ['$inc' => ['score' => 1]], ['_id' => 0], $options));

        $result = $mongoDb->findOneAndUpdate($collectionName, ['userId' => 1], ['$inc' => ['score' => 1]]);
        $this->assertSame(['_id' => $result['_id'], 'userId' => 1, 'score' => 1], $result);
        $this->assertSame(['userId' => 1, 'score' => 2], $mongoDb->findOne($collectionName, ['userId' => 1], ['_id' => 0]));

        $mongoDb->insert($collectionName, ['userId' => 2, 'score' => 3]);
        $this->assertEquals([['userId' => 1, 'score' => 2], ['userId' => 2, 'score' => 3]], $mongoDb->find($collectionName, null, ['_id' => 0])->toArray());

        $result = $mongoDb->findOneAndUpdate($collectionName, null, ['$inc' => ['score' => 1]], ['_id' => 0], ['sort' => ['userId' => 1]]);
        $this->assertSame(['userId' => 1, 'score' => 2], $result);
        $this->assertEquals([['userId' => 1, 'score' => 3], ['userId' => 2, 'score' => 3]], $mongoDb->find($collectionName, null, ['_id' => 0])->toArray());
    }

    public function testFindOneAndReplace() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'findOneAndReplace';

        $this->assertNull($mongoDb->findOne($collectionName, ['userId' => 1]));
        $options = ['returnDocument' => \MongoDB\Operation\FindOneAndReplace::RETURN_DOCUMENT_AFTER];
        $result = $mongoDb->findOneAndReplace($collectionName, ['userId' => 1], ['userId' => 1, 'score' => 1], ['_id' => 0], $options + ['upsert' => true]);
        $this->assertSame(['userId' => 1, 'score' => 1], $result);
        $this->assertSame($mongoDb->findOne($collectionName, ['userId' => 1], ['_id' => 0]), $result);

        $this->assertNull($mongoDb->findOneAndReplace($collectionName, ['userId' => 2], ['userId' => 2, 'score' => 2], ['_id' => 0], $options));

        $result = $mongoDb->findOneAndReplace($collectionName, ['userId' => 1], ['userId' => 2, 'score' => 2]);

        $this->assertSame(['_id' => $result['_id'], 'userId' => 1, 'score' => 1], $result);
        $this->assertSame([['userId' => 2, 'score' => 2]], $mongoDb->find($collectionName, ['userId' => 2], ['_id' => 0])->toArray());

        $mongoDb->insert($collectionName, ['userId' => 3, 'score' => 3]);
        $this->assertEquals([['userId' => 2, 'score' => 2], ['userId' => 3, 'score' => 3]], $mongoDb->find($collectionName, null, ['_id' => 0])->toArray());

        $result = $mongoDb->findOneAndReplace($collectionName, null, ['userId' => 4, 'score' => 4], ['_id' => 0], ['sort' => ['userId' => 1]]);
        $this->assertSame(['userId' => 2, 'score' => 2], $result);
        $this->assertEquals([['userId' => 4, 'score' => 4], ['userId' => 3, 'score' => 3]], $mongoDb->find($collectionName, null, ['_id' => 0])->toArray());
    }

    public function testFindOneAndDelete() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'findOneAndDelete';

        $mongoDb->insert($collectionName, ['userId' => 1, 'score' => 1]);
        $result = $mongoDb->findOneAndDelete($collectionName, ['userId' => 1], ['_id' => 0]);
        $this->assertSame(['userId' => 1, 'score' => 1], $result);

        $mongoDb->insert($collectionName, ['userId' => 1, 'score' => 1]);
        $result = $mongoDb->findOneAndDelete($collectionName, ['userId' => 2]);
        $this->assertSame(null, $result);

        $mongoDb->insert($collectionName, ['userId' => 2, 'score' => 1]);

        $result = $mongoDb->findOneAndDelete($collectionName, ['score' => 1], null, ['sort' => ['userId' => -1]]);
        $this->assertSame(['_id' => $result['_id'], 'userId' => 2, 'score' => 1], $result);
        $this->assertEquals([['userId' => 1, 'score' => 1]], $mongoDb->find($collectionName, null, ['_id' => 0])->toArray());

        $mongoDb->insert($collectionName, ['userId' => 2, 'score' => 2]);
        $this->assertEquals([['userId' => 1, 'score' => 1], ['userId' => 2, 'score' => 2]], $mongoDb->find($collectionName, null, ['_id' => 0])->toArray());

        $result = $mongoDb->findOneAndDelete($collectionName, null, ['_id' => 0], ['sort' => ['userId' => 1]]);
        $this->assertSame(['userId' => 1, 'score' => 1], $result);
        $this->assertEquals([['userId' => 2, 'score' => 2]], $mongoDb->find($collectionName, null, ['_id' => 0])->toArray());
    }

    public function testFindOne() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'findOne';

        $mongoDb->insert($collectionName, ['userId' => 1, 'groupId' => 1, 'name' => 'alice', 'foo' => [1]]);
        $mongoDb->insert($collectionName, ['userId' => 2, 'groupId' => 2, 'name' => 'steve', 'foo' => [2, 3]]);
        $mongoDb->insert($collectionName, ['userId' => 3, 'groupId' => 1, 'name' => 'bob', 'foo' => [4, 5, 6]]);

        $user = $mongoDb->findOne($collectionName, array('groupId' => 1), ['_id' => 0]);
        $this->assertSame(['userId' => 1, 'groupId' => 1, 'name' => 'alice', 'foo' => [1]], $user);
        $user = $mongoDb->findOne($collectionName, null, ['_id' => 0], null, ['sort' => ['userId' => -1]]);
        $this->assertSame(['userId' => 3, 'groupId' => 1, 'name' => 'bob', 'foo' => [4, 5, 6]], $user);

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

    public function testDrop() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'drop';
        $mongoDb->createCollection($collectionName);
        $this->assertTrue($mongoDb->existsCollection($collectionName));
        $mongoDb->drop($collectionName);
        $this->assertFalse($mongoDb->existsCollection($collectionName));
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

    public function testExistsCollection() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'existsCollection';

        $this->assertFalse($mongoDb->existsCollection($collectionName));
        $mongoDb->createCollection($collectionName);
        $this->assertTrue($mongoDb->existsCollection($collectionName));
    }

    public function testIsValidObjectId() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();

        $this->assertTrue($mongoDb->isValidObjectId('1234567890abcdef12345678'));
        $this->assertTrue($mongoDb->isValidObjectId(new MongoId('1234567890abcdef12345678')));
        $this->assertFalse($mongoDb->isValidObjectId('1234567890abcdef123456789'));
        $this->assertFalse($mongoDb->isValidObjectId('1234567890abcdef1234567'));
        $this->assertFalse($mongoDb->isValidObjectId('1234567890abcdef1234567g'));
    }

    public function testGetObjectId() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();

        $this->assertInstanceOf('MongoId', $mongoDb->getObjectId());

        $idString = '4cb4ab6d7addf98506010001';
        $mongoId = $mongoDb->getObjectId($idString);

        $this->assertSame($idString, $mongoId->{'$id'});
        $this->assertEquals($mongoId, $mongoDb->getObjectId($mongoId));
    }

    public function test_checkResultForErrors() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();

        CMTest_TH::callProtectedMethod($mongoDb, '_checkResultForErrors', [true]);
        CMTest_TH::callProtectedMethod($mongoDb, '_checkResultForErrors', [['ok' => 1]]);

        try {
            CMTest_TH::callProtectedMethod($mongoDb, '_checkResultForErrors', [false]);
        } catch (CM_MongoDb_Exception $ex) {
            $this->assertSame(['result' => false], $ex->getMetaInfo());
        }

        try {
            CMTest_TH::callProtectedMethod($mongoDb, '_checkResultForErrors', [['ok' => 0, 'errmsg' => 'foo']]);
        } catch (CM_MongoDb_Exception $ex) {
            $this->assertSame(['result' => ['ok' => 0, 'errmsg' => 'foo']], $ex->getMetaInfo());
        }
    }
}
