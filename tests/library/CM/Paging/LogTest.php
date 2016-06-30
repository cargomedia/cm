<?php

class CM_Paging_LogTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testConstructorValidation() {
        $exception = $this->catchException(function () {
            new CM_Paging_Log([1]);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Log level `1` does not exist.', $exception->getMessage());

        $exception = $this->catchException(function () {
            new CM_Paging_Log([CM_Log_Logger::INFO], 1);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Type is not a children of CM_Paging_Log.', $exception->getMessage());
    }

    public function testAddGet() {
        $handler = new CM_Log_Handler_MongoDb(CM_Paging_Log::COLLECTION_NAME);
        $user = CMTest_TH::createUser();
        $context = new CM_Log_Context();
        $context->setExtra(['bar' => 'quux']);
        $context->setUser($user);
        $record1 = new CM_Log_Record(CM_Log_Logger::DEBUG, 'foo', $context);
        $record2 = new CM_Log_Record(CM_Log_Logger::INFO, 'baz', new CM_Log_Context());

        $handler->handleRecord($record1);
        $handler->handleRecord($record2);

        $paging = new CM_Paging_Log([CM_Log_Logger::DEBUG, CM_Log_Logger::INFO]);

        $items = $paging->getItems();
        $this->assertSame(2, count($items));

        $this->assertSame('baz', $items[0]['message']);
        $this->assertSame(CM_Log_Logger::INFO, $items[0]['level']);

        $this->assertSame('foo', $items[1]['message']);
        $this->assertSame(CM_Log_Logger::DEBUG, $items[1]['level']);
        $this->assertSame($user->getDisplayName(), $items[1]['context']['user']['name']);
        $this->assertSame(['bar' => 'quux'], $items[1]['context']['extra']);

        $age = 86400;
        CMTest_TH::timeForward($age);
        CMTest_TH::timeForward($age);
        $record3 = new CM_Log_Record(CM_Log_Logger::CRITICAL, 'bar', new CM_Log_Context());
        $handler->handleRecord($record3);

        $paging2 = new CM_Paging_Log([CM_Log_Logger::CRITICAL], null, false, $age + 1);
        $items = $paging2->getItems();
        $this->assertSame(1, count($items));

        $this->assertSame('bar', $items[0]['message']);
        $this->assertSame(CM_Log_Logger::CRITICAL, $items[0]['level']);
    }

    public function testCleanUp() {
        $handler = new CM_Log_Handler_MongoDb(CM_Paging_Log::COLLECTION_NAME);
        $context1 = new CM_Log_Context();
        $context1->setExtra(['bar' => 'quux']);
        $record1 = new CM_Log_Record(CM_Log_Logger::DEBUG, 'foo', $context1);
        $record2 = new CM_Log_Record(CM_Log_Logger::DEBUG, 'baz', new CM_Log_Context());
        $record3 = new CM_Log_Record(CM_Log_Logger::CRITICAL, 'bar', new CM_Log_Context());
        $record4 = new CM_Log_Record(CM_Log_Logger::INFO, 'bazBar', new CM_Log_Context());
        $contextTyped = new CM_Log_Context();
        $contextTyped->setExtra(['type' => 1]);
        $typedRecord = new CM_Log_Record(CM_Log_Logger::DEBUG, 'quux', $contextTyped);

        $paging = new CM_Paging_Log([CM_Log_Logger::DEBUG, CM_Log_Logger::INFO]);

        $this->assertSame(0, $paging->getCount());

        $handler->handleRecord($record1);
        $handler->handleRecord($record2);
        $handler->handleRecord($record3);
        $handler->handleRecord($record4);
        $handler->handleRecord($typedRecord);
        $paging->_change();

        $this->assertSame(4, $paging->getCount());

        $age = 7 * 86400 + 1;
        CMTest_TH::timeForward($age);
        $paging->cleanUp();
        $this->assertSame(0, $paging->getCount());
    }

    public function testFlush() {
        $handler = new CM_Log_Handler_MongoDb(CM_Paging_Log::COLLECTION_NAME);
        $context1 = new CM_Log_Context();
        $context1->setExtra(['bar' => 'quux']);
        $record1 = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', $context1);
        $record2 = new CM_Log_Record(CM_Log_Logger::INFO, 'baz', new CM_Log_Context());
        $record3 = new CM_Log_Record(CM_Log_Logger::CRITICAL, 'quux', new CM_Log_Context());
        $contextTyped = new CM_Log_Context();
        $contextTyped->setExtra(['type' => 1]);
        $typedRecord = new CM_Log_Record(CM_Log_Logger::INFO, 'baz', $contextTyped);

        $paging = new CM_Paging_Log([CM_Log_Logger::INFO, CM_Log_Logger::CRITICAL]);

        $this->assertSame(0, $paging->getCount());

        $handler->handleRecord($record1);
        $handler->handleRecord($record2);
        $handler->handleRecord($record3);
        $handler->handleRecord($typedRecord);
        $paging->_change();
        $this->assertSame(4, $paging->getCount());

        $paging->flush();
        $this->assertSame(0, $paging->getCount());
    }

    public function testAggregate() {
        $handler = new CM_Log_Handler_MongoDb(CM_Paging_Log::COLLECTION_NAME);

        $context1 = new CM_Log_Context();
        $context1->setExtra(['bar' => 'quux']);
        $record1 = new CM_Log_Record(CM_Log_Logger::DEBUG, 'foo', $context1);

        $context2 = new CM_Log_Context();
        $record2 = new CM_Log_Record(CM_Log_Logger::DEBUG, 'baz', $context2);

        $exception = new CM_Exception_Invalid('Bad news', CM_Exception::WARN, ['baz' => 'bar']);
        $context3 = new CM_Log_Context();
        $context3->setException($exception);
        $record3 = new CM_Log_Record(CM_Log_Logger::WARNING, 'bar', $context3);

        //they will not be found
        $handler->handleRecord($record1);
        $handler->handleRecord($record2);
        $handler->handleRecord($record3);

        CMTest_TH::timeDaysForward(2);

        //recreate records to correctly set up CM_Log_Record::createdAt
        $context1 = new CM_Log_Context();
        $context1->setExtra(['bar' => 'quux']);
        $record1 = new CM_Log_Record(CM_Log_Logger::DEBUG, 'foo', $context1);
        $context3 = new CM_Log_Context();
        $context3->setException($exception);
        $record3 = new CM_Log_Record(CM_Log_Logger::WARNING, 'bar', $context3);

        $handler->handleRecord($record1);
        $handler->handleRecord($record3);
        $handler->handleRecord($record3);

        CMTest_TH::timeDaysForward(1);
        $exception2 = new CM_Exception_Invalid('Some info', CM_Exception::FATAL, ['foo' => 'bar']);
        //recreate records to correctly set up CM_Log_Record::createdAt
        $context1 = new CM_Log_Context();
        $context1->setExtra(['bar' => 'quux']);
        $record1 = new CM_Log_Record(CM_Log_Logger::DEBUG, 'foo', $context1);

        $context2 = new CM_Log_Context();
        $record2 = new CM_Log_Record(CM_Log_Logger::DEBUG, 'baz', $context2);

        $context3 = new CM_Log_Context();
        $context3->setException($exception);
        $record3 = new CM_Log_Record(CM_Log_Logger::DEBUG, 'Error bar', $context3);

        $context4 = new CM_Log_Context();
        $context4->setException($exception2);
        $record4 = new CM_Log_Record(CM_Log_Logger::DEBUG, 'Error bar', $context4);

        $handler->handleRecord($record2);
        $handler->handleRecord($record2);
        $handler->handleRecord($record2);
        $handler->handleRecord($record3);
        $handler->handleRecord($record3);
        $handler->handleRecord($record4);
        $handler->handleRecord($record1);
        $handler->handleRecord($record1);
        $handler->handleRecord($record1);

        $paging = new CM_Paging_Log([CM_Log_Logger::DEBUG], null, true, 2 * 86400);
        $this->assertSame(4, $paging->getCount());
        $foundRecord1 = $paging->getItem(0);
        $foundRecord2 = $paging->getItem(1);
        $foundRecord3 = $paging->getItem(2);
        $foundRecord4 = $paging->getItem(3);

        $this->assertSame(4, $foundRecord1['count']);
        $this->assertSame(3, $foundRecord2['count']);
        $this->assertSame(2, $foundRecord3['count']);
        $this->assertSame(1, $foundRecord4['count']);

        $this->assertSame('foo', $foundRecord1['message']);
        $this->assertSame('baz', $foundRecord2['message']);
        $this->assertSame('Error bar', $foundRecord3['message']);
        $this->assertSame('Bad news', $foundRecord3['exception']['message']);
        $this->assertSame('Error bar', $foundRecord4['message']);
        $this->assertSame('Some info', $foundRecord4['exception']['message']);
    }
}
