<?php

class CM_Http_Request_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetViewer() {
        $user = CMTest_TH::createUser();
        $uri = '/';
        $headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive');
        $mock = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($uri, $headers));
        /** @var CM_Http_Request_Abstract $mock */
        $this->assertNull($mock->getViewer());

        $headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive', 'Cookie' => 'sessionId=a1d2726e5b3801226aafd12fd62496c8');
        $mock = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($uri, $headers));
        try {
            $mock->getViewer(true);
            $this->fail();
        } catch (CM_Exception_AuthRequired $ex) {
            $this->assertTrue(true);
        }

        $session = new CM_Session();
        $session->setUser($user);
        $headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive', 'Cookie' => 'sessionId=' . $session->getId());
        unset($session);

        $mock = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($uri, $headers));
        $this->assertEquals($user, $mock->getViewer(true));
    }

    public function testGetViewerDoesntCreateSession() {
        $request = new CM_Http_Request_Get('/');
        $this->assertFalse($request->hasSession());

        $request->getViewer();
        $this->assertFalse($request->hasSession());
    }

    public function testGetCookie() {
        $uri = '/';
        $headers = array('Host'   => 'example.ch', 'Connection' => 'keep-alive',
                         'Cookie' => ';213q;213;=foo=hello;bar=tender;  adkhfa ; asdkf===fsdaf');
        $mock = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($uri, $headers));
        $this->assertEquals('hello', $mock->getCookie('foo'));
        $this->assertEquals('tender', $mock->getCookie('bar'));
        $this->assertNull($mock->getCookie('asdkf'));
    }

    public function testGetIp() {
        $request = CM_Http_Request_Abstract::factory('get', '/foo', null, []);
        $this->assertNull($request->getIp());
        $this->assertNull($request->getIp(true));

        $request = CM_Http_Request_Abstract::factory('get', '/foo', null, ['remote_addr' => '0']);
        $this->assertNull($request->getIp());
        $this->assertNull($request->getIp(true));

        $request = CM_Http_Request_Abstract::factory('get', '/foo', null, ['remote_addr' => '500.500.500.500']);
        $this->assertNull($request->getIp());
        $this->assertNull($request->getIp(true));

        $request = CM_Http_Request_Abstract::factory('get', '/foo', null, ['remote_addr' => '42.42.42.42']);
        $this->assertSame('707406378', $request->getIp());
        $this->assertSame('42.42.42.42', $request->getIp(true));
    }

    public function testGetLanguageLoggedUser() {
        $user = CMTest_TH::createUser();
        $request = $this->_prepareRequest('/', null, $user);
        // No language at all
        $this->assertSame($request->getLanguage(), null);

        // Getting default language, user has no language
        $defaultLanguage = CMTest_TH::createLanguage();
        $this->assertEquals($request->getLanguage(), $defaultLanguage);

        // Getting user language
        $anotherUserLanguage = CMTest_TH::createLanguage();
        $user->setLanguage($anotherUserLanguage);
        $this->assertEquals($request->getLanguage(), $anotherUserLanguage);
    }

    public function testGetLanguageGuest() {
        $request = $this->_prepareRequest('/');
        // No language at all
        $this->assertSame($request->getLanguage(), null);

        // Getting default language (guest has no language)
        $defaultLanguage = CMTest_TH::createLanguage();
        $this->assertEquals($request->getLanguage(), $defaultLanguage);
    }

    public function testGetLanguageByBrowser() {
        $defaultLanguage = CMTest_TH::createLanguage('en');
        $browserLanguage = CMTest_TH::createLanguage('de');
        $this->assertEquals(CM_Model_Language::findDefault(), $defaultLanguage);
        $request = $this->_prepareRequest('/', array('Accept-Language' => 'de'));
        $this->assertEquals($request->getLanguage(), $browserLanguage);
        $request = $this->_prepareRequest('/', array('Accept-Language' => 'pl'));
        $this->assertEquals($request->getLanguage(), $defaultLanguage);
    }

    public function testFactory() {
        $this->assertInstanceOf('CM_Http_Request_Get', CM_Http_Request_Abstract::factory('GET', '/test'));
        $this->assertInstanceOf('CM_Http_Request_Post', CM_Http_Request_Abstract::factory('POST', '/test'));
    }

    public function testSetUri() {
        $language = CM_Model_Language::create('english', 'en', true);
        $uri = '/en/foo/bar?foo1=bar1';
        $headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive');
        /** @var CM_Http_Request_Abstract $mock */
        $mock = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($uri, $headers));
        $this->assertEquals($language, $mock->popPathLanguage());
        $this->assertSame('/foo/bar', $mock->getPath());
        $this->assertSame(array('foo', 'bar'), $mock->getPathParts());
        $this->assertSame(array('foo1' => 'bar1'), $mock->getQuery());
        $this->assertEquals($language, $mock->getLanguageUrl());
        $this->assertSame($uri, $mock->getUri());

        $mock->setUri('/foo1/bar1?foo=bar');
        $this->assertSame('/foo1/bar1', $mock->getPath());
        $this->assertSame(array('foo1', 'bar1'), $mock->getPathParts());
        $this->assertSame(array('foo' => 'bar'), $mock->getQuery());
        $this->assertNull($mock->getLanguageUrl());
        $this->assertSame('/foo1/bar1?foo=bar', $mock->getUri());
    }

    public function testFindQuery() {
        $uri = '/foo/bar?foo1=bar1';
        $headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive');
        /** @var CM_Http_Request_Abstract $mock */
        $requestMockClass = $this->mockClass('CM_Http_Request_Abstract');
        /** @var \Mocka\AbstractClassTrait|CM_Http_Request_Abstract $requestMock */
        $requestMock = $requestMockClass->newInstance([$uri, $headers]);

        $this->assertSame(['foo1' => 'bar1'], $requestMock->findQuery());
        $requestMock->mockMethod('getQuery')->set(function () {
            throw new CM_Exception_Invalid('error');
        });
        $this->assertSame([], $requestMock->findQuery());
    }

    public function testSetUriNonUtf() {
        $uri = '/foo/bar?%%aff%%=quux&bar=%%AFF%%&baz[]=%%aff%%&baz[]=%%aff%%';
        $headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive');
        /** @var CM_Http_Request_Abstract $mock */
        $mock = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($uri, $headers));

        $this->assertSame('/foo/bar', $mock->getPath());
        $this->assertSame(array('foo', 'bar'), $mock->getPathParts());
        $this->assertSame(
            array(
                '%?f%%' => 'quux',
                'bar'   => '%?F%%',
                'baz'   => [
                    '%?f%%',
                    '%?f%%',
                ]
            ),
            $mock->getQuery()
        );
        $this->assertSame($uri, $mock->getUri());
    }

    public function testSetUriRelativeAndColon() {
        $uri = '/foo/bar?foo1=bar1:80';
        /** @var CM_Http_Request_Abstract $mock */
        $mock = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($uri));
        $this->assertSame('/foo/bar', $mock->getPath());
        $this->assertSame(array('foo1' => 'bar1:80'), $mock->getQuery());
    }

    public function testGetPath() {
        /** @var CM_Http_Request_Abstract|\Mocka\AbstractClassTrait $mock */
        $mock = $this->mockClass('CM_Http_Request_Abstract')->newInstanceWithoutConstructor();
        $mock->setPath('//');
        $this->assertSame('/', $mock->getPath());
        $mock->setPath('///');
        $this->assertSame('/', $mock->getPath());
    }

    public function testGetUri() {
        $request = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array('/foo/bar?hello=world'));
        /** @var CM_Http_Request_Abstract $request */

        $this->assertSame('/foo/bar?hello=world', $request->getUri());

        $this->assertSame('foo', $request->popPathPart());
        $this->assertSame('/foo/bar?hello=world', $request->getUri());

        $request->setPath('/hello');
        $this->assertSame('/foo/bar?hello=world', $request->getUri());

        $request->setUri('/world');
        $this->assertSame('/world', $request->getUri());
    }

    public function testGetClientId() {
        $uri = '/en/foo/bar?foo1=bar1';
        $headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive');
        /** @var CM_Http_Request_Abstract $mock */
        $mock = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($uri, $headers));
        $this->assertFalse($mock->hasClientId());
        $clientId = $mock->getClientId();
        $this->assertInternalType('int', $clientId);
        $this->assertTrue($mock->hasClientId());

        $uri = '/';
        $headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive', 'Cookie' => ';213q;213;=clientId=WRONG;');
        /** @var CM_Http_Request_Abstract $mock */
        $mock = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($uri, $headers));
        $this->assertFalse($mock->hasClientId());
        $this->assertSame($clientId + 1, $mock->getClientId());
        $this->assertTrue($mock->hasClientId());
        $id = $mock->getClientId();
        $this->assertSame($id, $mock->getClientId());

        $uri = '/';
        $headers = array('Host' => 'example.ch', 'Connection' => 'keep-alive', 'Cookie' => ';213q;213;=clientId=' . $id . ';');
        /** @var CM_Http_Request_Abstract $mock */
        $mock = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($uri, $headers));
        $this->assertTrue($mock->hasClientId());
        $this->assertSame($id, $mock->getClientId());
        $this->assertTrue($mock->hasClientId());
    }

    public function testGetClientIdSetCookie() {
        $request = new CM_Http_Request_Post('/foo/null/');
        $clientId = $request->getClientId();
        $site = $this->getMockSite();
        /** @var CM_Http_Response_Abstract $responseMock */
        $mockBuilder = $this->getMockBuilder('CM_Http_Response_Abstract');
        $mockBuilder->setMethods(['_process', 'setCookie']);
        $mockBuilder->setConstructorArgs([$request, $site, $this->getServiceManager()]);
        $responseMock = $mockBuilder->getMock();
        $responseMock->expects($this->once())->method('setCookie')->with('clientId', (string) $clientId);
        $responseMock->process();
    }

    public function testGetUserAgent() {
        $request = new CM_Http_Request_Get('/foo');
        $this->assertSame('', $request->getUserAgent());
        $request = new CM_Http_Request_Get('/foo', ['user-agent' => 'Mozilla/5.0']);
        $this->assertSame('Mozilla/5.0', $request->getUserAgent());
    }

    public function testIsBotCrawler() {
        $useragents = array(
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'   => true,
            'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)' => false,
            'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)'    => true,
        );
        foreach ($useragents as $useragent => $expected) {
            $request = new CM_Http_Request_Get('/foo', array('user-agent' => $useragent));
            $this->assertSame($expected, $request->isBotCrawler());
        }
    }

    public function testIsBotCrawlerWithoutUseragent() {
        $request = new CM_Http_Request_Get('/foo');
        $this->assertFalse($request->isBotCrawler());
    }

    public function testGetHost() {
        $request = new CM_Http_Request_Get('/', array('host' => 'www.example.com'));
        $this->assertSame('www.example.com', $request->getHost());
    }

    public function testGetHostWithPort() {
        $request = new CM_Http_Request_Get('/', array('host' => 'www.example.com:80'));
        $this->assertSame('www.example.com', $request->getHost());
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testGetHostWithoutHeader() {
        $request = new CM_Http_Request_Get('/');
        $request->getHost();
    }

    public function testIsSupported() {
        $userAgentList = [
            'MSIE 6.0'                                            => false,
            'MSIE 9.0'                                            => false,
            'MSIE 9.1'                                            => false,
            'MSIE 10.0'                                           => false,
            'Edge'                                                => true,
            'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0)'  => true,
            'Mozilla/5.0 (Linux; U; Android 2.3.3; zh-tw; HTC)'   => false,
            'Mozilla/5.0 (Linux; Android 4.0.4; Galaxy Nexus)'    => true,
            'Opera/9.80 (Android; Opera Mini/7.6.35766/35.5706;)' => false,
        ];
        foreach ($userAgentList as $userAgent => $isSupported) {
            $request = new CM_Http_Request_Get('/', ['user-agent' => $userAgent]);
            $this->assertSame($isSupported, $request->isSupported(), 'Mismatch for UA: `' . $userAgent . '`.');
        }
    }

    public function testIsSupportedWithoutUserAgent() {
        $request = new CM_Http_Request_Get('/', []);
        $this->assertSame(true, $request->isSupported());
    }

    public function testSetSession() {
        $user = CMTest_TH::createUser();
        $session = new CM_Session();
        $request = new CM_Http_Request_Get('/');

        $session->setUser($user);
        $request->setSession($session);
        $this->assertEquals($session, $request->getSession());
        $this->assertEquals($user, $request->getViewer());

        $session->deleteUser();
        $request->setSession($session);
        $this->assertEquals($session, $request->getSession());
        $this->assertSame(null, $request->getViewer());
    }

    public function testSetSessionFromCookie() {
        $requestFoo = new CM_Http_Request_Get('/foo');
        $sessionFoo = new CM_Session(null, $requestFoo);
        $sessionFoo->set('foo', 'bar');
        $sessionFoo->write();
        $sessionFooId = $sessionFoo->getId();

        $requestBar = new CM_Http_Request_Get('/bar', ['cookie' => 'sessionId=' . $sessionFooId . ';']);
        $sessionBar = $requestBar->getSession();

        $this->assertEquals($sessionFooId, $sessionBar->getId());
        $this->assertEquals('bar', $sessionBar->get('foo'));
        $this->assertEquals($requestBar, $sessionBar->getRequest());
    }

    public function testGetTimeZoneFromCookie() {
        $request = new CM_Http_Request_Get('/foo/bar/', ['cookie' => 'timezoneOffset=9000; clientId=7']);
        $timeZone = $request->getTimeZone();
        $this->assertInstanceOf('DateTimeZone', $timeZone);
        $this->assertSame('-02:30', $timeZone->getName());

        $request = new CM_Http_Request_Get('/foo/bar/', ['cookie' => 'timezoneOffset=-9000; clientId=7']);
        $timeZone = $request->getTimeZone();
        $this->assertInstanceOf('DateTimeZone', $timeZone);
        $this->assertSame('+02:30', $timeZone->getName());

        $request = new CM_Http_Request_Post('/foo/bar/', ['cookie' => 'timezoneOffset=3600']);
        $timeZone = $request->getTimeZone();
        $this->assertInstanceOf('DateTimeZone', $timeZone);
        $this->assertSame('-01:00', $timeZone->getName());

        $request = new CM_Http_Request_Post('/foo/bar/', ['cookie' => 'timezoneOffset=50400']);
        $timeZone = $request->getTimeZone();
        $this->assertNull($timeZone);

        $request = new CM_Http_Request_Post('/foo/bar/', ['cookie' => 'timezoneOffset=-62400']);
        $timeZone = $request->getTimeZone();
        $this->assertNull($timeZone);

        $request = new CM_Http_Request_Get('/foo/baz/');
        $timeZone = $request->getTimeZone();
        $this->assertNull($timeZone);
    }

    public function testSanitize() {
        $malformedString = pack("H*", 'c32e');
        $malformedUri = 'http://foo.bar/' . $malformedString;
        $this->assertFalse(mb_check_encoding($malformedUri, 'UTF-8'));
        $exception = $this->catchException(function () use ($malformedUri) {
            CM_Util::jsonEncode($malformedUri);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Cannot json_encode value.', $exception->getMessage());

        $request = new CM_Http_Request_Get($malformedUri, null, ['baz' => pack("H*", 'c32e')]);
        $this->assertInstanceOf('CM_Http_Request_Get', $request);
        $this->assertTrue(mb_check_encoding($request->getUri(), 'UTF-8'));
        $this->assertTrue(mb_check_encoding($request->getServer()['baz'], 'UTF-8'));
        $this->assertNotEmpty(CM_Util::jsonEncode($request->getUri()));
    }

    public function testPopPathPart() {
        $request = new CM_Http_Request_Get('/part0/part1/part2');
        $this->assertSame('part1', $request->popPathPart(1));
        $this->assertSame('part0', $request->popPathPart(0));
        $this->assertSame('/part2', $request->getPath());
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot pop
     */
    public function testPopPathPartNoMatch() {
        $request = new CM_Http_Request_Get('/part0/part1/part2');
        $request->popPathPart(5);
    }

    public function testPopPathPrefix() {
        $request = new CM_Http_Request_Get('/part0/part1/part2');
        $request->popPathPrefix('/part0/part1/');
        $this->assertSame('/part2', $request->getPath());
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot pop
     */
    public function testPopPathPrefixNoMatch() {
        $request = new CM_Http_Request_Get('/part0/part1/part2');
        $request->popPathPrefix('/foo');
    }

    public function testClearInstance() {
        $this->assertFalse(CM_Http_Request_Abstract::hasInstance());

        $request = new CM_Http_Request_Get('/');

        $this->assertTrue(CM_Http_Request_Abstract::hasInstance());
        $this->assertSame($request, CM_Http_Request_Abstract::getInstance());

        CM_Http_Request_Abstract::clearInstance();

        $this->assertFalse(CM_Http_Request_Abstract::hasInstance());
    }

    /**
     * @param string             $uri
     * @param array|null         $additionalHeaders
     * @param CM_Model_User|null $user
     * @return CM_Http_Request_Abstract
     */
    private function _prepareRequest($uri, array $additionalHeaders = null, CM_Model_User $user = null) {
        $headers = array('Host' => 'example.com', 'Connection' => 'keep-alive');
        if ($additionalHeaders) {
            $headers = array_merge($headers, $additionalHeaders);
        }
        /** @var CM_Http_Request_Abstract $request */
        $request = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($uri, $headers), '', true, true, true, array('getViewer'));
        $request->expects($this->any())->method('getViewer')->will($this->returnValue($user));
        $this->assertSame($request->getViewer(), $user);
        return $request;
    }
}
