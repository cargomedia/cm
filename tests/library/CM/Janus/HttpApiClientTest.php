<?php

class CM_Janus_HttpApiClientTest extends CMTest_TestCase {

    public function testStopStream() {
        $httpClient = $this->mockObject('GuzzleHttp\Client');
        $sendRequestMethod = $httpClient->mockMethod('send')->set(function (\GuzzleHttp\Message\RequestInterface $request) {
            $this->assertSame('http://cm-janus.dev:8080/stopStream', $request->getUrl());
            $this->assertSame('POST', $request->getMethod());
            $this->assertSame('streamId=foo&token=bar', $request->getBody()->getContents());

            $response = $this->mockClass('\GuzzleHttp\Message\Response')->newInstanceWithoutConstructor();
            $response->mockMethod('getBody')->set(null);
            return $response;
        });
        /** @var GuzzleHttp\Client $httpClient */

        $server = new CM_Janus_Server(0, 'bar', 'http://cm-janus.dev:8080', 'ws://cm-janus.dev:8188');
        $api = new CM_Janus_HttpApiClient($httpClient);
        $api->stopStream($server, 'foo');
        $this->assertSame(1, $sendRequestMethod->getCallCount());
    }

    public function testFetchStatus() {
        $httpClient = $this->mockObject('GuzzleHttp\Client');
        $sendRequestMethod = $httpClient->mockMethod('send')->set(function (\GuzzleHttp\Message\RequestInterface $request) {
            $this->assertSame('http://cm-janus.dev:8080/status', $request->getUrl());
            $this->assertSame('GET', $request->getMethod());

            $response = $this->mockClass('\GuzzleHttp\Message\Response')->newInstanceWithoutConstructor();
            $response->mockMethod('getBody')->set('{"foo":"bar"}');
            return $response;
        });
        /** @var GuzzleHttp\Client $httpClient */

        $server = new CM_Janus_Server(0, 'bar', 'http://cm-janus.dev:8080', 'ws://cm-janus.dev:8188');
        $api = new CM_Janus_HttpApiClient($httpClient);
        $result = $api->fetchStatus($server);
        $this->assertSame(['foo' => 'bar'], $result);
        $this->assertSame(1, $sendRequestMethod->getCallCount());
    }
}
