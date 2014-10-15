<?php

class CM_Tools_Generator_CliTest extends PHPUnit_Framework_TestCase {

    /** @var CM_Tools_Generator_Cli */
    private $_generatorCli;

    /** @var string */
    private $_dirRoot;

    public function setUp() {
        $filesystemTmp = CM_Service_Manager::getInstance()->getFilesystems()->getTmp();
        $this->_dirRoot = $filesystemTmp->getAdapter()->getPathPrefix() . '/foo-app/';

        $composerFile = new \Composer\Json\JsonFile($this->_dirRoot . 'composer.json');
        $composerFile->write(array(
            'name'    => 'foo/bar',
            'require' => array('cargomedia/cm' => '*'),
        ));

        $filesystemTmp->ensureDirectory('foo-app/vendor/composer/');
        $installedFile = new \Composer\Json\JsonFile($this->_dirRoot . 'vendor/composer/installed.json');
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
        $frameworkLocal = DIR_ROOT . CM_Bootloader::getInstance()->getModulePath('CM');
        $frameworkTest = $this->_dirRoot . 'vendor/cargomedia/cm';
        $filesystemTmp->ensureDirectory('foo-app/vendor/cargomedia');
        symlink($frameworkLocal, $frameworkTest);

        $installation = new CM_Tools_AppInstallation($this->_dirRoot);
        $this->_generatorCli = $this->getMockBuilder('CM_Tools_Generator_Cli')->setMethods(array('_getAppInstallation'))->getMock();
        $this->_generatorCli->expects($this->any())->method('_getAppInstallation')->will($this->returnValue($installation));
    }

    public function tearDown() {
        CMTest_TH::clearFilesystem();
    }

    public function testCreateModule() {
        $app = new CM_Tools_AppInstallation($this->_dirRoot);
        $this->assertFalse($app->moduleExists('Foo'));
        $this->assertFalse($app->moduleExists('Bar'));

        $this->_generatorCli->createModule('Foo');
        $this->_generatorCli->createModule('Bar', false, 'custom/');

        $app = new CM_Tools_AppInstallation($this->_dirRoot);
        $this->assertTrue($app->moduleExists('Foo'));
        $this->assertSame('modules/Foo/', $app->getModulePath('Foo'));
        $this->assertFileExists($this->_dirRoot . $app->getModulePath('Foo'));

        $this->assertTrue($app->moduleExists('Bar'));
        $this->assertSame('custom/', $app->getModulePath('Bar'));
        $this->assertFileExists($this->_dirRoot . $app->getModulePath('Bar'));
    }

    public function testCreateModuleSingle() {
        $this->_generatorCli->createModule('Bar', true);

        $app = new CM_Tools_AppInstallation($this->_dirRoot);
        $this->assertTrue($app->moduleExists('Bar'));
        $this->assertSame('', $app->getModulePath('Bar'));
        $this->assertFileExists($this->_dirRoot . $app->getModulePath('Bar'));
    }

    /**
     * @expectedException CM_Cli_Exception_Internal
     * @expectedExceptionMessage Module `foo` must exist!
     */
    public function testCreateNamespaceNoModule() {
        $method = new ReflectionMethod($this->_generatorCli, '_createNamespace');
        $method->setAccessible(true);
        $method->invoke($this->_generatorCli, 'foo', 'bar');
    }

    public function testCreateClass() {
        $cli = $this->_generatorCli;
        $cli->createModule('Foo');
        $cli->createClass('Foo_Bar');
        $this->assertTrue(class_exists('Foo_Bar'));
        $this->assertSame('CM_Class_Abstract', get_parent_class('Foo_Bar'));
        $this->assertFileExists($this->_dirRoot . 'modules/Foo/library/Foo/Bar.php');
    }

    public function testCreateView() {
        $cli = $this->_generatorCli;
        $cli->createModule('Foo');
        $cli->createView('Foo_Component_Foo_Bar');
        $this->assertTrue(class_exists('Foo_Component_Foo_Bar'));
        $this->assertSame('CM_Component_Abstract', get_parent_class('Foo_Component_Foo_Bar'));
        $this->assertFileExists($this->_dirRoot . 'modules/Foo/library/Foo/Component/Foo/Bar.php');
        $this->assertFileExists($this->_dirRoot . 'modules/Foo/library/Foo/Component/Foo/Bar.js');
        $this->assertFileExists($this->_dirRoot . 'modules/Foo/layout/default/Component/Foo_Bar/default.tpl');
        $this->assertFileExists($this->_dirRoot . 'modules/Foo/layout/default/Component/Foo_Bar/default.less');
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage is not a subclass of `CM_View_Abstract`
     */
    public function testCreateViewInvalid() {
        $cli = $this->_generatorCli;
        $cli->createModule('Foo');
        $cli->createView('Foo_Foo_Bar');
    }

    public function testCreateSite() {
        $cli = $this->_generatorCli;
        $cli->createModule('Foo');
        $cli->createSite('Foo_Site_Foo', 'Foo', 'foo.com');
        $this->assertTrue(class_exists('Foo_Site_Foo'));
        $this->assertSame('CM_Site_Abstract', get_parent_class('Foo_Site_Foo'));
        $this->assertFileExists($this->_dirRoot . 'modules/Foo/library/Foo/Site/Foo.php');
        $this->assertFileExists($this->_dirRoot . 'modules/Foo/resources/config/default.php');
        $this->assertFileExists($this->_dirRoot . 'resources/config/local.php');

        /** @var Closure $configExtendDefault */
        $configExtendDefault = require $this->_dirRoot . 'modules/Foo/resources/config/default.php';
        $this->assertInstanceOf('Closure', $configExtendDefault);
        /** @var Closure $configExtendLocal */
        $configExtendLocal = require $this->_dirRoot . 'resources/config/local.php';
        $this->assertInstanceOf('Closure', $configExtendLocal);

        $configNode = new CM_Config_Node();
        $configExtendDefault($configNode);
        $configExtendLocal($configNode);

        $expectedConfig = [
            'name'         => 'Foo',
            'emailAddress' => 'hello@foo.com',
            'url'          => 'http://www.foo.com',
            'urlCdn'       => 'http://origin-www.foo.com',
        ];
        $this->assertSame($expectedConfig, (array) $configNode->Foo_Site_Foo->export());
    }
}
