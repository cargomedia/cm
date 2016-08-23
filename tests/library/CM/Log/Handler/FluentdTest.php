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
        $getRecordContext = $contextFormatter->mockMethod('formatRecordContext')->set('formatted-record');
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
        $contextFormatter->mockMethod('formatRecordContext')->set(['key' => 'value']);
        /** @var CM_Log_ContextFormatter_Interface $contextFormatter */

        $handler = new CM_Log_Handler_Fluentd($fluentd, $contextFormatter, 'tag');

        $record = new CM_Log_Record(CM_Log_Logger::CRITICAL, 'foo', new CM_Log_Context());
        $this->callProtectedMethod($handler, '_writeRecord', [$record]);
        $this->assertSame(1, $postMock->getCallCount());
    }

    public function testSanitizeRecord() {
        $mock = $this->mockClass('CM_Log_Handler_Fluentd')->newInstanceWithoutConstructor();
        $record = [
            'foo'  => [
                'baz' => 'quux',
                'bar' => pack("H*", 'c32e')
            ],
            'foo2' => 2,
        ];
        $sanitizedRecord = $this->callProtectedMethod($mock, '_sanitizeRecord', [$record]);

        $this->assertSame([
            'foo'  => [
                'baz' => 'quux',
                'bar' => '?.',
            ],
            'foo2' => 2,
        ], $sanitizedRecord);
    }

    public function testArrayEncoding() {
        /** @var CM_Log_Handler_Fluentd|\Mocka\AbstractClassTrait $mock */
        $mock = $this->mockClass('CM_Log_Handler_Fluentd')->newInstanceWithoutConstructor();

        $this->assertSame([], CMTest_TH::callProtectedMethod($mock, '_encodeAsArray', [[]])); //empty array
        $array = [
            'foo4' => 'val4',
            'foo1' => ['bar1' => ['quux1' => 'val11', 'baz1' => 'val12']],
            'foo2' => ['bar2' => 'val21'],
            'foo3' => [4, '1', 3],
            'foo7' => ['bar4' => [1, 2]],
            'foo5' => '',
            'foo6' => [],
        ];
        $this->assertSame(
            [
                ['key' => 'foo1.bar1.baz1', 'value' => 'val12'],
                ['key' => 'foo1.bar1.quux1', 'value' => 'val11'],
                ['key' => 'foo2.bar2', 'value' => 'val21'],
                ['key' => 'foo3.0', 'value' => 4],
                ['key' => 'foo3.1', 'value' => '1'],
                ['key' => 'foo3.2', 'value' => 3],
                ['key' => 'foo4', 'value' => 'val4'],
                ['key' => 'foo5', 'value' => ''],
                ['key' => 'foo7.bar4.0', 'value' => 1],
                ['key' => 'foo7.bar4.1', 'value' => 2],
            ],
            CMTest_TH::callProtectedMethod($mock, '_encodeAsArray', [$array])
        );
    }
}
