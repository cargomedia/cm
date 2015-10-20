<?php

class CM_Janus_HttpApiClientTest extends CMTest_TestCase {

    public function testStopClient() {
        $httpClient = $this->mockObject('GuzzleHttp\Client');
        $sendRequestMethod = $httpClient->mockMethod('send')->set(function (\GuzzleHttp\Message\RequestInterface $request) {
            $this->assertSame('http://example.com/stop', $request->getUrl());
            $this->assertSame('POST', $request->getMethod());
            $this->assertSame('clientId=foo', $request->getBody()->getContents());

            $response = $this->mockClass('\GuzzleHttp\Message\Response')->newInstanceWithoutConstructor();
            $response->mockMethod('getBody')->set(null);
            return $response;
        });
        /** @var GuzzleHttp\Client $httpClient */

        $janusServer = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $janusServer->mockMethod('getPrivateHost')->set('example.com');
        /** @var CM_Janus_Server $janusServer */

        $api = new CM_Janus_HttpApiClient($httpClient);
        $api->stopClient($janusServer, 'foo');
        $this->assertSame(1, $sendRequestMethod->getCallCount());
    }

    public function testFetchStatus() {
        $httpClient = $this->mockObject('GuzzleHttp\Client');
        $sendRequestMethod = $httpClient->mockMethod('send')->set(function (\GuzzleHttp\Message\RequestInterface $request) {
            $this->assertSame('http://example.com/status', $request->getUrl());
            $this->assertSame('GET', $request->getMethod());

            $response = $this->mockClass('\GuzzleHttp\Message\Response')->newInstanceWithoutConstructor();
            $response->mockMethod('getBody')->set('{"foo":"bar"}');
            return $response;
        });
        /** @var GuzzleHttp\Client $httpClient */

        $janusServer = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $janusServer->mockMethod('getPrivateHost')->set('example.com');
        /** @var CM_Janus_Server $janusServer */

        $api = new CM_Janus_HttpApiClient($httpClient);
        $result = $api->fetchStatus($janusServer);
        $this->assertSame(['foo' => 'bar'], $result);
        $this->assertSame(1, $sendRequestMethod->getCallCount());
    }
}
