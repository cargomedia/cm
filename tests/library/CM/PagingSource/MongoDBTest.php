<?php

class CM_PagingSource_MongoDB_Test extends CMTest_TestCase {

    /** @var CM_Mongodb_Client */
    private $_mongodb;
    private $_collection = 'unitTest';

    private function _getRecipientData()
    {
        return array(
            'userId' => rand(0, 1000),
            'read' => 0,
            'blocked' => 0,
            'deleted' => 0,
            'createStamp' => 0,
            'receiveStamp' => null,
            'createStamp' => time(),
            'sendStamp' => null
        );
    }

    private function _getConversationObject()
    {
        $messages = array();
        for ($i=0; $i<20; $i++) {
            $messages[] = str_repeat(md5(rand()), 4); // 128 char-long message
        }
        return array(
            'id' => md5(rand()),
            'usedId' => rand(0, 1000),
            'createStamp' => time(),
            'recipients' => array($this->_getRecipientData(), $this->_getRecipientData()),
            'messages' => $messages
        );
    }


    public function setUp() {
        $this->_mongodb = CM_MongoDB_Client::getInstance();
        $this->tearDown(); // cleanup all failed tests leftovers

        $this->_mongodb->insert($this->_collection, $this->_getConversationObject());
        $this->_mongodb->insert($this->_collection, $this->_getConversationObject());
        $this->_mongodb->insert($this->_collection, $this->_getConversationObject());
        $this->_mongodb->insert($this->_collection, $this->_getConversationObject());
        $this->_mongodb->insert($this->_collection, $this->_getConversationObject());
        $this->_mongodb->insert($this->_collection, $this->_getConversationObject());
        $this->_mongodb->insert($this->_collection, $this->_getConversationObject());
        $this->_mongodb->insert($this->_collection, $this->_getConversationObject());

        $sentConversation = $this->_getConversationObject();
        $sentConversation['userId'] = 1001;
        $sentConversation['recipients'][0]['userId'] = 1001;
        $sentConversation['recipients'][0]['sentStamp'] = 123123;
        $sentConversation['messages'][] = 'testing';
        $this->_mongodb->insert($this->_collection, $sentConversation);
    }

    public function tearDown() {
        $this->_mongodb->drop($this->_collection);
    }

    public function testCount() {
        $source = new CM_PagingSource_MongoDB(array('recipients'), $this->_collection, array('recipients.userId' => 1001));
        $this->assertSame(1, $source->getCount());

        $sourceEmpty = new CM_PagingSource_MongoDB(array('recipients'), $this->_collection, array('recipients.userId' => 1002));
        $this->assertSame(0, $sourceEmpty->getCount());
    }

    public function testSearch() {
        $source = new CM_PagingSource_MongoDB(array('messages'), $this->_collection, array('recipients.userId' => 1001));
        $this->assertSame(1, $source->getCount());
        $items = $source->getItems();
        $this->assertContains('testing', $items[0]['messages']);
    }
}
