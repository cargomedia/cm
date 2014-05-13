<?php

class CM_FormField_BooleanTest extends CMTest_TestCase {

    public function testConstructor() {
        $field = new CM_FormField_BooleanTest();
        $this->assertInstanceOf('CM_FormField_BooleanTest', $field);
    }

    public function testRender() {
        // @todo add some tests
    }
}
