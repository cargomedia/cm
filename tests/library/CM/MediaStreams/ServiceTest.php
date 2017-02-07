<?php

class CM_MediaStreams_ServiceTest extends CMTest_TestCase {

    public function testCheckStreamsInvalidStreamChannel() {
        $streamPublish = $this->mockClass('CM_Model_Stream_Publish')->newInstanceWithoutConstructor();
        $streamSubscribe = $this->mockClass('CM_Model_Stream_Subscribe')->newInstanceWithoutConstructor();

        $streamChannel = $this->mockClass('CM_Model_StreamChannel_Media', ['CM_StreamChannel_DisallowInterface'])->newInstanceWithoutConstructor();
        $streamChannel->mockMethod('isValid')->set(false);
        $streamChannel->mockMethod('findStreamPublish')->set($streamPublish);
        $streamChannel->mockMethod('getStreamSubscribes')->set([$streamSubscribe]);

        $streamRepository = $this->mockClass('CM_MediaStreams_StreamRepository')->newInstanceWithoutConstructor();
        $streamRepository->mockMethod('getStreamChannels')->set([$streamChannel]);
        /** @var CM_MediaStreams_StreamRepository $streamRepository */

        $service = $this->mockObject('CM_MediaStreams_Service', [$streamRepository]);
        $stopStreamMethod = $service->mockMethod('_stopStream')
            ->at(0, function ($stream) use ($streamPublish) {
                $this->assertSame($streamPublish, $stream);
            })
            ->at(1, function ($stream) use ($streamSubscribe) {
                $this->assertSame($streamSubscribe, $stream);
            });

        /** @var CM_MediaStreams_Service $service */

        $service->checkStreams();
        $this->assertSame(2, $stopStreamMethod->getCallCount());
    }

    public function testCheckStreamsValidStreamChannel() {
        $streamPublish = $this->mockClass('CM_Model_Stream_Publish')->newInstanceWithoutConstructor();
        $streamSubscribe = $this->mockClass('CM_Model_Stream_Subscribe')->newInstanceWithoutConstructor();

        $streamChannel = $this->mockClass('CM_Model_StreamChannel_Media', ['CM_StreamChannel_DisallowInterface'])->newInstanceWithoutConstructor();
        $streamChannel->mockMethod('isValid')->set(true);
        $streamChannel->mockMethod('findStreamPublish')->set($streamPublish);
        $streamChannel->mockMethod('getStreamSubscribes')->set([$streamSubscribe]);

        $streamRepository = $this->mockClass('CM_MediaStreams_StreamRepository')->newInstanceWithoutConstructor();
        $streamRepository->mockMethod('getStreamChannels')->set([$streamChannel, $streamChannel]);
        /** @var CM_MediaStreams_StreamRepository $streamRepository */

        $service = $this->mockObject('CM_MediaStreams_Service', [$streamRepository]);
        $service->mockMethod('_isPublishAllowed')
            ->at(0, true)
            ->at(1, false);
        $service->mockMethod('_isSubscribeAllowed')
            ->at(0, false)
            ->at(1, false);
        $stopStreamMethod = $service->mockMethod('_stopStream')
            ->at(0, function ($stream) use ($streamSubscribe) {
                $this->assertSame($streamSubscribe, $stream);
            })
            ->at(1, function ($stream) use ($streamPublish) {
                $this->assertSame($streamPublish, $stream);
            })
            ->at(2, function ($stream) use ($streamSubscribe) {
                $this->assertSame($streamSubscribe, $stream);
            });

        /** @var CM_MediaStreams_Service $service */

        $service->checkStreams();
        $this->assertSame(3, $stopStreamMethod->getCallCount());
    }

    public function testCheckStreamsNoConnectionsDisallowed() {
        $streamPublish = $this->mockClass('CM_Model_Stream_Publish')->newInstanceWithoutConstructor();
        $streamSubscribe = $this->mockClass('CM_Model_Stream_Subscribe')->newInstanceWithoutConstructor();

        $streamChannel = $this->mockClass('CM_Model_StreamChannel_Media')->newInstanceWithoutConstructor();
        $isValidMethod = $streamChannel->mockMethod('isValid');
        $streamChannel->mockMethod('findStreamPublish')->set($streamPublish);
        $streamChannel->mockMethod('getStreamSubscribes')->set([$streamSubscribe]);

        $streamRepository = $this->mockClass('CM_MediaStreams_StreamRepository')->newInstanceWithoutConstructor();
        $streamRepository->mockMethod('getStreamChannels')->set([$streamChannel]);
        /** @var CM_MediaStreams_StreamRepository $streamRepository */

        $service = $this->mockObject('CM_MediaStreams_Service', [$streamRepository]);
        $_isPublishAllowedMethod = $service->mockMethod('_isPublishAllowed');
        $_isSubscribeAllowedMethod = $service->mockMethod('_isSubscribeAllowed');
        /** @var CM_MediaStreams_Service $service */

        $service->checkStreams();
        $this->assertSame(0, $isValidMethod->getCallCount());
        $this->assertSame(0, $_isPublishAllowedMethod->getCallCount());
        $this->assertSame(0, $_isSubscribeAllowedMethod->getCallCount());
    }
}
