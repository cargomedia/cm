<?php

class CMService_MongoDBTest extends CMTest_TestCase {

    private $_collectionPrefix = 'UnitTest_';

    /**
     * Generate a name of a collection and ensure it's empty
     * @param string $testName
     * @return string
     */
    private function getEmptyCollectionName($testName) {
        $collectionName = $this->_collectionPrefix . $testName;
        $mdb = CM_Services::getInstance()->getMongoDB();
        $mdb->getCollection($collectionName)->drop();

        return $collectionName;
    }

    /**
     * Test insert
     */
    public function testInsert() {
        $mdb = CM_Services::getInstance()->getMongoDB();
        $collectionName = $this->getEmptyCollectionName('insert');
        $name = 'Bob';
        $userId = 123;
        $mdb->insert($collectionName, array('userId' => $userId, 'name' => $name));
        $res = $mdb->findOne($collectionName, array('userId' => $userId));
        $this->assertSame($res['name'], $name);
    }

    /**
     * Test update
     */
    public function testUpdate() {
        $mdb = CM_Services::getInstance()->getMongoDB();
        $collectionName = $this->getEmptyCollectionName('update');
        $name = 'Bob';
        $userId = 123;
        $mdb->insert($collectionName, array('userId' => $userId, 'name' => $name));
        $res = $mdb->findOne($collectionName, array('userId' => $userId));
        $this->assertSame($res['name'], $name);

        $mdb->update($collectionName, array('userId' => $userId), array('$set' => array('name' => 'Alice')));
        $res = $mdb->findOne($collectionName, array('userId' => $userId));
        $this->assertSame($res['name'], 'Alice');

        $collectionName = $this->getEmptyCollectionName('update2');
        $mdb->insert($collectionName, array('messageId'  => 1,
                                            'recipients' => array(array('userId' => 1, 'read' => 0), array('userId' => 2, 'read' => 0))));
        $mdb->update($collectionName, array('messageId' => 1, 'recipients.userId' => 2), array('$set' => array('recipients.$.read' => 1)));

        $message = $mdb->findOne($collectionName, array('messageId' => 1));
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
     * Test getNewId
     *
     * NOTE: this one actually tests is the returned id isn't empty rather than if it's unique... which would be hard to test.
     */
    public function testGetNewId() {
        $mdb = CM_Services::getInstance()->getMongoDB();
        $id1 = $mdb->getNewId();
        $id2 = $mdb->getNewId();
        $this->assertNotSame($id1, $id2);
    }

    /**
     * Test find
     */
    public function testFind() {
        $mdb = CM_Services::getInstance()->getMongoDB();
        $collectionName = $this->getEmptyCollectionName('find');

        $mdb->insert($collectionName, array('userId' => 1, 'groupId' => 1, 'name' => 'alice'));
        $mdb->insert($collectionName, array('userId' => 2, 'groupId' => 2, 'name' => 'steve'));
        $mdb->insert($collectionName, array('userId' => 3, 'groupId' => 1, 'name' => 'bob'));
        $users = $mdb->find($collectionName, array('groupId' => 1));
        $this->assertSame(2, $users->count());
        $expectedNames = array('alice', 'bob');
        foreach ($users as $user) {
            $expectedNames = array_diff($expectedNames, array($user['name']));
        }
        $this->assertEmpty($expectedNames);
    }

    /**
     * Test Count
     */
    public function testCount() {
        $mdb = CM_Services::getInstance()->getMongoDB();
        $collectionName = $this->getEmptyCollectionName('count');
        $this->assertSame(0, $mdb->getCollection($collectionName)->count());
        $mdb->insert($collectionName, array('userId' => 1, 'name' => 'alice'));
        $mdb->insert($collectionName, array('userId' => 2, 'name' => 'steve'));
        $mdb->insert($collectionName, array('userId' => 3, 'name' => 'bob'));
        $this->assertSame(3, $mdb->getCollection($collectionName)->count());
    }

    /**
     * Test remove
     */
    public function testRemove() {
        $mdb = CM_Services::getInstance()->getMongoDB();
        $collectionName = $this->getEmptyCollectionName('remove');
        $mdb->insert($collectionName, array('userId' => 1, 'name' => 'alice'));
        $mdb->insert($collectionName, array('userId' => 2, 'name' => 'steve'));
        $mdb->insert($collectionName, array('userId' => 3, 'name' => 'bob'));
        $this->assertSame(3, $mdb->getCollection($collectionName)->count());

        $mdb->remove($collectionName, array('userId' => 2));

        $this->assertSame(2, $mdb->getCollection($collectionName)->count());
        $this->assertSame(0, $mdb->find($collectionName, array('userId' => 2))->count());
    }
    /*
        // TODO?
        public function testGetDefaultDatabase() {
            $mongo = CM_Services::getInstance()->getMongoDB();
            $db = $mongo->getDatabase();
            $this->assertInstanceOf('MongoDB', $db); // todo: check db name
        }

        public function testGetNonDefaultDatabase() {
            $mongo = CM_Services::getInstance()->getMongoDB('test');
            $db = $mongo->getDatabase();
            $this->assertInstanceOf('MongoDB', $db); // todo: check db name
        }
    */
}
