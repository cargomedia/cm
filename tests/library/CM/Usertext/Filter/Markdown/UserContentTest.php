<?php

class CM_Usertext_Filter_Markdown_UserContentTest extends CMTest_TestCase
{
    protected function setUp()
    {
//        $serviceManager = new CM_Service_Manager();
//        $filesystemDefault = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local());
//        $serviceManager->registerInstance('usercontent', $filesystemDefault);
//
//        $config = array(
//            'default' => array(
//                'url' => 'http://example.com/default',
//                'filesystem' => 'usercontent',
//            ),
//        );
//        $this->_service = new CM_Service_UserContent($config);
//        $this->_service->setServiceManager($serviceManager);
    }

    public function tearDown()
    {
        CMTest_TH::clearEnv();
    }

    public function testProcess()
    {
        $text = 'test ![usercontent](formatter/1.jpg) test' . PHP_EOL . '![usercontent](formatter/2.jpg)![usercontent](formatter/3.jpg)nospace![usercontent](formatter/4.jpg)';

        $usercontentPrefix = 'http://localhost/userfiles/usercontent/';
        $expected = 'test <img src="' . $usercontentPrefix . 'formatter/1.jpg" alt="image"/> test' . PHP_EOL . '<img src="' . $usercontentPrefix . 'formatter/2.jpg" alt="image"/><img src="' . $usercontentPrefix . 'formatter/3.jpg" alt="image"/>nospace<img src="' . $usercontentPrefix . 'formatter/4.jpg" alt="image"/>';
        $filter = new CM_Usertext_Filter_Markdown_UserContent();
        $actual = $filter->transform($text, new CM_Render());

        $this->assertSame($expected, $actual);
    }

    public function testInvalid()
    {
        $text = 'test ![usercontent](formatter/1.jpg';
        $expected = $text;
        $filter = new CM_Usertext_Filter_Markdown_UserContent();
        $actual = $filter->transform($text, new CM_Render());

        $this->assertSame($expected, $actual);
    }
}
