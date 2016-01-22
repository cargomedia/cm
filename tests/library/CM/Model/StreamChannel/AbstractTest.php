<?php

class CM_Model_StreamChannel_AbstractTest extends CMTest_TestCase {

    public function setUp() {
        CM_Config::get()->CM_Model_Abstract->types[CM_Model_StreamChannel_Mock::getTypeStatic()] = 'CM_Model_StreamChannel_Mock';
        if (!class_exists('CM_Model_StreamChannel_Mock')) {
            $this->getMockForAbstractClass('CM_Model_StreamChannel_Abstract', array(), 'CM_Model_StreamChannel_Mock', false);
        }
    }

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testConstructor() {
        try {
            new CM_Model_StreamChannel_Mock(123123);
            $this->fail('Can instantiate streamChannel without data.');
        } catch (CM_Exception_Nonexistent $ex) {
            $this->assertTrue(true);
        }
    }

    public function testFactory() {
        $streamChannel1 = CM_Model_StreamChannel_Media::createStatic(array('key'         => 'dsljkfk34asdd', 'serverId' => 1,
                                                                           'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic(),
                                                                           'width'       => 100,
                                                                           'height'      => 100, 'thumbnailCount' => 0));
        $streamChannel2 = CM_Model_StreamChannel_Abstract::factory($streamChannel1->getId());
        $this->assertEquals($streamChannel1, $streamChannel2);

        $streamChannel1 = CM_Model_StreamChannel_Message::createStatic(array('key'         => 'asdasdaasadgss',
                                                                             'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic()));
        $streamChannel2 = CM_Model_StreamChannel_Abstract::factory($streamChannel1->getId());
        $this->assertEquals($streamChannel1, $streamChannel2);
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Unexpected instance of
     */
    public function testFactoryInvalidInstance() {
        $messageStreamChannel = CM_Model_StreamChannel_Message::createStatic(array('key'         => 'message-stream-channel',
                                                                                   'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic()));
        CM_Model_StreamChannel_Media::factory($messageStreamChannel->getId());
    }

    public function testFindByKeyAndAdapter() {
        $adapterType = 1;
        /** @var CM_Model_StreamChannel_Media $streamChannelOriginal */
        $streamChannelOriginal = CMTest_TH::createStreamChannel(null, $adapterType);
        $streamChannelKey = $streamChannelOriginal->getKey();
        $streamChannel = CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamChannelKey, $adapterType);
        $this->assertInstanceOf('CM_Model_StreamChannel_Media', $streamChannel);
        $this->assertEquals($streamChannelOriginal->getId(), $streamChannel->getId());

        $streamChannelOriginal->delete();
        $this->assertNull(CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamChannelKey, $adapterType));
    }

    /**
     * @expectedException CM_Exception_Nonexistent
     */
    public function testDelete() {
        /** @var CM_Model_StreamChannel_Mock $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Mock::createStatic(array('key'         => 'bar',
                                                                         'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic()));
        $streamChannel->delete();
        new CM_Model_StreamChannel_Mock($streamChannel->getId());
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Cannot delete streamChannel with existing streams
     */
    public function testDeleteWithSubscribes() {
        /** @var CM_Model_StreamChannel_Mock $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Mock::createStatic(array('key'         => 'bar-with-subscribers',
                                                                         'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic()));
        CM_Model_Stream_Publish::createStatic(array('streamChannel' => $streamChannel, 'user' => CMTest_TH::createUser(), 'start' => 123123,
                                                    'key'           => '123123_1',));
        CM_Model_Stream_Publish::createStatic(array('streamChannel' => $streamChannel, 'user' => CMTest_TH::createUser(), 'start' => 123123,
                                                    'key'           => '123123_2',));
        CM_Model_Stream_Subscribe::createStatic(array('streamChannel' => $streamChannel, 'user' => CMTest_TH::createUser(), 'start' => 123123,
                                                      'key'           => '133123_3'));
        CM_Model_Stream_Subscribe::createStatic(array('streamChannel' => $streamChannel, 'user' => CMTest_TH::createUser(), 'start' => 123123,
                                                      'key'           => '133123_4'));
        $this->assertEquals(2, $streamChannel->getStreamPublishs()->getCount());
        $this->assertEquals(2, $streamChannel->getStreamSubscribes()->getCount());
        $streamChannel->delete();
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Cannot delete streamChannel with existing streams
     */
    public function testDeleteWithPublishs() {
        /** @var CM_Model_StreamChannel_Mock $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        CMTest_TH::createStreamPublish(null, $streamChannel);
        $streamChannel->delete();
    }

    public function testGetSubscribers() {
        /** @var CM_Model_StreamChannel_Mock $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Mock::createStatic(array('key'         => 'bar',
                                                                         'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic()));
        $this->assertEquals(0, $streamChannel->getSubscribers()->getCount());
        $streamSubscribe = CM_Model_Stream_Subscribe::createStatic(array('streamChannel' => $streamChannel, 'user' => CMTest_TH::createUser(),
                                                                         'start'         => 123123,
                                                                         'key'           => '111_1'));
        $this->assertEquals(1, $streamChannel->getSubscribers()->getCount());
        $user = CMTest_TH::createUser();
        CM_Model_Stream_Subscribe::createStatic(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123,
                                                      'key'           => '111_2'));
        $this->assertEquals(2, $streamChannel->getSubscribers()->getCount());
        CM_Model_Stream_Subscribe::createStatic(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123,
                                                      'key'           => '111_3'));
        $this->assertEquals(2, $streamChannel->getSubscribers()->getCount());
        $streamSubscribe->delete();
        $this->assertEquals(1, $streamChannel->getSubscribers()->getCount());
    }

    public function testGetPublishers() {
        /** @var CM_Model_StreamChannel_Mock $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Mock::createStatic(array('key'         => 'bar1',
                                                                         'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic()));
        $this->assertEquals(0, $streamChannel->getPublishers()->getCount());
        $streamPublish = CM_Model_Stream_Publish::createStatic(array('streamChannel' => $streamChannel, 'user' => CMTest_TH::createUser(),
                                                                     'start'         => 123123,
                                                                     'key'           => '111_1'));
        $this->assertEquals(1, $streamChannel->getPublishers()->getCount());
        $user = CMTest_TH::createUser();
        CM_Model_Stream_Publish::createStatic(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123,
                                                    'key'           => '111_2'));
        $this->assertEquals(2, $streamChannel->getPublishers()->getCount());
        CM_Model_Stream_Publish::createStatic(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123,
                                                    'key'           => '111_3'));
        $this->assertEquals(2, $streamChannel->getPublishers()->getCount());
        $streamPublish->delete();
        $this->assertEquals(1, $streamChannel->getPublishers()->getCount());
    }

    public function testGetUsers() {
        /** @var CM_Model_StreamChannel_Mock $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Mock::createStatic(array('key'         => 'bar2',
                                                                         'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic()));
        $this->assertEquals(0, $streamChannel->getUsers()->getCount());
        $user = CMTest_TH::createUser();
        CM_Model_Stream_Publish::createStatic(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123,
                                                    'key'           => '112_1'));
        $this->assertEquals(1, $streamChannel->getUsers()->getCount());
        CM_Model_Stream_Publish::createStatic(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123,
                                                    'key'           => '112_2'));
        $this->assertEquals(1, $streamChannel->getUsers()->getCount());
        CM_Model_Stream_Publish::createStatic(array('streamChannel' => $streamChannel, 'user' => $user, 'start' => 123123,
                                                    'key'           => '112_3'));
        $this->assertEquals(1, $streamChannel->getUsers()->getCount());
        CM_Model_Stream_Publish::createStatic(array('streamChannel' => $streamChannel, 'user' => CMTest_TH::createUser(), 'start' => 123123,
                                                    'key'           => '112_4'));
        $this->assertEquals(2, $streamChannel->getUsers()->getCount());
    }

    public function testCreate() {
        /** @var CM_Model_StreamChannel_Abstract $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Abstract::createType(CM_Model_StreamChannel_Message::getTypeStatic(), array('key'         => 'foo1',
                                                                                                                            'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic()));
        $this->assertInstanceOf('CM_Model_StreamChannel_Message', $streamChannel);
        $this->assertSame('foo1', $streamChannel->getKey());
        $this->assertEquals(CM_MessageStream_Adapter_SocketRedis::getTypeStatic(), $streamChannel->getAdapterType());
        $this->assertSame(time(), $streamChannel->getCreateStamp());

        /** @var CM_Model_StreamChannel_Abstract $channel1 */
        $channel1 = CM_Model_StreamChannel_Abstract::createType(CM_Model_StreamChannel_Message::getTypeStatic(), [
            'key'         => 'foo',
            'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic(),
        ]);
        /** @var CM_Model_StreamChannel_Abstract $channel2 */
        $channel2 = CM_Model_StreamChannel_Abstract::createType(CM_Model_StreamChannel_Message::getTypeStatic(), [
            'key'         => 'foo',
            'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic(),
        ]);
        $this->assertInstanceOf('CM_Model_StreamChannel_Message', $channel1);
        $this->assertInstanceOf('CM_Model_StreamChannel_Message', $channel2);
        $this->assertSame($channel1->getId(), $channel2->getId());
        $this->assertSame($channel1->getCreateStamp(), $channel2->getCreateStamp());
    }

    public function testEncryptAndDecryptKey() {
        $data = 'foo ';
        $encryptionKey = 'qwertyuiopasdfgh';
        $encryptMethod = new ReflectionMethod('CM_Model_StreamChannel_Abstract', '_encryptKey');
        $encryptMethod->setAccessible(true);
        $encryptedData = $encryptMethod->invoke(null, $data, $encryptionKey);

        $streamChannel = $this->getMockBuilder('CM_Model_StreamChannel_Abstract')->setMethods(array('getKey'))->disableOriginalConstructor()->getMockForAbstractClass();
        $streamChannel->expects($this->any())->method('getKey')->will($this->returnValue($encryptedData));
        $decryptMethod = new ReflectionMethod('CM_Model_StreamChannel_Abstract', '_decryptKey');
        $decryptMethod->setAccessible(true);
        $decryptedData = $decryptMethod->invoke($streamChannel, $encryptionKey);
        $this->assertSame($data, $decryptedData);
    }

    public function testIsSubscriber() {
        /** @var CM_Model_StreamChannel_Mock $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Mock::createStatic(
            array('key' => 'foo', 'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic()));
        $user1 = CMTest_TH::createUser();
        $subscribe1 = CM_Model_Stream_Subscribe::createStatic(
            array('streamChannel' => $streamChannel, 'user' => $user1, 'start' => 123123, 'key' => '1'));
        $subscribe2 = CM_Model_Stream_Subscribe::createStatic(
            array('streamChannel' => $streamChannel, 'user' => $user1, 'start' => 123123, 'key' => '2'));

        $this->assertTrue($streamChannel->isSubscriber($user1));
        $this->assertTrue($streamChannel->isSubscriber($user1, $subscribe1));

        $subscribe2->delete();
        $this->assertTrue($streamChannel->isSubscriber($user1));
        $this->assertFalse($streamChannel->isSubscriber($user1, $subscribe1));

        $subscribe1->delete();
        $this->assertFalse($streamChannel->isSubscriber($user1));
    }

    public function testIsPublisher() {
        /** @var CM_Model_StreamChannel_Mock $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Mock::createStatic(
            array('key' => 'foo', 'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic()));
        $user1 = CMTest_TH::createUser();
        $publish1 = CM_Model_Stream_Publish::createStatic(
            array('streamChannel' => $streamChannel, 'user' => $user1, 'start' => 123123, 'key' => '1'));
        $publish2 = CM_Model_Stream_Publish::createStatic(
            array('streamChannel' => $streamChannel, 'user' => $user1, 'start' => 123123, 'key' => '2'));

        $this->assertTrue($streamChannel->isPublisher($user1));
        $this->assertTrue($streamChannel->isPublisher($user1, $publish1));

        $publish2->delete();
        $this->assertTrue($streamChannel->isPublisher($user1));
        $this->assertFalse($streamChannel->isPublisher($user1, $publish1));

        $publish1->delete();
        $this->assertFalse($streamChannel->isPublisher($user1));
    }

    public function testIsSubscriberOrPublisher() {
        /** @var CM_Model_StreamChannel_Mock $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Mock::createStatic(
            array('key' => 'foo', 'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic()));
        $user1 = CMTest_TH::createUser();
        $publish1 = CM_Model_Stream_Publish::createStatic(
            array('streamChannel' => $streamChannel, 'user' => $user1, 'start' => 123123, 'key' => '1'));
        $subscribe1 = CM_Model_Stream_Subscribe::createStatic(
            array('streamChannel' => $streamChannel, 'user' => $user1, 'start' => 123123, 'key' => '2'));

        $this->assertTrue($streamChannel->isSubscriberOrPublisher($user1));
        $this->assertTrue($streamChannel->isSubscriberOrPublisher($user1, $publish1));

        $subscribe1->delete();
        $this->assertTrue($streamChannel->isSubscriberOrPublisher($user1));
        $this->assertFalse($streamChannel->isSubscriberOrPublisher($user1, $publish1));

        $publish1->delete();
        $this->assertFalse($streamChannel->isSubscriberOrPublisher($user1));
    }
}

class CM_Model_StreamChannel_Mock extends CM_Model_StreamChannel_Abstract {

    /**
     * @param CM_Model_Stream_Publish $streamPublish
     */
    public function onPublish(CM_Model_Stream_Publish $streamPublish) {
    }

    /**
     * @param CM_Model_Stream_Subscribe $streamSubscribe
     */
    public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
    }

    /**
     * @param CM_Model_Stream_Publish $streamPublish
     */
    public function onUnpublish(CM_Model_Stream_Publish $streamPublish) {
    }

    /**
     * @param CM_Model_Stream_Subscribe $streamSubscribe
     */
    public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
    }

    public static function getTypeStatic() {
        return 1;
    }
}
