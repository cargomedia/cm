<?php

class CM_RenderAdapter_AbstractTest extends CMTest_TestCase {

    public function testGetTplPath() {
        $tplName = 'default.tpl';
        $view = $this->getMockBuilder('CM_View_Abstract')->setMockClassName('CM_View_Mock')->getMockForAbstractClass();

        $tplPath = 'View' . DIRECTORY_SEPARATOR . 'Mock' . DIRECTORY_SEPARATOR . $tplName;
        $render = $this->getMockBuilder('CM_Render')->setMethods(array('getLayoutPath'))->getMockForAbstractClass();
        $renderAdapter = $this->getMockBuilder('CM_RenderAdapter_Abstract')->setConstructorArgs(array($render, $view))->getMockForAbstractClass();
        $_getTplPath = CMTest_TH::getProtectedMethod('CM_RenderAdapter_Abstract', '_getTplPath');

        $render->expects($this->at(0))->method('getLayoutPath')->with($tplPath, null, false, false)->will($this->returnValue($tplPath));
        $_getTplPath->invoke($renderAdapter, $tplName, true);

        $render->expects($this->at(0))->method('getLayoutPath')->with($tplPath, 'CM', false, false)->will($this->returnValue($tplPath));
        $_getTplPath->invoke($renderAdapter, $tplName, false);

        $render->expects($this->at(0))->method('getLayoutPath')->with($tplPath, 'CM', false, false)->will($this->returnValue(null));
        $tplPath = 'View' . DIRECTORY_SEPARATOR . 'Abstract' . DIRECTORY_SEPARATOR . $tplName;
        $render->expects($this->at(1))->method('getLayoutPath')->with($tplPath, 'CM', false, false)->will($this->returnValue($tplPath));
        $_getTplPath->invoke($renderAdapter, $tplName, false);
    }

}
