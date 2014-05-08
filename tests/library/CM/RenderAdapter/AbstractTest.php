<?php

class CM_RenderAdapter_AbstractTest extends CMTest_TestCase {

    public function testGetTplPath() {
        $tplName = 'default.tpl';
        $view = $this->getMockBuilder('CM_View_Abstract')->setMockClassName('CM_View_Mock')->getMockForAbstractClass();
        $tplPathAbstract = 'View' . DIRECTORY_SEPARATOR . 'Abstract' . DIRECTORY_SEPARATOR . $tplName;
        $tplPathMock = 'View' . DIRECTORY_SEPARATOR . 'Mock' . DIRECTORY_SEPARATOR . $tplName;

        $render = $this->getMockBuilder('CM_Render')->setMethods(array('getLayoutPath'))->getMockForAbstractClass();
        $renderAdapter = $this->getMockBuilder('CM_RenderAdapter_Abstract')->setConstructorArgs(array($render, $view))->getMockForAbstractClass();
        $_getTplPath = CMTest_TH::getProtectedMethod('CM_RenderAdapter_Abstract', '_getTplPath');

        $render->expects($this->at(0))->method('getLayoutPath')->with($tplPathMock, null, false, false)->will($this->returnValue($tplPathMock));
        $this->assertSame($tplPathMock, $_getTplPath->invoke($renderAdapter, $tplName, true));

        $render->expects($this->at(0))->method('getLayoutPath')->with($tplPathMock, 'CM', false, false)->will($this->returnValue($tplPathMock));
        $this->assertSame($tplPathMock, $_getTplPath->invoke($renderAdapter, $tplName, false));

        $render->expects($this->at(0))->method('getLayoutPath')->with($tplPathMock, 'CM', false, false)->will($this->returnValue(null));
        $render->expects($this->at(1))->method('getLayoutPath')->with($tplPathAbstract, 'CM', false, false)->will($this->returnValue($tplPathMock));
        $this->assertSame($tplPathMock, $_getTplPath->invoke($renderAdapter, $tplName, false));

        $render->expects($this->at(0))->method('getLayoutPath')->with($tplPathMock, 'CM', false, false)->will($this->returnValue(null));
        $render->expects($this->at(1))->method('getLayoutPath')->with($tplPathAbstract, 'CM', false, false)->will($this->returnValue(null));
        try {
            $_getTplPath->invoke($renderAdapter, $tplName, false);
            $this->fail('Returned path of nonexistent template file.');
        } catch (CM_Exception $ex) {
            $this->assertContains('Cannot find template `' . $tplName . '` for `CM_View_Mock`.', $ex->getMessage());
        }
    }
}
