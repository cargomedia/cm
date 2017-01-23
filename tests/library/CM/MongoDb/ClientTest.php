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

        $result = $mongoDb->updateOne($collectionName, null, ['$set' => ['groupId' => 5]]);
        $this->assertSame(1, $result);
        $this->assertSame(['_id' => 1, 'name' => 'Klaus', 'groupId' => 5], $mongoDb->findOne($collectionName, ['_id' => 1]));
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

        $result = $mongoDb->updateMany($collectionName, null, ['$set' => ['groupId' => 5]]);
        $this->assertSame(3, $result);
    }

    public function testReplaceOne() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'replace';
        $doc1 = ['name' => 'Bob', 'groupId' => 1];
        $doc2 = ['name' => 'Alice', 'groupId' => 1];
        $doc3 = ['name' => 'Dexter', 'groupId' => 2];
        $mongoDb->insert($collectionName, ['_id' => 1] + $doc1);
        $mongoDb->insert($collectionName, ['_id' => 2] + $doc2);
        $this->assertSame($doc1, $mongoDb->findOne($collectionName, null, ['_id' => 0]));

        $result = $mongoDb->replaceOne($collectionName, ['groupId' => 1], $doc3);
        $this->assertSame(1, $result);
        $this->assertSame($doc3, $mongoDb->findOne($collectionName, ['_id' => 1], ['_id' => 0]));

        $result = $mongoDb->replaceOne($collectionName, ['groupId' => 3], ['name' => 'foo']);
        $this->assertSame(0, $result);

        $result = $mongoDb->replaceOne($collectionName, null, $doc2);
        $this->assertSame(1, $result);
        $this->assertSame($doc2, $mongoDb->findOne($collectionName, ['_id' => 1], ['_id' => 0]));
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
        $this->assertEquals($expected, $mongoDb->find($collectionName, null, ['name' => 1, '_id' => 0]));

        // skip
        $expected = [$doc2, $doc3];
        $this->assertEquals($expected, $mongoDb->find($collectionName, null, ['_id' => 0], null, ['skip' => 1]));

        // limit
        $expected = [$doc1, $doc2];
        $this->assertEquals($expected, $mongoDb->find($collectionName, null, ['_id' => 0], null, ['limit' => 2]));

        // sort
        $expected = [$doc3, $doc1, $doc2];
        $this->assertEquals($expected, $mongoDb->find($collectionName, null, ['_id' => 0], null, ['sort' => ['groupId' => 1, 'userId'  => -1]]));

        // aggregation
        $result = $mongoDb->find($collectionName, ['groupId' => 1], ['_id' => 0, 'foo' => 1], [['$unwind' => '$foo']]);
        $this->assertEquals([['foo' => 1], ['foo' => 1], ['foo' => 2], ['foo' => 3]], $result);
    }

    public function testFindBatchSize() {
        $collectionName = 'findBatchSize';
        CM_Config::get()->CM_MongoDb_Client->batchSize = null;

        /** @var CM_MongoDb_Client|\Mocka\AbstractClassTrait $mongoDb */
        $mongoDb = $this->mockClass('CM_MongoDb_Client')->newInstanceWithoutConstructor();
        $collection = $this->mockClass('MongoDB\Collection')->newInstanceWithoutConstructor();
        $mongoDb->mockMethod('_getCollection')->set($collection);

        $mockFind = $collection->mockMethod('find')->set(function($criteria, $options) {
            $this->assertArrayNotHasKey('batchSize', $options);
            return new ArrayIterator([]);
        });
        $mongoDb->find($collectionName);
        $this->assertSame(1, $mockFind->getCallCount());

        CM_Config::get()->CM_MongoDb_Client->batchSize = 10;
        $mockFind = $collection->mockMethod('find')->set(function($criteria, $options) {
            $this->assertSame(10, $options['batchSize']);
            return new ArrayIterator([]);
        });
        $mongoDb->find($collectionName);
        $this->assertSame(2, $mockFind->getCallCount());

        $mockFind = $collection->mockMethod('find')->set(function($criteria, $options) {
            $this->assertSame(15, $options['batchSize']);
            return new ArrayIterator([]);
        });
        $mongoDb->find($collectionName, null, null, null, ['batchSize' => 15]);
        $this->assertSame(3, $mockFind->getCallCount());
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
        $this->assertEquals([['userId' => 1, 'score' => 2], ['userId' => 2, 'score' => 3]], $mongoDb->find($collectionName, null, ['_id' => 0]));

        $result = $mongoDb->findOneAndUpdate($collectionName, null, ['$inc' => ['score' => 1]], ['_id' => 0], ['sort' => ['userId' => 1]]);
        $this->assertSame(['userId' => 1, 'score' => 2], $result);
        $this->assertEquals([['userId' => 1, 'score' => 3], ['userId' => 2, 'score' => 3]], $mongoDb->find($collectionName, null, ['_id' => 0]));
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
        $this->assertEquals([['userId' => 2, 'score' => 2]], $mongoDb->find($collectionName, ['userId' => 2], ['_id' => 0]));

        $mongoDb->insert($collectionName, ['userId' => 3, 'score' => 3]);
        $this->assertEquals([['userId' => 2, 'score' => 2], ['userId' => 3, 'score' => 3]], $mongoDb->find($collectionName, null, ['_id' => 0]));

        $result = $mongoDb->findOneAndReplace($collectionName, null, ['userId' => 4, 'score' => 4], ['_id' => 0], ['sort' => ['userId' => 1]]);
        $this->assertSame(['userId' => 2, 'score' => 2], $result);
        $this->assertEquals([['userId' => 4, 'score' => 4], ['userId' => 3, 'score' => 3]], $mongoDb->find($collectionName, null, ['_id' => 0]));
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
        $this->assertEquals([['userId' => 1, 'score' => 1]], $mongoDb->find($collectionName, null, ['_id' => 0]));

        $mongoDb->insert($collectionName, ['userId' => 2, 'score' => 2]);
        $this->assertEquals([['userId' => 1, 'score' => 1], ['userId' => 2, 'score' => 2]], $mongoDb->find($collectionName, null, ['_id' => 0]));

        $result = $mongoDb->findOneAndDelete($collectionName, null, ['_id' => 0], ['sort' => ['userId' => 1]]);
        $this->assertSame(['userId' => 1, 'score' => 1], $result);
        $this->assertEquals([['userId' => 2, 'score' => 2]], $mongoDb->find($collectionName, null, ['_id' => 0]));
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

    public function testRename() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();

        $mongoDb->insert('source', ['foo' => 'origin']);
        $sourceDoc1 = $mongoDb->findOne('source');
        $this->assertTrue($mongoDb->existsCollection('source'));
        $this->assertFalse($mongoDb->existsCollection('target'));

        $mongoDb->rename('source', 'target');
        $this->assertFalse($mongoDb->existsCollection('source'));
        $this->assertTrue($mongoDb->existsCollection('target'));
        $this->assertEquals([$sourceDoc1], $mongoDb->find('target'));

        $mongoDb->insert('source', ['foo' => 'origin']);
        $sourceDoc2 = $mongoDb->findOne('source');

        $mongoDb->rename('source', 'target', true);
        $this->assertFalse($mongoDb->existsCollection('source'));
        $this->assertEquals([$sourceDoc2], $mongoDb->find('target'));
    }

    public function testRenameThrows() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        /** @var CM_MongoDb_Exception $exception */
        $exception = $this->catchException(function () use ($mongoDb) {
            $mongoDb->rename('not_defined', 'target');
        });
        $this->assertInstanceOf('CM_MongoDb_Exception', $exception);
        $this->assertSame('Source collection does not exist', $exception->getMessage());
        $this->assertSame([
            'collectionSource' => 'not_defined',
            'collectionTarget' => 'target'
        ], $exception->getMetaInfo());

        $mongoDb->insert('source', array('foo' => 'origin'));
        $mongoDb->insert('target', array('foo' => 'existing-value'));
        /** @var CM_MongoDb_Exception $exception */
        $exception = $this->catchException(function () use ($mongoDb) {
            $mongoDb->rename('source', 'target');
        });
        $this->assertInstanceOf('CM_MongoDb_Exception', $exception);
        $this->assertSame('Target collection already exists', $exception->getMessage());
        $this->assertSame([
            'collectionSource' => 'source',
            'collectionTarget' => 'target'
        ], $exception->getMetaInfo());
    }

    public function testDeleteMany() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'remove';
        $mongoDb->insert($collectionName, ['userId' => 1, 'name' => 'alice', 'groupId' => 1]);
        $mongoDb->insert($collectionName, ['userId' => 2, 'name' => 'steve', 'groupId' => 1]);
        $mongoDb->insert($collectionName, ['userId' => 3, 'name' => 'bob', 'groupId' => 1]);
        $mongoDb->insert($collectionName, ['userId' => 4, 'name' => 'dexter', 'groupId' => 2]);
        $this->assertSame(4, $mongoDb->count($collectionName));

        $this->assertSame(1, $mongoDb->deleteMany($collectionName, ['userId' => 2]));

        $this->assertSame(3, $mongoDb->count($collectionName));
        $this->assertSame(null, $mongoDb->findOne($collectionName, ['userId' => 2]));

        $this->assertSame(2, $mongoDb->deleteMany($collectionName, ['groupId' => 1]));
        $this->assertSame(1, $mongoDb->count($collectionName));
        $this->assertSame(null, $mongoDb->findOne($collectionName, ['userId' => 1]));
        $this->assertSame(null, $mongoDb->findOne($collectionName, ['userId' => 3]));

        $this->assertSame(1, $mongoDb->deleteMany($collectionName));
        $this->assertSame(0, $mongoDb->count($collectionName));
        $this->assertSame(null, $mongoDb->findOne($collectionName, ['userId' => 3]));

        $this->assertSame(0, $mongoDb->deleteMany($collectionName));
        $this->assertSame(null, $mongoDb->findOne($collectionName, ['userId' => 4]));
    }

    public function testDeleteOne() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'remove';
        $mongoDb->insert($collectionName, ['userId' => 1, 'name' => 'alice', 'groupId' => 1]);
        $mongoDb->insert($collectionName, ['userId' => 2, 'name' => 'steve', 'groupId' => 1]);
        $mongoDb->insert($collectionName, ['userId' => 3, 'name' => 'bob', 'groupId' => 1]);
        $mongoDb->insert($collectionName, ['userId' => 4, 'name' => 'dexter', 'groupId' => 2]);
        $this->assertSame(4, $mongoDb->count($collectionName));

        $this->assertSame(1, $mongoDb->deleteOne($collectionName, ['userId' => 2]));

        $this->assertSame(3, $mongoDb->count($collectionName));
        $this->assertSame(null, $mongoDb->findOne($collectionName, ['userId' => 2]));

        $this->assertSame(1, $mongoDb->deleteOne($collectionName, ['groupId' => 1]));
        $this->assertSame(2, $mongoDb->count($collectionName));
        $this->assertSame(null, $mongoDb->findOne($collectionName, ['userId' => 1]));

        $this->assertSame(1, $mongoDb->deleteOne($collectionName, ['groupId' => 1]));
        $this->assertSame(1, $mongoDb->count($collectionName));
        $this->assertSame(null, $mongoDb->findOne($collectionName, ['userId' => 3]));

        $this->assertSame(1, $mongoDb->deleteOne($collectionName));
        $this->assertSame(null, $mongoDb->findOne($collectionName, ['userId' => 4]));
        $this->assertSame(0, $mongoDb->deleteOne($collectionName));
    }

    public function testExistsCollection() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionName = 'existsCollection';

        $this->assertFalse($mongoDb->existsCollection($collectionName));
        $mongoDb->createCollection($collectionName);
        $this->assertTrue($mongoDb->existsCollection($collectionName));
    }

    public function testIsValidObjectId() {
        $this->assertTrue(CM_MongoDb_Client::isValidObjectId('1234567890abcdef12345678'));
        $this->assertTrue(CM_MongoDb_Client::isValidObjectId(new \MongoDB\BSON\ObjectID('1234567890abcdef12345678')));
        $this->assertFalse(CM_MongoDb_Client::isValidObjectId('1234567890abcdef123456789'));
        $this->assertFalse(CM_MongoDb_Client::isValidObjectId('1234567890abcdef1234567'));
        $this->assertFalse(CM_MongoDb_Client::isValidObjectId('1234567890abcdef1234567g'));
    }

    public function testGetObjectId() {
        $this->assertInstanceOf('\MongoDB\BSON\ObjectID', CM_MongoDb_Client::getObjectId());

        $idString = '4cb4ab6d7addf98506010001';
        $id1 = CM_MongoDb_Client::getObjectId($idString);

        $this->assertSame($idString, (string) $id1);
        $this->assertEquals($id1, CM_MongoDb_Client::getObjectId($id1));

        $id2 = CM_MongoDb_Client::getObjectId();
        $this->assertNotSame((string) $id1, (string) $id2);
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
