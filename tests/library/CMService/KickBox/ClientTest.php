<?php

class CMService_KickBox_ClientTest extends CMTest_TestCase {

    public function testMalformedEmailAddress() {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponse'), array(''));
        $kickBoxMock->expects($this->never())->method('_getResponse');
        /** @var CMService_KickBox_Client $kickBoxMock */
        $this->assertFalse($kickBoxMock->isValid('invalid email@example.com'));
    }

    public function testInvalid() {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponse'), array(''));
        $responseMock = array('result' => 'invalid');
        $kickBoxMock->expects($this->once())->method('_getResponse')->will($this->returnValue($responseMock));
        /** @var CMService_KickBox_Client $kickBoxMock */
        $this->assertFalse($kickBoxMock->isValid('test@example.com'));
    }

    public function testDisposable() {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponse'), array(''));
        $responseMock = array('result' => 'valid', 'disposable' => 'true');
        $kickBoxMock->expects($this->once())->method('_getResponse')->will($this->returnValue($responseMock));
        /** @var CMService_KickBox_Client $kickBoxMock */
        $this->assertFalse($kickBoxMock->isValid('test@example.com'));
    }

    public function testAcceptAllPoorSendex() {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponse'), array(''));
        $responseMock = array('result' => 'valid', 'disposable' => 'false', 'accept_all' => 'true', 'sendex' => 0.19);
        $kickBoxMock->expects($this->once())->method('_getResponse')->will($this->returnValue($responseMock));
        /** @var CMService_KickBox_Client $kickBoxMock */
        $this->assertFalse($kickBoxMock->isValid('test@example.com'));
    }

    public function testAcceptAllGoodSendex() {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponse'), array(''));
        $responseMock = array('result' => 'valid', 'disposable' => 'false', 'accept_all' => 'true', 'sendex' => 0.2);
        $kickBoxMock->expects($this->once())->method('_getResponse')->will($this->returnValue($responseMock));
        /** @var CMService_KickBox_Client $kickBoxMock */
        $this->assertTrue($kickBoxMock->isValid('test@example.com'));
    }

    public function testUnknown() {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponse'), array(''));
        $responseMock = array('result' => 'unknown', 'disposable' => 'false', 'accept_all' => 'false');
        $kickBoxMock->expects($this->once())->method('_getResponse')->will($this->returnValue($responseMock));
        /** @var CMService_KickBox_Client $kickBoxMock */
        $this->assertTrue($kickBoxMock->isValid('test@example.com'));
    }

    public function testValid() {
        $kickBoxMock = $this->getMock('CMService_KickBox_Client', array('_getResponse'), array(''));
        $responseMock = array('result' => 'valid', 'disposable' => 'false', 'accept_all' => 'false');
        $kickBoxMock->expects($this->once())->method('_getResponse')->will($this->returnValue($responseMock));
        /** @var CMService_KickBox_Client $kickBoxMock */
        $this->assertTrue($kickBoxMock->isValid('test@example.com'));
    }
}
