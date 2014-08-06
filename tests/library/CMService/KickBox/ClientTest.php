<?php

class CMService_KickBox_ClientTest extends CMTest_TestCase {

    public function testMalformedEmailAddress() {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponseBody'), array('', true, true, 0.2));
        $kickBoxMock->expects($this->never())->method('_getResponseBody');
        /** @var CMService_KickBox_Client $kickBoxMock */
        $this->assertFalse($kickBoxMock->isValid('invalid email@example.com'));
    }

    public function testNoCredits() {
        $responseBodyMock = null;
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseBodyMock);
        $this->assertTrue($kickBoxMock->isValid('testNoCredits@example.com'));
    }

    public function testInvalid() {
        $responseBodyMock = array('result' => 'invalid', 'disposable' => 'false', 'accept_all' => 'false', 'sendex' => 0.2);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseBodyMock);
        $this->assertFalse($kickBoxMock->isValid('testInvalid@example.com'));
        $kickBoxMock = $this->_getKickBoxMock(false, true, 0.2, $responseBodyMock);
        $this->assertTrue($kickBoxMock->isValid('testInvalid@example.com'));
    }

    public function testDisposable() {
        $responseBodyMock = array('result' => 'valid', 'disposable' => 'true', 'accept_all' => 'false', 'sendex' => 0.2);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseBodyMock);
        $this->assertFalse($kickBoxMock->isValid('testDisposable@example.com'));
        $kickBoxMock = $this->_getKickBoxMock(true, false, 0.2, $responseBodyMock);
        $this->assertTrue($kickBoxMock->isValid('testDisposable@example.com'));
    }

    public function testAcceptAll() {
        $responseBodyMock = array('result' => 'valid', 'disposable' => 'false', 'accept_all' => 'true', 'sendex' => 0.19);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseBodyMock);
        $this->assertFalse($kickBoxMock->isValid('testAcceptAll@example.com'));
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.19, $responseBodyMock);
        $this->assertTrue($kickBoxMock->isValid('testAcceptAll@example.com'));
    }

    public function testUnknown() {
        $responseBodyMock = array('result' => 'unknown', 'disposable' => 'false', 'accept_all' => 'false', 'sendex' => 0.19);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseBodyMock);
        $this->assertFalse($kickBoxMock->isValid('testUnknown@example.com'));
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.19, $responseBodyMock);
        $this->assertTrue($kickBoxMock->isValid('testUnknown@example.com'));
    }

    public function testValid() {
        $responseBodyMock = array('result' => 'valid', 'disposable' => 'false', 'accept_all' => 'false', 'sendex' => 0.19);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseBodyMock);
        $this->assertTrue($kickBoxMock->isValid('testValid@example.com'));
    }

    public function testHandleExceptionNoCredits() {
        $responseMock = new \Kickbox\HttpClient\Response(array('result' => 'unknown'), 403, array('header' => 'value'));
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponse', '_handleException'), array('', true, false, 0));
        $kickBoxMock->expects($this->once())->method('_getResponse')->will($this->returnValue($responseMock));
        $exception = new CM_Exception('KickBox exception', array(
            'email'   => 'test@example.com',
            'code'    => 403,
            'headers' => array('header' => 'value'),
            'body'    => array('result' => 'unknown'),
        ));
        $exception->setSeverity(CM_Exception::WARN);
        $kickBoxMock->expects($this->once())->method('_handleException')->with($exception);
        /** @var CMService_KickBox_Client $kickBoxMock */
        $kickBoxMock->isValid('test@example.com');
    }

    /**
     * @param bool       $disallowInvalid
     * @param bool       $disallowDisposable
     * @param float      $disallowUnknownThreshold
     * @param array|null $responseBodyMock
     * @return CMService_KickBox_Client
     */
    protected function _getKickBoxMock($disallowInvalid, $disallowDisposable, $disallowUnknownThreshold, $responseBodyMock) {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponseBody'), array(
            '',
            $disallowInvalid,
            $disallowDisposable,
            $disallowUnknownThreshold
        ));
        $kickBoxMock->expects($this->once())->method('_getResponseBody')->will($this->returnValue($responseBodyMock));
        return $kickBoxMock;
    }
}
