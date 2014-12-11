<?php

class CM_Clockwork_Storage_FileSystemTest extends CMTest_TestCase {

    public function testGetSetRuntime() {
        $defaultTimeZoneBackup = date_default_timezone_get();

        $interval = '1 day';
        $timezone = new DateTimeZone('Europe/Berlin');
        $event1 = new CM_Clockwork_Event('foo', $interval);
        $event2 = new CM_Clockwork_Event('bar', $interval);
        $date1 = new DateTime('2014-10-31 08:00:00', $timezone);
        $date2 = new DateTime('2014-10-31 08:02:03', $timezone);

        $context = 'persistence-test';
        $storage = new CM_Clockwork_Storage_FileSystem($context);
        $serviceManager = CM_Service_Manager::getInstance();
        $storage->setServiceManager($serviceManager);

        $filepath = 'clockwork/' . md5($context) . '.json';
        $file = new CM_File($filepath, $serviceManager->getFilesystems()->getData());
        $this->assertFalse($file->exists());
        $this->assertFalse($file->getParentDirectory()->exists());
        $storage->setRuntime($event1, $date1);
        $this->assertTrue($file->getParentDirectory()->exists());
        $this->assertTrue($file->exists());
        $storage->setRuntime($event2, $date2);

        date_default_timezone_set('Antarctica/Vostok');
        $storage = new CM_Clockwork_Storage_FileSystem($context);
        $storage->setServiceManager($serviceManager);

        $this->assertEquals($date1, $storage->getLastRuntime($event1));
        $this->assertEquals($date2, $storage->getLastRuntime($event2));
        date_default_timezone_set($defaultTimeZoneBackup);
    }
}
