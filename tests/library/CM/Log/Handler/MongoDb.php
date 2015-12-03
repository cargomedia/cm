<?php

class CM_Log_Handler_MongoDbTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testFailWithWrongCollection() {
        $exception = $this->catchException(function () {
            new CM_Log_Handler_MongoDb('badCollection');
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('MongoDb Collection `badCollection` does not contain valid TTL index', $exception->getMessage());
    }

    public function testWriting() {
        $collection = 'cm_event_log';
        $level = CM_Log_Logger::DEBUG;
        $message = 'foo';

        $mongoClient = $this->getServiceManager()->getMongoDb();
        $this->assertSame(0, $mongoClient->count($collection));

        $mongoClient->createIndex($collection, ['expireAt' => 1], ['expireAfterSeconds' => 0]);
        $record = new CM_Log_Record($level, $message, new CM_Log_Context(null, null, null, ['bar' => ['baz' => 'quux']]));

        $handler = new CM_Log_Handler_MongoDb($collection, 30, $level);
        $this->callProtectedMethod($handler, '_writeRecord', [$record]);
        $this->assertSame(1, $mongoClient->count($collection));
    }
}
