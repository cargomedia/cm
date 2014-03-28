<?php

class CM_Paging_Emoticon_AllTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearDb();
    }

    public function testAdd() {
        $paging = new CM_Paging_Emoticon_All();
        $paging->add(':smiley:', '1.png', array(':)', ':-)'));
        $paging->add(':<', '2.png');

        $emoticonList = $paging->getItems();
        $this->assertEquals(array(':smiley:', ':)', ':-)'), $emoticonList[0]['codes']);
        $this->assertEquals(array(':<'), $emoticonList[1]['codes']);
    }

    public function testSetAliases() {
        $emoticons = new CM_Paging_Emoticon_All();
        $emoticons->add(':foo:', 'foo.png');
        $items = $emoticons->getItems();
        $this->assertSame(array(':foo:'), $items[0]['codes']);

        $emoticons->setAliases(':foo:', array(':bar:', ':zoo:'));
        $items = $emoticons->getItems();
        $this->assertSame(array(':foo:', ':bar:', ':zoo:'), $items[0]['codes']);

        $emoticons->setAliases(':foo:', array(':bar:'));
        $items = $emoticons->getItems();
        $this->assertSame(array(':foo:', ':bar:'), $items[0]['codes']);
    }

    public function testAddAlias() {
        $emoticons = new CM_Paging_Emoticon_All();
        $emoticons->add(':foo:', 'foo.png');
        $items = $emoticons->getItems();
        $this->assertSame(array(':foo:'), $items[0]['codes']);

        $emoticons->addAlias(':foo:', ':bar:');
        $items = $emoticons->getItems();
        $this->assertSame(array(':foo:', ':bar:'), $items[0]['codes']);
    }
}
