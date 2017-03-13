<?php

class CM_Frontend_Bundler_AbstractTest extends CMTest_TestCase {

    public function test_ParseResponse() {
        $bundler = $this->mockObject('CM_Frontend_Bundler_Abstract');

        $result = CMTest_TH::callProtectedMethod($bundler, '_parseResponse', ['{"content":"foo"}']);
        $this->assertSame("foo", $result);

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($bundler) {
            CMTest_TH::callProtectedMethod($bundler, '_parseResponse', ['invalid']);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Failed to parse cm-bundler response', $exception->getMessage());
        $this->assertSame('invalid', $exception->getMetaInfo()['cmBundlerRawResponse']);

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($bundler) {
            CMTest_TH::callProtectedMethod($bundler, '_parseResponse', ['{"error":"foo","stack":"bar"}']);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('cm-bundler has responded with an error', $exception->getMessage());
        $this->assertEquals([
            'error' => 'foo',
            'stack' => 'bar'
        ], $exception->getMetaInfo());

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($bundler) {
            CMTest_TH::callProtectedMethod($bundler, '_parseResponse', ['']);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('cm-bundler has responded without any content', $exception->getMessage());
        $this->assertSame([], $exception->getMetaInfo());
    }
}
