<?php

class CM_Log_LoggerTest extends CMTest_TestCase {

    public function testConstructor() {
        /** @var CM_Log_Context $context */
        $context = $this->mockClass('CM_Log_Context')->newInstanceWithoutConstructor();

        /** @var CM_Log_Handler_HandlerInterface $handler */
        $handler = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstanceWithoutConstructor();

        $loggerClass = $this->mockClass('CM_Log_Logger');
        $setHandler = $loggerClass->mockMethod('setHandler');
        $setContext = $loggerClass->mockMethod('setContext');

        $loggerClass->newInstance([$context, $handler]);
        $this->assertSame($handler, $setHandler->getCall(0)->getArgument(0));
        $this->assertSame($context, $setContext->getCall(0)->getArgument(0));
    }

    public function testSetGetContext() {
        /** @var CM_Log_Context $context */
        $context = $this->mockClass('CM_Log_Context')->newInstanceWithoutConstructor();

        $logger = new CM_Log_Logger();
        $logger->setContext($context);
        $this->assertSame($context, $logger->getContext());
    }

    public function testSetGetHandler() {
        /** @var CM_Log_Handler_HandlerInterface $handler */
        $handler = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstanceWithoutConstructor();

        $logger = new CM_Log_Logger();
        $logger->setHandler($handler);
        $this->assertSame($handler, $logger->getHandler());
    }

    public function testAddRecord() {
        $handler = $this->mockInterface('CM_Log_Handler_HandlerInterface')->newInstanceWithoutConstructor();
        $addRecord = $handler->mockMethod('handleRecord');
        /** @var CM_Log_Handler_HandlerInterface $handler */

        /** @var CM_Log_Record $record */
        $record = $this->mockClass('CM_Log_Record')->newInstanceWithoutConstructor();

        $logger = new CM_Log_Logger();
        $logger->setHandler($handler);
        $this->callProtectedMethod($logger, '_addRecord', [$record]);
        $this->assertSame([$record], $addRecord->getCall(0)->getArguments());
    }

    public function testAddMessage() {
        $loggerContextClass = $this->mockClass('CM_Log_Context');
        $merge = $loggerContextClass->mockMethod('merge');
        $loggerContext = $loggerContextClass->newInstanceWithoutConstructor();
        /** @var CM_Log_Context $loggerContext */

        $logger = $this->mockObject('CM_Log_Logger');

        $addRecord = $logger->mockMethod('_addRecord');
        /** @var CM_Log_Logger $logger */
        $message = 'messageFoo';
        $level = CM_Log_Logger::INFO;
        $context = new CM_Log_Context();
        $logger->addMessage($message, $level, $context);
        /** @var CM_Log_Record $record */
        $record = $addRecord->getCall(0)->getArgument(0);
        $this->assertSame($message, $record->getMessage());
        $this->assertSame($level, $record->getLevel());
        $this->assertSame(0, $merge->getCallCount());

        $logger->setContext($loggerContext);
        $message = 'message';
        $level = CM_Log_Logger::DEBUG;
        $context = new CM_Log_Context();
        $logger->addMessage($message, $level, $context);
        $this->assertSame([$context], $merge->getCall(0)->getArguments());
        /** @var CM_Log_Record $record */
        $record = $addRecord->getCall(1)->getArgument(0);
        $this->assertSame($message, $record->getMessage());
        $this->assertSame($level, $record->getLevel());
        $this->assertInstanceOf($loggerContextClass->getClassName(), $record->getContext());
    }

    public function testLevelHelpers() {
        $levels = CM_Log_Logger::getLevels();

        $logger = $this->mockObject('CM_Log_Logger');
        $addMessage = $logger->mockMethod('addMessage');
        /** @var CM_Log_Logger $logger */

        $message = 'message';
        $context = new CM_Log_Context();

        foreach ($levels as $label => $code) {
            $methodName = strtolower($label);
            $logger->$methodName($message, $context);
            $this->assertSame([$message, $code, $context], $addMessage->getCalls()->getLast()->getArguments());
        }
    }

    public function testStaticLogLevelMethods() {
        $this->assertSame('INFO', CM_Log_Logger::getLevelName(CM_Log_Logger::INFO));
        $this->assertNotEmpty(CM_Log_Logger::getLevels());
        $this->assertTrue(CM_Log_Logger::hasLevel(CM_Log_Logger::INFO));
        $this->assertFalse(CM_Log_Logger::hasLevel(666));
    }
}
