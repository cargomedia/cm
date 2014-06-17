<?php

class CM_Clockwork_PersistenceAdapter_FileTest extends CMTest_TestCase {

    /** @var DateTime */
    private $_datetime1;

    /** @var DateTime */
    private $_datetime2;

    /** @var string */
    private $_fileContent;

    public function setup() {
        $this->_datetime1 = new DateTime('455-01-06 03:15:02');
        $this->_datetime2 = new DateTime('1375-04-08 13:34:59');
        $this->_fileContent = <<<EOT
{"foo":"0455-01-06 03:15:02","bar":"1375-04-08 13:34:59"}
EOT;
    }

    public function tearDown() {
        CMTest_TH::clearFilesystem();
    }

    public function testLoad() {
        $filesystem = CM_Service_Manager::getInstance()->getFilesystems()->getData();
        $filePath = 'clockwork' . DIRECTORY_SEPARATOR . 'clockwork-test.json';
        $filesystem->ensureDirectory('clockwork');
        $adapter = new CM_Clockwork_PersistenceAdapter_File('clockwork-test');

        $this->assertSame(array(), $adapter->load('clockwork-test'));
        CM_File::create($filePath, $this->_fileContent, $filesystem);
        $this->assertEquals(array('foo' => $this->_datetime1, 'bar' => $this->_datetime2), $adapter->load('clockwork-test'));
    }

    public function testSave() {
        $filesystem = CM_Service_Manager::getInstance()->getFilesystems()->getData();
        $filePath = 'clockwork' . DIRECTORY_SEPARATOR . 'clockwork-test.json';

        $file = new CM_File($filePath, $filesystem);
        $this->assertFalse($file->getExists());

        $adapter = new CM_Clockwork_PersistenceAdapter_File('clockwork-test');
        $adapter->save('clockwork-test', array('foo' => $this->_datetime1, 'bar' => $this->_datetime2));

        $this->assertTrue($file->getExists());
        $this->assertSame($this->_fileContent, $file->read());

    }
}
