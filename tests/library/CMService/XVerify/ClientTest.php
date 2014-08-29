<?php

class CMService_XVerify_ClientTest extends CMTest_TestCase {

    public function testMalformedEmailAddress() {
        $xVerifyMock = $this->getMock('CMService_XVerify_Client', array('_getResponseBody'), array('', ''));
        $xVerifyMock->expects($this->never())->method('_getResponseBody');
        /** @var CMService_XVerify_Client $xVerifyMock */
        $this->assertFalse($xVerifyMock->isValid('invalid email@example.com'));
    }

    public function testEmptyResponse() {
        $responseBodyMock = '';
        $exceptionExpected = new CM_Exception('Invalid XVerify email validation response', array(
            'email'   => 'testEmptyResponse@example.com',
            'code'    => '500',
            'headers' => array('Content-Length' => array(0), 'Content-Type' => array('application/json')),
            'body'    => '',
        ));
        $headerList = array('Content-Length' => 0, 'Content-Type' => 'application/json');
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock, 500, $headerList, $exceptionExpected);
        $this->assertTrue($xVerifyMock->isValid('testEmptyResponse@example.com'));
    }

    public function testInvalidResponse() {
        $responseBodyMock = '{"address":{"status":"invalid","responsecode":2}}';
        $exceptionExpected = new CM_Exception('Invalid XVerify email validation response', array(
            'email'   => 'testInvalidResponse@example.com',
            'code'    => '200',
            'headers' => array(),
            'body'    => '{"address":{"status":"invalid","responsecode":2}}',
        ));
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock, 200, array(), $exceptionExpected);
        $this->assertTrue($xVerifyMock->isValid('testInvalidResponse@example.com'));
    }

    public function testInvalidResponseCode() {
        $responseBodyMock = '{"email":{"status":"bad_data","responsecode":400}}';
        $exceptionExpected = new CM_Exception('Invalid XVerify email validation response', array(
            'email'   => 'testInvalidResponseCode@example.com',
            'code'    => '200',
            'headers' => array(),
            'body'    => '{"email":{"status":"bad_data","responsecode":400}}',
        ));
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock, 200, array(), $exceptionExpected);
        $this->assertTrue($xVerifyMock->isValid('testInvalidResponseCode@example.com'));
    }

    public function testMissingResponseCode() {
        $responseBodyMock = '{"email":{"status":"invalid"}}';
        $exceptionExpected = new CM_Exception('Invalid XVerify email validation response', array(
            'email'   => 'testMissingResponseCode@example.com',
            'code'    => '200',
            'headers' => array(),
            'body'    => '{"email":{"status":"invalid"}}',
        ));
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock, 200, array(), $exceptionExpected);
        $this->assertTrue($xVerifyMock->isValid('testMissingResponseCode@example.com'));
    }

    public function testValid() {
        $responseBodyMock = '{"email":{"status":"valid","responsecode":1}}';
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock);
        $this->assertTrue($xVerifyMock->isValid('testValid@example.com'));
    }

    public function testInvalid() {
        $responseBodyMock = '{"email":{"status":"invalid","responsecode":2}}';
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock);
        $this->assertFalse($xVerifyMock->isValid('testInvalid@example.com'));
    }

    public function testUnknown() {
        $responseBodyMock = '{"email":{"status":"unknown","responsecode":3}}';
        $xVerifyMock = $this->_getXVerifyMock($responseBodyMock);
        $this->assertTrue($xVerifyMock->isValid('testUnknown@example.com'));
    }

    public function testHandleException() {
        $xVerifyMock = $this->getMock('CMService_XVerify_Client', array('_getResponse', '_logException'), array('', ''));
        $exception = new Exception('No credits');
        $xVerifyMock->expects($this->once())->method('_getResponse')->will($this->throwException($exception));
        $xVerifyMock->expects($this->once())->method('_logException')->with($exception);
        /** @var CMService_XVerify_Client $xVerifyMock */
        $xVerifyMock->isValid('testHandleException@example.com');
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
        $xVerifyMock = $this->getMock('CMService_XVerify_Client', array('_getResponse', '_logException'), array('', ''));
        $responseMock = new \GuzzleHttp\Message\Response($statusCode, $headerList, \GuzzleHttp\Stream\Stream::factory($responseBody));
        $xVerifyMock->expects($this->once())->method('_getResponse')->will($this->returnValue($responseMock));
        if ($exceptionExpected) {
            $xVerifyMock->expects($this->once())->method('_logException')->with($exceptionExpected);
        } else {
            $xVerifyMock->expects($this->never())->method('_logException');
        }
        return $xVerifyMock;
    }
}
