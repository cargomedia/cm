<?php

class CM_Wowza_HttpApiClientTest extends CMTest_TestCase {

    public function testStopClient() {
        $httpClient = $this->mockObject('GuzzleHttp\Client');
        $sendRequestMethod = $httpClient->mockMethod('send')->set(function (\GuzzleHttp\Message\RequestInterface $request) {
            $this->assertSame('http://example.com:8080/stop', $request->getUrl());
            $this->assertSame('POST', $request->getMethod());
            $this->assertSame('clientId=foo', $request->getBody()->getContents());

            $response = $this->mockClass('\GuzzleHttp\Message\Response')->newInstanceWithoutConstructor();
            $response->mockMethod('getBody')->set(null);
            return $response;
        });
        /** @var GuzzleHttp\Client $httpClient */

        $wowzaServer = $this->mockClass('CM_Wowza_Server')->newInstanceWithoutConstructor();
        $wowzaServer->mockMethod('getPrivateIp')->set('example.com');
        $wowzaServer->mockMethod('getHttpPort')->set(8080);
        /** @var CM_Wowza_Server $wowzaServer */

        $api = new CM_Wowza_HttpApiClient($httpClient);
        $api->stopClient($wowzaServer, 'foo');
        $this->assertSame(1, $sendRequestMethod->getCallCount());
    }

    public function testFetchStatus() {
        $httpClient = $this->mockObject('GuzzleHttp\Client');
        $sendRequestMethod = $httpClient->mockMethod('send')->set(function (\GuzzleHttp\Message\RequestInterface $request) {
            $this->assertSame('http://example.com:8080/status', $request->getUrl());
            $this->assertSame('GET', $request->getMethod());

            $response = $this->mockClass('\GuzzleHttp\Message\Response')->newInstanceWithoutConstructor();
            $response->mockMethod('getBody')->set('{"foo":"bar"}');
            return $response;
        });
        /** @var GuzzleHttp\Client $httpClient */

        $wowzaServer = $this->mockClass('CM_Wowza_Server')->newInstanceWithoutConstructor();
        $wowzaServer->mockMethod('getPrivateIp')->set('example.com');
        $wowzaServer->mockMethod('getHttpPort')->set(8080);
        /** @var CM_Wowza_Server $wowzaServer */

        $api = new CM_Wowza_HttpApiClient($httpClient);
        $result = $api->fetchStatus($wowzaServer);
        $this->assertSame(['foo' => 'bar'], $result);
        $this->assertSame(1, $sendRequestMethod->getCallCount());
    }
}
