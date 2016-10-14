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
        $postMock = $fluentd->mockMethod('post')->set(
            function ($tag, array $data) {
                $this->assertSame('tag', $tag);
                $this->assertSame('critical', $data['level']);
                $this->assertSame('foo', $data['message']);
                $this->assertArrayHasKey('timestamp', $data);
                $this->assertSame('value', $data['key']);

            }
        );
        /** @var \Fluent\Logger\FluentLogger $fluentd */

        $contextFormatter = $this->mockInterface('CM_Log_ContextFormatter_Interface')->newInstanceWithoutConstructor();
        $contextFormatter->mockMethod('formatContext')->set(['key' => 'value']);
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

    public function test_encodeRecord() {
        /** @var CM_Log_Handler_Fluentd|\Mocka\AbstractClassTrait $mock */
        $mock = $this->mockClass('CM_Log_Handler_Fluentd')->newInstanceWithoutConstructor();

        $this->assertSame([], CMTest_TH::callProtectedMethod($mock, '_encodeRecord', [[]]));

        $record = [
            'foo' => [
                'id'  => 123,
                'bar' => (object) ['baz'],
                'baz' => new DateTime('01-01-2001'),
                'bax' => new Fake_Model_Fluentd(),
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
                    'class' => 'Fake_Model_Fluentd',
                    'id'    => '42',
                ],
            ],
        ], CMTest_TH::callProtectedMethod($mock, '_encodeRecord', [$record]));
    }
}

class Fake_Model_Fluentd extends CM_Model_Abstract {

    public function getIdRaw() {
        return ['id' => 42];
    }
}
