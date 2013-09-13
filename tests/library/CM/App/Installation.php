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
}
