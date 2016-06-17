<?php

class CM_Log_Handler_FluentdTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testConstructor() {
        $fluentd = $this->mockClass('\Fluent\Logger\FluentLogger')->newInstanceWithoutConstructor();
        /** @var \Fluent\Logger\FluentLogger $fluentd */
        
        $contextFormatter = $this->mockInterface('CM_Log_ContextFormatter_Interface')->newInstanceWithoutConstructor();
        /** @var CM_Log_ContextFormatter_Interface $contextFormatter */
        
        $handler = new CM_Log_Handler_Fluentd($fluentd, $contextFormatter, 'tag');
        $this->assertInstanceOf('CM_Log_Handler_Fluentd', $handler);
    }

    public function testFormatting() {
        $fluentd = $this->mockClass('\Fluent\Logger\FluentLogger')->newInstanceWithoutConstructor();
        /** @var \Fluent\Logger\FluentLogger $fluentd */

        $contextFormatter = $this->mockInterface('CM_Log_ContextFormatter_Interface')->newInstanceWithoutConstructor();
        $getRecordContext = $contextFormatter->mockMethod('getRecordContext')->set('formatted-record');
        /** @var CM_Log_ContextFormatter_Interface $contextFormatter */
        
        $handler = new CM_Log_Handler_Fluentd($fluentd, $contextFormatter, 'tag');
        $record = $this->mockClass('CM_Log_Record')->newInstanceWithoutConstructor();
        $formattedRecord = $this->callProtectedMethod($handler, '_formatRecord', [$record]);
        
        $this->assertSame(1, $getRecordContext->getCallCount());
        $this->assertSame($record, $getRecordContext->getLastCall()->getArgument(0));
        $this->assertSame('formatted-record', $formattedRecord);
        
    }

    public function testWriteRecord() {
        $fluentd = $this->mockClass('\Fluent\Logger\FluentLogger')->newInstanceWithoutConstructor();
        $postMock = $fluentd->mockMethod('post')->set(
            function ($tag, array $data) {
                $this->assertSame('tag', $tag);
                $this->assertSame('value', $data['key']);
            }
        );
        /** @var \Fluent\Logger\FluentLogger $fluentd */

        $contextFormatter = $this->mockInterface('CM_Log_ContextFormatter_Interface')->newInstanceWithoutConstructor();
        $contextFormatter->mockMethod('getRecordContext')->set(['key' => 'value']);
        /** @var CM_Log_ContextFormatter_Interface $contextFormatter */
        
        $handler = new CM_Log_Handler_Fluentd($fluentd, $contextFormatter, 'tag');

        $record = new CM_Log_Record(CM_Log_Logger::CRITICAL, 'foo', new CM_Log_Context());
        $this->callProtectedMethod($handler, '_writeRecord', [$record]);
        $this->assertSame(1, $postMock->getCallCount());
    }
}
