<?php

class CM_Janus_HttpApiClientTest extends CMTest_TestCase {

    public function testStopStream() {
        $httpClient = $this->mockObject('GuzzleHttp\Client');
        $sendRequestMethod = $httpClient->mockMethod('send')->set(function (\GuzzleHttp\Message\RequestInterface $request) {
            $this->assertSame('http://cm-janus.dev:8080/stopStream', $request->getUrl());
            $this->assertSame('POST', $request->getMethod());
            $this->assertSame('streamId=foo', $request->getBody()->getContents());
            $this->assertSame('bar', $request->getHeader('Server-Key'));

            $body = $this->mockClass('\GuzzleHttp\Post\PostBody')->newInstanceWithoutConstructor();
            $body->mockMethod('getContents')->set('{"success":"Stream stopped"}');

            $response = $this->mockClass('\GuzzleHttp\Message\Response')->newInstanceWithoutConstructor();
            $response->mockMethod('getBody')->set($body);
            return $response;
        });
        /** @var GuzzleHttp\Client $httpClient */

        $location = $this->mockClass('CM_Geo_Point')->newInstanceWithoutConstructor();
        /** @var CM_Geo_Point $location */
        
        $server = new CM_Janus_Server(0, 'bar', 'http://cm-janus.dev:8080', 'ws://cm-janus.dev:8188', [], $location);
        $api = new CM_Janus_HttpApiClient($httpClient);
        $api->stopStream($server, 'foo');
        $this->assertSame(1, $sendRequestMethod->getCallCount());
    }

    public function testFetchStatus() {
        $httpClient = $this->mockObject('GuzzleHttp\Client');
        $sendRequestMethod = $httpClient->mockMethod('send')->set(function (\GuzzleHttp\Message\RequestInterface $request) {
            $this->assertSame('http://cm-janus.dev:8080/status', $request->getUrl());
            $this->assertSame('GET', $request->getMethod());
            $this->assertSame('bar', $request->getHeader('Server-Key'));

            $body = $this->mockClass('\GuzzleHttp\Post\PostBody')->newInstanceWithoutConstructor();
            $body->mockMethod('getContents')->set('[{"id":"foo", "channelName":"bar"},{"id":"baz", "channelName":"quux"}]');

            $response = $this->mockClass('\GuzzleHttp\Message\Response')->newInstanceWithoutConstructor();
            $response->mockMethod('getBody')->set($body);
            return $response;
        });
        /** @var GuzzleHttp\Client $httpClient */

        $location = $this->mockClass('CM_Geo_Point')->newInstanceWithoutConstructor();
        /** @var CM_Geo_Point $location */
        
        $server = new CM_Janus_Server(0, 'bar', 'http://cm-janus.dev:8080', 'ws://cm-janus.dev:8188', [], $location);
        $api = new CM_Janus_HttpApiClient($httpClient);
        $result = $api->fetchStatus($server);
        $this->assertSame([['id' => 'foo', 'channelName' => 'bar'], ['id' => 'baz', 'channelName' => 'quux']], $result);
        $this->assertSame(1, $sendRequestMethod->getCallCount());
    }

    public function testFail() {
        /** @var GuzzleHttp\Client|\Mocka\AbstractClassTrait $httpClient */
        $httpClient = $this->mockObject('GuzzleHttp\Client');
        /** @var \Mocka\FunctionMock $sendFailMethod */
        $sendFailMethod = $httpClient->mockMethod('send')->set(function () {
            throw new GuzzleHttp\Exception\TransferException();
        });

        $location = $this->mockClass('CM_Geo_Point')->newInstanceWithoutConstructor();
        /** @var CM_Geo_Point $location */

        $server = new CM_Janus_Server(0, 'bar', 'http://cm-janus.dev:8080', 'ws://cm-janus.dev:8188', [], $location);
        $api = new CM_Janus_HttpApiClient($httpClient);
        $exception = $this->catchException(function () use ($api, $server) {
            $api->fetchStatus($server);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertStringStartsWith('Fetching contents from', $exception->getMessage());

        $this->assertSame(1, $sendFailMethod->getCallCount());

        $httpClient->mockMethod('send')->set(function () {
            $response = $this->mockClass('\GuzzleHttp\Message\Response')->newInstanceWithoutConstructor();
            $response->mockMethod('getBody')->set(null);
            return $response;
        });

        $exception = $this->catchException(function () use ($api, $server) {
            $api->fetchStatus($server);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Empty response body', $exception->getMessage());
        $this->assertSame(2, $sendFailMethod->getCallCount());
    }
}
