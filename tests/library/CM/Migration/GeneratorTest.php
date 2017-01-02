<?php

class CM_Migration_GeneratorTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testInstantiationThrows() {
        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () {
            $tmp = CM_Service_Manager::getInstance()->getFilesystems()->getTmp();
            $generator = new CM_Migration_Generator($tmp, 'Not_Existing_Class');
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Parent migration class does not exist', $exception->getMessage());
        $this->assertSame([
            'parentClassName' => 'Not_Existing_Class',
        ], $exception->getMetaInfo());
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
            sprintf('class CM_Migration_Script_%s_FooBarBaz extends \CM_Migration_Script {', $time),
            '',
            '    /**',
            '     * Describe the migration script',
            '     */',
            '    public function up() {',
            '        // TODO: Implement the migration script',
            '    }',
            '}',
            '',
        ]), $file->read());
    }
}
