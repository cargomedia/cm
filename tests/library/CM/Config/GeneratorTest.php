<?php

class CM_Config_GeneratorTest extends CMTest_TestCase {

    public function testGetConfigActionVerbs() {
        if (CM_Config::get()->CM_Action_Abstract->verbsMaxValue) {
            unset(CM_Config::get()->CM_Action_Abstract->verbsMaxValue);
        }
        if (CM_Config::get()->CM_Action_Abstract->verbs) {
            unset(CM_Config::get()->CM_Action_Abstract->verbs);
        }
        $actionVerbs = array(
            array(
                'name'      => 'CREATE',
                'value'     => 'Create',
                'className' => 'CM_Action_Abstract',
            ),
            array(
                'name'      => 'UPDATE',
                'value'     => 'Update',
                'className' => 'CM_Action_Abstract',
            )
        );
        $expected = new CM_Config_Node();
        $expected->CM_Action_Abstract->verbs = ['CM_Action_Abstract::CREATE' => 1, 'CM_Action_Abstract::UPDATE' => 2];
        $expected->CM_Action_Abstract->verbsMaxValue = 2;

        $generator = $this->getMockBuilder('CM_Config_Generator')->setMethods(array('getActionVerbs'))->getMock();
        $generator->expects($this->any())->method('getActionVerbs')->will($this->returnValue($actionVerbs));

        /** @var CM_Config_Generator $generator */
        $this->assertEquals($expected, $generator->getConfigActionVerbs());
    }

    public function testGetConfigActionVerbsConfig() {
        CM_Config::get()->CM_Action_Abstract->verbsMaxValue = 5;
        CM_Config::get()->CM_Action_Abstract->verbs = array();
        $actionVerbs = array(
            array(
                'name'      => 'CREATE',
                'value'     => 'Create',
                'className' => 'CM_Action_Abstract',
            )
        );
        $expected = new CM_Config_Node();
        $expected->CM_Action_Abstract->verbs = ['CM_Action_Abstract::CREATE' => 6];
        $expected->CM_Action_Abstract->verbsMaxValue = 6;

        $generator = $this->getMockBuilder('CM_Config_Generator')->setMethods(array('getActionVerbs'))->getMock();
        $generator->expects($this->any())->method('getActionVerbs')->will($this->returnValue($actionVerbs));

        /** @var CM_Config_Generator $generator */
        $this->assertEquals($expected, $generator->getConfigActionVerbs());
    }
}
