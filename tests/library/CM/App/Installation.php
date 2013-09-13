<?php

class CM_App_InstallationTest extends CMTest_TestCase {

	public function testGetPackages() {
		$package1 = $this->getMockBuilder('\Composer\Package\CompletePackage')
				->setMethods(array('getName', 'getRequires'))->disableOriginalConstructor()->getMock();
		$package1->expects($this->any())->method('getName')->will($this->returnValue('cargomedia/cm'));
		$package1->expects($this->any())->method('getRequires')->will($this->returnValue(array()));
		/** @var \Composer\Package\CompletePackage $package1 */

		$package2 = $this->getMockBuilder('\Composer\Package\CompletePackage')
				->setMethods(array('getName', 'getRequires'))->disableOriginalConstructor()->getMock();
		$package2->expects($this->any())->method('getName')->will($this->returnValue('foo'));
		$package2->expects($this->any())->method('getRequires')->will($this->returnValue(array('owl' => '*')));
		/** @var \Composer\Package\CompletePackage $package2 */

		$package3 = $this->getMockBuilder('\Composer\Package\CompletePackage')
				->setMethods(array('getName', 'getRequires'))->disableOriginalConstructor()->getMock();
		$package3->expects($this->any())->method('getName')->will($this->returnValue('bar'));
		$package3->expects($this->any())->method('getRequires')->will($this->returnValue(array()));
		/** @var \Composer\Package\CompletePackage $package3 */

		$package4 = $this->getMockBuilder('\Composer\Package\CompletePackage')
				->setMethods(array('getName', 'getRequires'))->disableOriginalConstructor()->getMock();
		$package4->expects($this->any())->method('getName')->will($this->returnValue('owl'));
		$package4->expects($this->any())->method('getRequires')->will($this->returnValue(array('cargomedia/cm' => '*')));
		/** @var \Composer\Package\CompletePackage $package4 */

		$composerPackages = array($package1, $package2, $package3, $package4);
		$installation = $this->getMockBuilder('CM_App_Installation')->setMethods(array('_getComposerPackages', '_getComposerVendorDir'))->getMock();
		$installation->expects($this->any())->method('_getComposerPackages')->will($this->returnValue($composerPackages));
		$installation->expects($this->any())->method('_getComposerVendorDir')->will($this->returnValue('vendor'));
		/** @var CM_App_Installation $installation */

		$packages = $installation->getPackages();
		$this->assertCount(3, $packages);
		$this->assertContainsOnlyInstancesOf('CM_App_Package', $packages);
		$this->assertSame($package1->getName(), $packages[0]->getName());
		$this->assertSame($package4->getName(), $packages[1]->getName());
		$this->assertSame($package2->getName(), $packages[2]->getName());
	}

	public function testGetNamespacePaths() {
		$module1 = new CM_App_Module('module1', 'mod/ule1/');
		$module2 = new CM_App_Module('module2', 'mod/ule2/');
		$package1 = $this->getMockBuilder('CM_App_Package')
				->setMethods(array('getModules'))->disableOriginalConstructor()->getMock();
		$package1->expects($this->any())->method('getModules')->will($this->returnValue(array($module1, $module2)));
		/** @var CM_App_Package $package1 */

		$module3 = new CM_App_Module('module3', 'mod/ule3/');
		$module4 = new CM_App_Module('module4', 'mod/ule4/');
		$package2 = $this->getMockBuilder('CM_App_Package')
				->setMethods(array('getModules'))->disableOriginalConstructor()->getMock();
		$package2->expects($this->any())->method('getModules')->will($this->returnValue(array($module3, $module4)));
		/** @var CM_App_Package $package2 */

		$installation = $this->getMockBuilder('CM_App_Installation')->setMethods(array('getPackages', '_getPackagePath'))->getMock();
		$installation->expects($this->any())->method('getPackages')->will($this->returnValue(array($package1, $package2)));
		$installation->expects($this->at(1))->method('_getPackagePath')->with($package1)->will($this->returnValue('pa/ckage1/'));
		$installation->expects($this->at(2))->method('_getPackagePath')->with($package1)->will($this->returnValue('pa/ckage1/'));
		$installation->expects($this->at(3))->method('_getPackagePath')->with($package2)->will($this->returnValue('pa/ckage2/'));
		$installation->expects($this->at(4))->method('_getPackagePath')->with($package2)->will($this->returnValue('pa/ckage2/'));
		/** @var CM_App_Installation $installation */

		$this->assertSame(array(
			'module1' => 'pa/ckage1/mod/ule1/',
			'module2' => 'pa/ckage1/mod/ule2/',
			'module3' => 'pa/ckage2/mod/ule3/',
			'module4' => 'pa/ckage2/mod/ule4/',
		), $installation->getNamespacePaths());
	}
}
