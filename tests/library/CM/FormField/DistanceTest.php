<?php

class CM_FormField_DistanceTest extends CMTest_TestCase {

    public function testParseUserInput() {
        $expected = 12345;
        $formField = new CM_FormField_Distance();
        $parsedUserInput = $formField->parseUserInput($expected);
        $formField->setValue($parsedUserInput);
        $actual = $formField->getValue();
        $this->assertSame($expected, $actual);
    }
}
