<?php

class CM_Clockwork_Storage_MongoDBTest extends CMTest_TestCase {

    public function testDataStorage() {
        $defaultTimeZoneBackup = date_default_timezone_get();
        $this->getServiceManager()->getMongoDb()->createCollection('clockwork');

        $storage = new CM_Clockwork_Storage_MongoDB('example');
        $event1 = new CM_Clockwork_Event('foo', '1sec');
        $this->_assertSameStatus(new CM_Clockwork_Event_Status(), $storage->getStatus($event1));

        $lastRuntime = DateTime::createFromFormat('U', time());
        $status1 = (new CM_Clockwork_Event_Status())->setRunning(true)->setLastRuntime($lastRuntime);
        $storage->setStatus($event1, $status1);

        $event2 = new CM_Clockwork_Event('bar', '1sec');
        $status2 = new CM_Clockwork_Event_Status();
        $status2->setRunning(false)->setLastRuntime(DateTime::createFromFormat('U', time() + 1000));
        $storage->setStatus($event2, $status2);

        date_default_timezone_set('Antarctica/Vostok');
        $this->_assertSameStatus($status1, $storage->getStatus($event1));
        $this->_assertSameStatus($status2, $storage->getStatus($event2));
        $storage2 = new CM_Clockwork_Storage_MongoDB('example');
        $storage2->fetchData();
        $this->_assertSameStatus($status1, $storage2->getStatus($event1));
        $this->_assertSameStatus($status2, $storage2->getStatus($event2));

        $status2->setRunning(true)->setLastStartTime(DateTime::createFromFormat('U', time() + 2000));
        $storage->setStatus($event2, $status2);
        $this->_assertSameStatus($status2, $storage->getStatus($event2));
        date_default_timezone_set($defaultTimeZoneBackup);
    }

    protected function _assertSameStatus(CM_Clockwork_Event_Status $expected, CM_Clockwork_Event_Status $actual) {
        $this->assertEquals($expected->getLastRuntime(), $actual->getLastRuntime());
        $this->assertSame($expected->isRunning(), $actual->isRunning());
    }
}
