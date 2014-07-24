<?php

class CMService_KickBox_ClientTest extends CMTest_TestCase {

    public function testMalformedEmailAddress() {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponse'), array('', true, true, 0.2));
        $kickBoxMock->expects($this->never())->method('_getResponse');
        /** @var CMService_KickBox_Client $kickBoxMock */
        $this->assertFalse($kickBoxMock->isValid('invalid email@example.com'));
    }

    public function testNoCredits() {
        $responseMock = null;
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseMock);
        $this->assertTrue($kickBoxMock->isValid('test@example.com'));
    }

    public function testInvalid() {
        $responseMock = array('result' => 'invalid', 'disposable' => 'false', 'accept_all' => 'false', 'sendex' => 0.2);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseMock);
        $this->assertFalse($kickBoxMock->isValid('test@example.com'));
        $kickBoxMock = $this->_getKickBoxMock(false, true, 0.2, $responseMock);
        $this->assertTrue($kickBoxMock->isValid('test@example.com'));
    }

    public function testDisposable() {
        $responseMock = array('result' => 'valid', 'disposable' => 'true', 'accept_all' => 'false', 'sendex' => 0.2);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseMock);
        $this->assertFalse($kickBoxMock->isValid('test@example.com'));
        $kickBoxMock = $this->_getKickBoxMock(true, false, 0.2, $responseMock);
        $this->assertTrue($kickBoxMock->isValid('test@example.com'));
    }

    public function testAcceptAll() {
        $responseMock = array('result' => 'valid', 'disposable' => 'false', 'accept_all' => 'true', 'sendex' => 0.19);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseMock);
        $this->assertFalse($kickBoxMock->isValid('test@example.com'));
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.19, $responseMock);
        $this->assertTrue($kickBoxMock->isValid('test@example.com'));
    }

    public function testUnknown() {
        $responseMock = array('result' => 'unknown', 'disposable' => 'false', 'accept_all' => 'false', 'sendex' => 0.19);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseMock);
        $this->assertFalse($kickBoxMock->isValid('test@example.com'));
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.19, $responseMock);
        $this->assertTrue($kickBoxMock->isValid('test@example.com'));
    }

    public function testValid() {
        $responseMock = array('result' => 'valid', 'disposable' => 'false', 'accept_all' => 'false', 'sendex' => 0.19);
        $kickBoxMock = $this->_getKickBoxMock(true, true, 0.2, $responseMock);
        $this->assertTrue($kickBoxMock->isValid('test@example.com'));
    }

    /**
     * @param bool       $disallowInvalid
     * @param bool       $disallowDisposable
     * @param float      $disallowUnknownThreshold
     * @param array|null $responseMock
     * @return CMService_KickBox_Client
     */
    protected function _getKickBoxMock($disallowInvalid, $disallowDisposable, $disallowUnknownThreshold, $responseMock) {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponse'), array(
            '',
            $disallowInvalid,
            $disallowDisposable,
            $disallowUnknownThreshold
        ));
        $kickBoxMock->expects($this->once())->method('_getResponse')->will($this->returnValue($responseMock));
        return $kickBoxMock;
    }
}
