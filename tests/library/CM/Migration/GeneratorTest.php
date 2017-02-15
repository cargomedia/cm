<?php

class CM_Migration_GeneratorTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testSave() {
        $tmp = CM_Service_Manager::getInstance()->getFilesystems()->getTmp();
        $generator = new CM_Migration_Generator($tmp);
        $time = CMTest_TH::time();
        $file = $generator->save('  foo-bar_baz');

        $path = $file->getPathOnLocalFilesystem();

        $this->assertSame(sprintf('%s_FooBarBaz.php', $time), $file->getFileName());
        $this->assertSame(join(PHP_EOL, [
            '<?php',
            '',
            sprintf('class Migration_%s_FooBarBaz implements \CM_Migration_UpgradableInterface, \CM_Service_ManagerAwareInterface {', $time),
            '',
            '    use CM_Service_ManagerAwareTrait;',
            '',
            '    public function up(\CM_OutputStream_Interface $output) {',
            '        // TODO: Implement the migration script',
            '    }',
            '}',
            '',
        ]), $file->read());
    }

    public function test_sanitize() {
        $tmp = CM_Service_Manager::getInstance()->getFilesystems()->getTmp();
        $generator = new CM_Migration_Generator($tmp);

        $this->assertSame('FooBar', CMTest_TH::callProtectedMethod($generator, '_sanitize', ['foo_Bar']));
        $this->assertSame('FooBar', CMTest_TH::callProtectedMethod($generator, '_sanitize', ['  fOO_bar']));
        $this->assertSame('FooBar', CMTest_TH::callProtectedMethod($generator, '_sanitize', ['foo_bAr']));
        $this->assertSame('FooBar', CMTest_TH::callProtectedMethod($generator, '_sanitize', ['foo_bAr']));
        $this->assertSame('123', CMTest_TH::callProtectedMethod($generator, '_sanitize', [123]));
        $this->assertSame('1_2_3', CMTest_TH::callProtectedMethod($generator, '_sanitize', ['1_2_3']));
        $this->assertSame('Foo1Bar2_3baz', CMTest_TH::callProtectedMethod($generator, '_sanitize', ['foo1-bar2_3baz']));

        $invalidNames = [
            null, '', 'foo bar', 'foo/bar', 'foo!bar', 'foo-1', '1_2-3', '%^$@',
        ];
        foreach ($invalidNames as $invalidName) {
            /** @var CM_Exception_Invalid $exception */
            $exception = $this->catchException(function () use ($generator, $invalidName) {
                CMTest_TH::callProtectedMethod($generator, '_sanitize', [$invalidName]);
            });
            $this->assertInstanceOf('CM_Exception_Invalid', $exception);
            $this->assertSame('Invalid migration script name', $exception->getMessage());
            $this->assertSame([
                'scriptName' => $invalidName,
            ], $exception->getMetaInfo());
        }
    }
}
