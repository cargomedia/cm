<?php

class CM_Http_Response_FactoryTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetResponse() {
        $responses = array();
        $responses['/captcha'] = 'CM_Http_Response_Captcha';
        $responses['/emailtracking'] = 'CM_Http_Response_EmailTracking';
        $responses['/rpc'] = 'CM_Http_Response_RPC';
        $responses['/upload'] = 'CM_Http_Response_Upload';
        $responses['/library-css'] = 'CM_Http_Response_Resource_Css_Library';
        $responses['/vendor-css'] = 'CM_Http_Response_Resource_Css_Vendor';
        $responses['/library-js'] = 'CM_Http_Response_Resource_Javascript_Library';
        $responses['/vendor-js'] = 'CM_Http_Response_Resource_Javascript_Vendor';
        $responses['/layout'] = 'CM_Http_Response_Resource_Layout';
        $responses['/ajax'] = 'CM_Http_Response_View_Ajax';
        $responses['/form'] = 'CM_Http_Response_View_Form';
        $responses['/homepage'] = 'CM_Http_Response_Page';

        $responseClassList = array_values($responses);
        $factory = new CM_Http_Response_Factory($this->getServiceManager(), $responseClassList);

        foreach ($responses as $path => $expectedResponse) {
            $request = new CM_Http_Request_Post($path . '/null/timestamp', null, null, '[]');
            $this->assertInstanceOf($expectedResponse, $factory->getResponse($request));
        }
    }

}
