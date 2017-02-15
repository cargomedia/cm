<?php

class CM_Migration_LoaderTest extends CMTest_TestCase {

    /** @var CM_File_Filesystem */
    private $_tmp;

    protected function setUp() {
        $dirTmp = CM_Bootloader::getInstance()->getDirTmp();
        $adapter = new CM_File_Filesystem_Adapter_Local($dirTmp . self::class);
        $tmp = new CM_File_Filesystem($adapter);
        $dir = new CM_File('.', $tmp);
        $dir->ensureParentDirectory();
        $this->_tmp = $tmp;
    }

    public function tearDown() {
        $dir = new CM_File('.', $this->_tmp);
        $dir->delete(true);
        CMTest_TH::clearEnv();
    }

    public function testGetScriptList() {
        $tmp = $this->_tmp;
        $generator = new CM_Migration_Generator($tmp);
        $generator->save('boo');
        CMTest_TH::timeForward(10);
        $generator->save('abc');
        CMTest_TH::timeForward(10);
        $bar = $generator->save('baz');
        $bar->rename('000-custom-name.php');

        // test

        $loader = new CM_Migration_Loader($this->getServiceManager(), [$tmp->getAdapter()->getPathPrefix()]);

        $list = $loader->getRunnerList();
        $this->assertInstanceOf('Generator', $list);

        $list->rewind();

        $current = $list->current();
        $this->assertRegExp('/Migration_[0-9]+_Baz/', $current->getScriptClassName());
        $this->assertSame('000-custom-name', $current->getName());

        $list->next();
        $current = $list->current();
        $this->assertRegExp('/Migration_[0-9]+_Boo/', $current->getScriptClassName());
        $this->assertContains('_Boo', $current->getName());

        $list->next();
        $current = $list->current();
        $this->assertRegExp('/Migration_[0-9]+_Abc/', $current->getScriptClassName());
        $this->assertContains('_Abc', $current->getName());
    }

    public function testFindScript() {
        $tmp = $this->_tmp;
        $generator = new CM_Migration_Generator($tmp);

        $fooTime = CMTest_TH::time();
        $generator->save('foo');
        CMTest_TH::timeForward(10);
        $bar = $generator->save('bar');
        $bar->rename('custom-name.php');

        // test

        $loader = new CM_Migration_Loader($this->getServiceManager(), [$tmp->getAdapter()->getPathPrefix()]);

        $this->assertNull($loader->findRunner('unknown'));

        $filename = sprintf('%s_Foo', $fooTime);
        $foo = $loader->findRunner($filename);
        $this->assertNotNull($foo);
        $this->assertRegExp('/Migration_[0-9]+_Foo/', $foo->getScriptClassName());
        $this->assertSame($filename, $foo->getName());

        $bar = $loader->findRunner('custom-name');
        $this->assertNotNull($bar);
        $this->assertRegExp('/Migration_[0-9]+_Bar/', $bar->getScriptClassName());
        $this->assertSame('custom-name', $bar->getName());
    }

    public function test_requireScript() {
        $tmp = $this->_tmp;
        $loader = new CM_Migration_Loader($this->getServiceManager(), [$tmp->getAdapter()->getPathPrefix()]);

        $good = new CM_File('good.php', $tmp);
        $good->write(join(PHP_EOL, [
            '<?php',
            'class Good_Migration implements CM_Migration_UpgradableInterface { public function up(CM_OutputStream_Interface $output) {} }',
            ''
        ]));
        $this->assertSame('Good_Migration', CMTest_TH::callProtectedMethod($loader, '_requireScript', [$good]));

        $goodExtendsClass = new CM_File('good-extends-class.php', $tmp);
        $goodExtendsClass->write(join(PHP_EOL, [
            '<?php',
            'class Migration_3 extends CMTest_Mock_MigrationScript {}',
            ''
        ]));
        $this->assertSame('Migration_3', CMTest_TH::callProtectedMethod($loader, '_requireScript', [$goodExtendsClass]));

        $wrong = new CM_File('wrong.php', $tmp);
        $wrong->write(join(PHP_EOL, [
            '<?php',
            'class Wrong_Migration {}',
            ''
        ]));
        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($loader, $wrong) {
            CMTest_TH::callProtectedMethod($loader, '_requireScript', [$wrong]);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Migration script must implements CM_Migration_UpgradableInterface', $exception->getMessage());
        $this->assertSame(['Wrong_Migration'], $exception->getMetaInfo()['classes']);
        $this->assertContains('wrong.php', $exception->getMetaInfo()['filePath']);

        $wrongNoClass = new CM_File('wrong-no-class.php', $tmp);
        $wrongNoClass->write(join(PHP_EOL, [
            '<?php',
            ''
        ]));
        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($loader, $wrongNoClass) {
            CMTest_TH::callProtectedMethod($loader, '_requireScript', [$wrongNoClass]);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Migration script must declare one class and one class only', $exception->getMessage());
        $this->assertEmpty($exception->getMetaInfo()['classes']);
        $this->assertContains('wrong-no-class.php', $exception->getMetaInfo()['filePath']);

        $wrongMultiClass = new CM_File('wrong-multi-class.php', $tmp);
        $wrongMultiClass->write(join(PHP_EOL, [
            '<?php',
            'class Migration_1 implements CM_Migration_UpgradableInterface { public function up(CM_OutputStream_Interface $output) {} }',
            'class Migration_2 implements CM_Migration_UpgradableInterface { public function up(CM_OutputStream_Interface $output) {} }',
            ''
        ]));
        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($loader, $wrongMultiClass) {
            CMTest_TH::callProtectedMethod($loader, '_requireScript', [$wrongMultiClass]);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Migration script must declare one class and one class only', $exception->getMessage());
        $this->assertSame(['Migration_1', 'Migration_2'], $exception->getMetaInfo()['classes']);
        $this->assertContains('wrong-multi-class.php', $exception->getMetaInfo()['filePath']);
    }

    public function test_prepareScript() {
        $tmp = $this->_tmp;
        $generator = new CM_Migration_Generator($tmp);
        $script = $generator->save('tata');
        $loader = new CM_Migration_Loader($this->getServiceManager(), [$tmp->getAdapter()->getPathPrefix()]);
        $tata = CMTest_TH::callProtectedMethod($loader, '_instantiateRunner', [$script]);
        $this->assertRegExp('/Migration_[0-9]+_Tata/', $tata->getScriptClassName());
        $this->assertSame(sprintf('%s_Tata', CMTest_TH::time()), $tata->getName());
    }
}
