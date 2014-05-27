<?php

class CM_Usertext_Filter_Markdown_UserContentTest extends CMTest_TestCase {

    /** @var string */
    protected $_usercontentUrl = 'http://example.com/usercontent/formatter/';

    /** @var CM_Service_Manager */
    private $_serviceManager;

    protected function setUp() {
        $this->_serviceManager = new CM_Service_Manager();

        $filesystemDefault = new CM_File_Filesystem(new CM_File_Filesystem_Adapter_Local());
        $this->_serviceManager->registerInstance('filesystem-usercontent-default', $filesystemDefault);

        $config = array(
            'default' => array(
                'url' => 'http://example.com/usercontent',
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
        $text = 'test ![formatter](1.jpg) test' .
            PHP_EOL . '![formatter](2.jpg)![formatter](3.jpg)nospace![formatter](4.jpg)';

        $expected = 'test ![image](' . $this->_usercontentUrl . '1.jpg) test'
            . PHP_EOL . '![image](' . $this->_usercontentUrl . '2.jpg)![image]('
            . $this->_usercontentUrl . '3.jpg)nospace![image](' . $this->_usercontentUrl
            . '4.jpg)';
        $filter = new CM_Usertext_Filter_Markdown_UserContent($this->_serviceManager);
        $actual = $filter->transform($text, new CM_Render());

        $this->assertSame($expected, $actual);
    }

    public function testInvalid() {
        $text = 'test ![formatter](1.jpg';
        $filter = new CM_Usertext_Filter_Markdown_UserContent($this->_serviceManager);
        $actual = $filter->transform($text, new CM_Render());
        $this->assertSame($text, $actual);

        $text = '![formatter]()';
        $filter = new CM_Usertext_Filter_Markdown_UserContent($this->_serviceManager);
        $actual = $filter->transform($text, new CM_Render());
        $this->assertSame($text, $actual);

        $text = '![formatter](]1.jpg)';
        $filter = new CM_Usertext_Filter_Markdown_UserContent($this->_serviceManager);
        $actual = $filter->transform($text, new CM_Render());
        $this->assertSame($text, $actual);
    }
}
