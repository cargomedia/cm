<?php

class CM_Clockwork_PersistenceTest extends CMTest_TestCase {

    /** @var DateTime */
    private $_datetime1;

    /** @var DateTime */
    private $_datetime2;

    /** @var string */
    private $_fileContent;

    /** @var  CM_File_Filesystem */
    private $_fileSystem;

    /** @var  CM_File */
    private $_file;

    /** @var  string */
    private $_context;


    public function setup() {
        $this->_datetime1 = new DateTime('455-01-06 03:15:02');
        $this->_datetime2 = new DateTime('1375-04-08 13:34:59');
        $this->_fileContent = <<<EOT
{"foo":"0455-01-06 03:15:02","bar":"1375-04-08 13:34:59"}
EOT;
        $this->_context = 'persistence-test';
        $this->_fileSystem = CM_Service_Manager::getInstance()->getFilesystems()->getData();

        $this->_file = new CM_File('clockwork' . DIRECTORY_SEPARATOR . $this->_context . '.json', $this->_fileSystem);
    }

    public function tearDown() {
        CMTest_TH::clearFilesystem();
    }

    public function testGetLastRunTime() {
        $this->_fileSystem->ensureDirectory('clockwork');
        CM_File::create($this->_file->getPath(), $this->_fileContent, $this->_fileSystem);
        $interval = new DateInterval('P1D');
        $event1 = new CM_Clockwork_Event('foo', $interval);
        $event2 = new CM_Clockwork_Event('bar', $interval);
        $event3 = new CM_Clockwork_Event('nonexistent', $interval);

        $persistence = new CM_Clockwork_Persistence($this->_context);

        $this->assertEquals($this->_datetime1, $persistence->getLastRuntime($event1));
        $this->assertEquals($this->_datetime2, $persistence->getLastRuntime($event2));
        $this->assertNull($persistence->getLastRuntime($event3));
    }

    public function testSetRuntime() {
        $interval = new DateInterval('P1D');
        $event1 = new CM_Clockwork_Event('foo', $interval);
        $event2 = new CM_Clockwork_Event('bar', $interval);

        $persistence = new CM_Clockwork_Persistence($this->_context);

        $dir = new CM_File('clockwork', $this->_fileSystem);
        $this->assertFalse($dir->getExists());
        $this->assertFalse($this->_file->getExists());
        $persistence->setRuntime($event1, $this->_datetime1);
        $this->assertTrue($dir->getExists());
        $this->assertTrue($this->_file->getExists());
        $persistence->setRuntime($event2, $this->_datetime2);

        $this->assertEquals($this->_datetime1, $persistence->getLastRuntime($event1));
        $this->assertEquals($this->_datetime2, $persistence->getLastRuntime($event2));

        $this->assertSame($this->_fileContent, $this->_file->read());
    }
}
