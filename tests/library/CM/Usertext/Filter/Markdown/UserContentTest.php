<?php

class CM_Usertext_Filter_Markdown_UserContentTest extends CMTest_TestCase {

    /** @var string */
    protected $_usercontentUrl = 'http://example.com/default/usercontent/';

    /** @var CM_Service_Manager */
    private $_serviceManager;

    protected function setUp() {
        $this->_serviceManager = new CM_Service_Manager();

        $filesystemDefault = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local());
        $this->_serviceManager->registerInstance('filesystem-usercontent-default', $filesystemDefault);

        $config = array(
            'default' => array(
                'url' => 'http://example.com/default',
                'filesystem' => 'filesystem-usercontent-default',
            ),
        );
        $service = new CM_Service_UserContent($config);
        $this->_serviceManager->registerInstance('usercontent', $service);
        $service->setServiceManager($this->_serviceManager);
    }

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testProcess() {
        $text = 'test ![usercontent](formatter/1.jpg) test' .
            PHP_EOL . '![usercontent](formatter/2.jpg)![usercontent](formatter/3.jpg)nospace![usercontent](formatter/4.jpg)';

        $expected = 'test <img src="' . $this->_usercontentUrl . 'formatter/1.jpg" alt="image"/> test'
            . PHP_EOL . '<img src="' . $this->_usercontentUrl . 'formatter/2.jpg" alt="image"/><img src="'
            . $this->_usercontentUrl . 'formatter/3.jpg" alt="image"/>nospace<img src="' . $this->_usercontentUrl
            . 'formatter/4.jpg" alt="image"/>';
        $filter = new CM_Usertext_Filter_Markdown_UserContent($this->_serviceManager);
        $actual = $filter->transform($text, new CM_Render());

        $this->assertSame($expected, $actual);
    }

    public function testInvalid() {
        $text = 'test ![usercontent](formatter/1.jpg';
        $filter = new CM_Usertext_Filter_Markdown_UserContent($this->_serviceManager);
        $actual = $filter->transform($text, new CM_Render());
        $this->assertSame($text, $actual);

        $text = '![usercontent]()';
        $filter = new CM_Usertext_Filter_Markdown_UserContent($this->_serviceManager);
        $actual = $filter->transform($text, new CM_Render());
        $this->assertSame($text, $actual);
    }
}
