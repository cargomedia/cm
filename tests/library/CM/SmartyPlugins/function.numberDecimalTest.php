<?php

class smarty_function_numberDecimalTest extends CMTest_TestCase {

    public function testNumeric() {
        $render = new CM_Frontend_Render();
        $this->assertSame("500,000.55", $render->parseTemplateContent('{numberDecimal value=500000.55}'));
        $this->assertSame("1", $render->parseTemplateContent('{numberDecimal value=1.00}'));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Invalid non-numeric value
     */
    public function testNonNumeric() {
        $render = new CM_Frontend_Render();
        $render->parseTemplateContent('{numberDecimal value="foo"}');
    }

}
