<?php

class CM_RenderAdapter_AbstractTest extends CMTest_TestCase {

    public function testGetTplPath() {
        $tplName = 'default.tpl';
        $view = $this->getMockBuilder('CM_View_Abstract')->getMockForAbstractClass();
        preg_match('/^([a-zA-Z]+)_([a-zA-Z]+)_(.+)$/', get_class($view), $matches);

        $tplPath = $matches[2] . DIRECTORY_SEPARATOR . $matches[3] . DIRECTORY_SEPARATOR . $tplName;
        $render = $this->getMockBuilder('CM_Render')->setMethods(array('getLayoutPath'))->getMockForAbstractClass();
        $render->expects($this->at(0))->method('getLayoutPath')->with($tplPath, null, false, false)->will($this->returnValue($tplPath));
        $renderAdapter = $this->getMockBuilder('CM_RenderAdapter_Abstract')->setConstructorArgs(array($render, $view))->getMockForAbstractClass();
        $_getTplPath = CMTest_TH::getProtectedMethod('CM_RenderAdapter_Abstract', '_getTplPath');
        $_getTplPath->invoke($renderAdapter, $tplName, true);
        $render->expects($this->at(0))->method('getLayoutPath')->with($tplPath, 'Mock', false, false)->will($this->returnValue($tplPath));
        $_getTplPath->invoke($renderAdapter, $tplName, false);

    }

}
