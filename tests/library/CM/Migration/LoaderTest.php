<?php

class CM_Migration_LoaderTest extends CMTest_TestCase {

    /** @var CM_File_Filesystem */
    private $_tmp;

    protected function setUp() {
        $dirTmp = CM_Bootloader::getInstance()->getDirTmp();
        $adapter = new CM_File_Filesystem_Adapter_Local($dirTmp . self::class);
        $tmp = new CM_File_Filesystem($adapter);
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

        $list = $loader->getScriptList();
        $this->assertInstanceOf('Generator', $list);

        $list->rewind();

        $current = $list->current();
        $this->assertRegExp('/CM_Migration_Script_[0-9]+_Baz/', get_class($current));
        $this->assertSame('000-custom-name', $current->getName());

        $list->next();
        $current = $list->current();
        $this->assertRegExp('/CM_Migration_Script_[0-9]+_Boo/', get_class($current));
        $this->assertContains('_Boo', $current->getName());

        $list->next();
        $current = $list->current();
        $this->assertRegExp('/CM_Migration_Script_[0-9]+_Abc/', get_class($current));
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

        $this->assertNull($loader->findScript('unknown'));

        $filename = sprintf('%s_Foo', $fooTime);
        $foo = $loader->findScript($filename);
        $this->assertNotNull($foo);
        $this->assertRegExp('/CM_Migration_Script_[0-9]+_Foo/', get_class($foo));
        $this->assertSame($filename, $foo->getName());

        $bar = $loader->findScript('custom-name');
        $this->assertNotNull($bar);
        $this->assertRegExp('/CM_Migration_Script_[0-9]+_Bar/', get_class($bar));
        $this->assertSame('custom-name', $bar->getName());
    }

    public function test_requireScript() {
        $tmp = $this->_tmp;
        $generator = new CM_Migration_Generator($tmp);
        $script = $generator->save('toto');
        $wrongNoClass = new CM_File('wrong-no-class.php', $tmp);
        $wrongNoClass->write(join(PHP_EOL, [
            '<?php',
            ''
        ]));

        // test

        $loader = new CM_Migration_Loader($this->getServiceManager(), [$tmp->getAdapter()->getPathPrefix()]);

        $className = CMTest_TH::callProtectedMethod($loader, '_requireScript', [$script->getPathOnLocalFilesystem()]);
        $this->assertRegExp('/CM_Migration_Script_[0-9]+_Toto/', $className);

        $className = CMTest_TH::callProtectedMethod($loader, '_requireScript', [$script->getPathOnLocalFilesystem()]);
        $this->assertRegExp('/CM_Migration_Script_[0-9]+_Toto/', $className);

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($loader, $wrongNoClass) {
            CMTest_TH::callProtectedMethod($loader, '_requireScript', [$wrongNoClass->getPathOnLocalFilesystem()]);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Migration script must declare only one class', $exception->getMessage());
        $this->assertEmpty($exception->getMetaInfo()['declaredClasses']);
        $this->assertContains('wrong-no-class.php', $exception->getMetaInfo()['filePath']);
    }

    public function test_prepareScript() {
        $tmp = $this->_tmp;
        $generator = new CM_Migration_Generator($tmp);
        $script = $generator->save('tata');
        $wrong = new CM_File('wrong.php', $tmp);
        $wrong->write(join(PHP_EOL, [
            '<?php',
            'class Wrong_Migration_Script {}',
            ''
        ]));

        $loader = new CM_Migration_Loader($this->getServiceManager(), [$tmp->getAdapter()->getPathPrefix()]);

        $tata = CMTest_TH::callProtectedMethod($loader, '_prepareScript', [$script]);
        $this->assertRegExp('/CM_Migration_Script_[0-9]+_Tata/', get_class($tata));
        $this->assertSame(sprintf('%s_Tata', CMTest_TH::time()), $tata->getName());

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($loader, $wrong) {
            CMTest_TH::callProtectedMethod($loader, '_prepareScript', [$wrong]);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Migration script does not inherit from CM_Migration_Script', $exception->getMessage());
        $this->assertSame('Wrong_Migration_Script', $exception->getMetaInfo()['className']);
        $this->assertContains('wrong.php', $exception->getMetaInfo()['filePath']);
    }
}
