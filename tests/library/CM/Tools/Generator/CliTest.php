<?php

class CM_Tools_Generator_CliTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreateModule() {
        $app = $this->_mockAppInstallation();
        $this->assertFalse($app->moduleExists('Foo'));
        $this->assertFalse($app->moduleExists('Bar'));

        $cli = $this->_mockGeneratorCli($app);
        $cli->createModule('Foo');
        $cli->createModule('Bar', false, 'custom/');

        $this->assertTrue($app->moduleExists('Foo'));
        $this->assertSame('modules/Foo/', $app->getModulePath('Foo'));
        $this->assertTrue($app->getFilesystem()->exists($app->getModulePath('Foo')));

        $this->assertTrue($app->moduleExists('Bar'));
        $this->assertSame('custom/', $app->getModulePath('Bar'));
        $this->assertTrue($app->getFilesystem()->exists($app->getModulePath('Bar')));
    }

    public function testCreateModuleSingle() {
        $app = $this->_mockAppInstallation();
        $this->_mockGeneratorCli($app)->createModule('Bar', true);

        $this->assertTrue($app->moduleExists('Bar'));
        $this->assertSame('', $app->getModulePath('Bar'));
        $this->assertTrue($app->getFilesystem()->exists($app->getModulePath('Bar')));
    }

    /**
     * @expectedException CM_Cli_Exception_Internal
     * @expectedExceptionMessage Module `foo` must exist!
     */
    public function testCreateNamespaceNoModule() {
        $app = $this->_mockAppInstallation();
        $cli = $this->_mockGeneratorCli($app);
        CMTest_TH::callProtectedMethod($cli, '_createNamespace', ['foo', 'bar']);
    }

    public function testCreateClass() {
        $app = $this->_mockAppInstallation();
        $cli = $this->_mockGeneratorCli($app);
        $cli->createModule('Foo');
        $cli->createClass('Foo_Bar');
        $this->assertTrue(class_exists('Foo_Bar'));
        $this->assertSame('CM_Class_Abstract', get_parent_class('Foo_Bar'));
        $this->assertTrue($app->getFilesystem()->exists('modules/Foo/library/Foo/Bar.php'));
    }

    public function testCreateView() {
        $app = $this->_mockAppInstallation();
        $cli = $this->_mockGeneratorCli($app);
        $this->assertSame(['CM'], $app->getModuleNames());
        $cli->createModule('Foo');
        $this->assertSame(['CM', 'Foo'], $app->getModuleNames());
        $cli->createView('Foo_Component_Foo_Bar');
        $this->assertTrue(class_exists('Foo_Component_Foo_Bar'));
        $this->assertSame('CM_Component_Abstract', get_parent_class('Foo_Component_Foo_Bar'));
        $this->assertTrue($app->getFilesystem()->exists('vendor/autoload.php'));
        $this->assertTrue($app->getFilesystem()->exists('modules/Foo/library/Foo/Component/Foo/Bar.php'));
        $this->assertTrue($app->getFilesystem()->exists('modules/Foo/library/Foo/Component/Foo/Bar.js'));
        $this->assertTrue($app->getFilesystem()->exists('modules/Foo/layout/default/Component/Foo_Bar/default.tpl'));
        $this->assertTrue($app->getFilesystem()->exists('modules/Foo/layout/default/Component/Foo_Bar/default.less'));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage is not a subclass of `CM_View_Abstract`
     */
    public function testCreateViewInvalid() {
        $app = $this->_mockAppInstallation();
        $cli = $this->_mockGeneratorCli($app);
        $cli->createModule('Foo');
        $cli->createView('Foo_Foo_Bar');
    }

    /**
     * @throws CM_Exception_Invalid
     * @throws Exception
     * @throws \Mocka\Exception
     * @return CM_Tools_AppInstallation|\Mocka\AbstractClassTrait
     */
    private function _mockAppInstallation() {
        $appName = uniqid('foo-app-');
        $filesystemTmp = CM_Service_Manager::getInstance()->getFilesystems()->getTmp();
        $dirRoot = $filesystemTmp->getAdapter()->getPathPrefix() . '/' . $appName . '/';

        $frameworkLocal = DIR_ROOT . CM_Bootloader::getInstance()->getModulePath('CM');
        $frameworkTest = $dirRoot . 'vendor/cargomedia/cm';
        $filesystemTmp->ensureDirectory("{$appName}/vendor/cargomedia");
        if (false === symlink($frameworkLocal, $frameworkTest)) {
            throw new CM_Exception_Invalid('Symlink failed to be created');
        }

        $composerFile = new \Composer\Json\JsonFile($dirRoot . 'composer.json');
        $composerFile->write(array(
            'name'    => 'foo/bar',
            'require' => array('cargomedia/cm' => '*'),
        ));

        $filesystemTmp->ensureDirectory("{$appName}/vendor/composer/");
        $installedFile = new \Composer\Json\JsonFile($dirRoot . 'vendor/composer/installed.json');
        $installedFile->write(
            [
                [
                    'name'     => 'cargomedia/cm',
                    'version'  => '1.3.10',
                    'type'     => 'library',
                    'extra'    => [
                        'cm-modules' => [
                            'CM' => [
                                'path' => ''
                            ]
                        ]
                    ],
                    'autoload' => [
                        'psr-0' => [
                            'CM_' => 'library/'
                        ]
                    ]
                ]
            ]
        );
        return $this->mockObject('CM_Tools_AppInstallation', [$dirRoot]);
    }

    /**
     * @param CM_Tools_AppInstallation $appInstallation
     * @return \Mocka\AbstractClassTrait|CM_Tools_Generator_Cli
     * @throws \Mocka\Exception
     */
    private function _mockGeneratorCli(CM_Tools_AppInstallation $appInstallation) {
        $cli = $this->mockObject('CM_Tools_Generator_Cli');
        $cli->mockMethod('_getAppInstallation')->set($appInstallation);
        return $cli;
    }

}
