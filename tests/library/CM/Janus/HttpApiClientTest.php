<?php

class CM_Janus_HttpApiClientTest extends CMTest_TestCase {

    public function testStopStream() {
        $contextFormatter = $this->mockInterface('CM_Log_ContextFormatter_Interface')->newInstanceWithoutConstructor();
        $contextFormatter->mockMethod('formatAppContext')->set(['key' => 'value']);
        /** @var CM_Log_ContextFormatter_Interface $contextFormatter */

        $httpClient = $this->mockObject('GuzzleHttp\Client');
        $sendRequestMethod = $httpClient->mockMethod('send')->set(function (\GuzzleHttp\Psr7\Request $request) {
            $this->assertSame('http://cm-janus.dev:8080/stopStream?context={"key":"value"}', urldecode($request->getUri()));
            $this->assertSame('POST', $request->getMethod());
            $this->assertSame('streamId=foo', $request->getBody()->getContents());
            $this->assertSame('bar', $request->getHeaderLine('Server-Key'));

            return new \GuzzleHttp\Psr7\Response(200, [], '{"success":"Stream stopped"}');
        });
        /** @var GuzzleHttp\Client $httpClient */

        $location = $this->mockClass('CM_Geo_Point')->newInstanceWithoutConstructor();
        /** @var CM_Geo_Point $location */

        $server = new CM_Janus_Server(0, 'bar', 'http://cm-janus.dev:8080', 'ws://cm-janus.dev:8188', [], $location);

        $api = new CM_Janus_HttpApiClient($httpClient, $contextFormatter);
        $api->stopStream($server, 'foo');
        $this->assertSame(1, $sendRequestMethod->getCallCount());
    }

    public function testFetchStatus() {
        $contextFormatter = $this->mockInterface('CM_Log_ContextFormatter_Interface')->newInstanceWithoutConstructor();
        $contextFormatter->mockMethod('formatAppContext')->set(['key' => 'value']);
        /** @var CM_Log_ContextFormatter_Interface $contextFormatter */

        $httpClient = $this->mockObject('GuzzleHttp\Client');
        $sendRequestMethod = $httpClient->mockMethod('send')->set(function (\GuzzleHttp\Psr7\Request $request) {
            $this->assertSame('http://cm-janus.dev:8080/status?context={"key":"value"}', urldecode($request->getUri()));
            $this->assertSame('GET', $request->getMethod());
            $this->assertSame('bar', $request->getHeaderLine('Server-Key'));

            return new \GuzzleHttp\Psr7\Response(200, [], '[{"id":"foo", "channelName":"bar"},{"id":"baz", "channelName":"quux"}]');
        });
        /** @var GuzzleHttp\Client $httpClient */

        $location = $this->mockClass('CM_Geo_Point')->newInstanceWithoutConstructor();
        /** @var CM_Geo_Point $location */

        $server = new CM_Janus_Server(0, 'bar', 'http://cm-janus.dev:8080', 'ws://cm-janus.dev:8188', [], $location);

        $api = new CM_Janus_HttpApiClient($httpClient, $contextFormatter);
        $result = $api->fetchStatus($server);
        $this->assertSame([['id' => 'foo', 'channelName' => 'bar'], ['id' => 'baz', 'channelName' => 'quux']], $result);
        $this->assertSame(1, $sendRequestMethod->getCallCount());
    }

    public function testFail() {
        /** @var CM_Log_ContextFormatter_Interface $contextFormatter */
        $contextFormatter = $this->mockInterface('CM_Log_ContextFormatter_Interface')->newInstanceWithoutConstructor();

        /** @var GuzzleHttp\Client|\Mocka\AbstractClassTrait $httpClient */
        $httpClient = $this->mockObject('GuzzleHttp\Client');
        /** @var \Mocka\FunctionMock $sendFailMethod */
        $sendFailMethod = $httpClient->mockMethod('send')->set(function () {
            throw new GuzzleHttp\Exception\TransferException();
        });

        $location = $this->mockClass('CM_Geo_Point')->newInstanceWithoutConstructor();
        /** @var CM_Geo_Point $location */

        $server = new CM_Janus_Server(0, 'bar', 'http://cm-janus.dev:8080', 'ws://cm-janus.dev:8188', [], $location);

        $api = new CM_Janus_HttpApiClient($httpClient, $contextFormatter);
        $exception = $this->catchException(function () use ($api, $server) {
            $api->fetchStatus($server);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertStringStartsWith('Fetching contents from', $exception->getMessage());

        $this->assertSame(1, $sendFailMethod->getCallCount());

        $httpClient->mockMethod('send')->set(function () {
            return new \GuzzleHttp\Psr7\Response();
        });

        $exception = $this->catchException(function () use ($api, $server) {
            $api->fetchStatus($server);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Empty response body', $exception->getMessage());
        $this->assertSame(2, $sendFailMethod->getCallCount());
    }
}
