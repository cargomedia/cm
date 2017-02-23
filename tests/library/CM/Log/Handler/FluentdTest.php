<?php

class CM_Log_Handler_FluentdTest extends CMTest_TestCase {

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
        $getFromattedContext = $contextFormatter->mockMethod('formatContext')->set(['bar' => 'foo']);
        /** @var CM_Log_ContextFormatter_Interface $contextFormatter */

        $handler = new CM_Log_Handler_Fluentd($fluentd, $contextFormatter, 'tag');
        $record = new CM_Log_Record(CM_Log_Logger::DEBUG, 'log message foo', new CM_Log_Context());
        $formattedRecord = $this->callProtectedMethod($handler, '_formatRecord', [$record]);

        $this->assertSame(1, $getFromattedContext->getCallCount());
        $this->assertSame('log message foo', $formattedRecord['message']);
        $this->assertSame('debug', $formattedRecord['level']);
        $this->assertSame('foo', $formattedRecord['bar']);
        $this->assertArrayHasKey('timestamp', $formattedRecord);
    }

    public function testWriteRecord() {
        $fluentd = $this->mockClass('\Fluent\Logger\FluentLogger')->newInstanceWithoutConstructor();
        $postMock = $fluentd->mockMethod('post')
            ->at(0, function ($tag, array $data) {
                $this->assertSame('tag', $tag);
                $this->assertSame('critical', $data['level']);
                $this->assertSame('foo', $data['message']);
                $this->assertRegExp('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}\+\d{4}$/', $data['timestamp']);
                $this->assertSame('value', $data['key']);
                return true;
            })
            ->at(1, function ($tag, array $data) {
                return false;
            });
        /** @var \Fluent\Logger\FluentLogger $fluentd */

        $contextFormatter = $this->mockInterface('CM_Log_ContextFormatter_Interface')->newInstanceWithoutConstructor();
        $contextFormatter->mockMethod('formatContext')->set(['key' => 'value']);
        /** @var CM_Log_ContextFormatter_Interface $contextFormatter */

        $handler = new CM_Log_Handler_Fluentd($fluentd, $contextFormatter, 'tag');

        $record = new CM_Log_Record(CM_Log_Logger::CRITICAL, 'foo', new CM_Log_Context());
        $this->callProtectedMethod($handler, '_writeRecord', [$record]);
        $this->assertSame(1, $postMock->getCallCount());

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'bar', new CM_Log_Context());
        $exception = $this->catchException(function () use ($handler, $record) {
            $this->callProtectedMethod($handler, '_writeRecord', [$record]);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Could not write to fluentd', $exception->getMessage());
        $this->assertSame(2, $postMock->getCallCount());
    }

    public function testSanitizeRecord() {
        $mockHandler = $this->mockClass('CM_Log_Handler_Fluentd')->newInstanceWithoutConstructor();

        $record = [
            'foo'  => [
                'baz' => 'quux',
                'bar' => pack("H*", 'c32e')
            ],
            'foo2' => 2,
        ];
        $sanitizedRecord = $this->callProtectedMethod($mockHandler, '_sanitizeRecord', [$record]);

        $this->assertSame([
            'foo'  => [
                'baz' => 'quux',
                'bar' => '?.',
            ],
            'foo2' => 2,
        ], $sanitizedRecord);
    }

    public function test_encodeRecord() {
        /** @var CM_Log_Handler_Fluentd|\Mocka\AbstractClassTrait $mockHandler */
        $mockHandler = $this->mockClass('CM_Log_Handler_Fluentd')->newInstanceWithoutConstructor();

        $this->assertSame([], CMTest_TH::callProtectedMethod($mockHandler, '_encodeRecord', [[]]));

        $record = [
            'foo' => [
                'id'  => 123,
                'bar' => (object) ['baz'],
                'baz' => new DateTime('01-01-2001'),
                'bax' => new CM_Model_Mock_Fluentd(),
            ],
        ];
        $this->assertSame([
            'foo' => [
                'id'  => '123',
                'bar' => [
                    'class' => 'stdClass'
                ],
                'baz' => '2001-01-01T00:00:00+00:00',
                'bax' => [
                    'class' => 'CM_Model_Mock_Fluentd',
                    'id'    => '42',
                ],
            ],
        ], CMTest_TH::callProtectedMethod($mockHandler, '_encodeRecord', [$record]));
    }
}

class CM_Model_Mock_Fluentd extends CM_Model_Abstract {

    public function getIdRaw() {
        return ['id' => 42];
    }
}
