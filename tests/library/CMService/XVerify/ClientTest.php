<?php

class CMService_XVerify_ClientTest extends CMTest_TestCase {

    public function testMalformedEmailAddress() {
        $mockBuilder = $this->getMockBuilder('CMService_XVerify_Client');
        $mockBuilder->setMethods(['_getResponseBody']);
        $mockBuilder->setConstructorArgs(['', '']);
        $xVerifyMock = $mockBuilder->getMock();
        $xVerifyMock->expects($this->never())->method('_getResponseBody');
        /** @var CMService_XVerify_Client $xVerifyMock */
        $this->assertFalse($xVerifyMock->isValid('invalid email@google.com'));
    }

    public function testEmptyResponse() {
        $responseBodyMock = '';
        $exceptionExpected = new CM_Exception('Invalid XVerify email validation response', null, [
            'email'   => 'testEmptyResponse@google.com',
            'code'    => '500',
            'headers' => array('Content-Length' => array(0), 'Content-Type' => array('application/json')),
            'body'    => '',
        ]);
        $headerList = array('Content-Length' => 0, 'Content-Type' => 'application/json');
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock, 500, $headerList, $exceptionExpected);
        $this->assertTrue($xVerifyMock->isValid('testEmptyResponse@google.com'));
    }

    public function testInvalidResponse() {
        $responseBodyMock = '{"address":{"status":"invalid","responsecode":2}}';
        $exceptionExpected = new CM_Exception('Invalid XVerify email validation response', null, [
            'email'   => 'testInvalidResponse@google.com',
            'code'    => '200',
            'headers' => array(),
            'body'    => '{"address":{"status":"invalid","responsecode":2}}',
        ]);
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock, 200, array(), $exceptionExpected);
        $this->assertTrue($xVerifyMock->isValid('testInvalidResponse@google.com'));
    }

    public function testInvalidResponseCode() {
        $responseBodyMock = '{"email":{"status":"bad_request","responsecode":503}}';
        $exceptionExpected = new CM_Exception('Invalid XVerify email validation response', null, [
            'email'   => 'testInvalidResponseCode@google.com',
            'code'    => '200',
            'headers' => array(),
            'body'    => '{"email":{"status":"bad_request","responsecode":503}}',
        ]);
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock, 200, array(), $exceptionExpected);
        $this->assertTrue($xVerifyMock->isValid('testInvalidResponseCode@google.com'));
    }

    public function testMissingResponseCode() {
        $responseBodyMock = '{"email":{"status":"invalid"}}';
        $exceptionExpected = new CM_Exception('Invalid XVerify email validation response', null, [
            'email'   => 'testMissingResponseCode@google.com',
            'code'    => '200',
            'headers' => array(),
            'body'    => '{"email":{"status":"invalid"}}',
        ]);
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock, 200, array(), $exceptionExpected);
        $this->assertTrue($xVerifyMock->isValid('testMissingResponseCode@google.com'));
    }

    public function testValid() {
        $responseBodyMock = '{"email":{"status":"valid","responsecode":1}}';
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock);
        $this->assertTrue($xVerifyMock->isValid('testValid@google.com'));
    }

    public function testInvalid() {
        $responseBodyMock = '{"email":{"status":"invalid","responsecode":2}}';
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock);
        $this->assertFalse($xVerifyMock->isValid('testInvalid@google.com'));
    }

    public function testUnknown() {
        $responseBodyMock = '{"email":{"status":"unknown","responsecode":3}}';
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock);
        $this->assertTrue($xVerifyMock->isValid('testUnknown@google.com'));
    }

    public function testBadData() {
        $responseBodyMock = '{"email":{"status":"bad_data","responsecode":400}}';
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock);
        $this->assertFalse($xVerifyMock->isValid('testBadData@google.com'));
    }

    public function testHandleException() {
        $mockBuilder = $this->getMockBuilder('CMService_XVerify_Client');
        $mockBuilder->setMethods(['_getResponse', '_handleException']);
        $mockBuilder->setConstructorArgs(['', '']);
        $xVerifyMock = $mockBuilder->getMock();
        $exception = new Exception('No credits');
        $xVerifyMock->expects($this->once())->method('_getResponse')->will($this->throwException($exception));
        $xVerifyMock->expects($this->once())->method('_handleException')->with($exception);
        /** @var CMService_XVerify_Client $xVerifyMock */
        $xVerifyMock->isValid('testHandleException@google.com');
    }

    /**
     * @param string    $responseBody
     * @param int       $statusCode
     * @param array     $headerList
     * @param Exception $exceptionExpected
     * @return CMService_XVerify_Client
     */
    protected function _getXVerifyMock($responseBody, $statusCode = null, $headerList = null, Exception $exceptionExpected = null) {
        if (null === $statusCode) {
            $statusCode = 200;
        }
        if (null === $headerList) {
            $headerList = array();
        }
        $mockBuilder = $this->getMockBuilder('CMService_XVerify_Client');
        $mockBuilder->setMethods(['_getResponse', '_handleException']);
        $mockBuilder->setConstructorArgs(['', '']);
        /** @var PHPUnit_Framework_MockObject_MockObject|CMService_XVerify_Client $xVerifyMock */
        $xVerifyMock = $mockBuilder->getMock();
        $responseMock = new \GuzzleHttp\Psr7\Response($statusCode, $headerList, \GuzzleHttp\Stream\Stream::factory($responseBody));
        $xVerifyMock->expects($this->once())->method('_getResponse')->will($this->returnValue($responseMock));
        if ($exceptionExpected) {
            $xVerifyMock->expects($this->once())->method('_handleException')->with($exceptionExpected);
        } else {
            $xVerifyMock->expects($this->never())->method('_handleException');
        }
        return $xVerifyMock;
    }
}
