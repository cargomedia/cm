<?php

class CM_FormField_DateTimeIntervalTest extends CMTest_TestCase {
    
    public function testValidate() {
        $formField = new CM_FormField_DateTimeInterval();

        $environment = new CM_Frontend_Environment(null, null, null, new DateTimeZone('Asia/Tokyo'));
        $value = $formField->validate($environment, ['year' => 2016, 'month' => 05, 'day' => 20, 'start' => '3:20', 'end' => '16.31']);
        $expectedStart = new DateTime('2016-05-20 03:20:00', new DateTimeZone('Asia/Tokyo'));
        $expectedEnd = new DateTime('2016-05-20 16:31:00', new DateTimeZone('Asia/Tokyo'));
        $this->assertEquals([$expectedStart, $expectedEnd], $value);

        $environment = new CM_Frontend_Environment();
        $value = $formField->validate($environment, ['year' => 2016, 'month' => 05, 'day' => 20, 'start' => '23', 'end' => '1']);
        $expectedStart = new DateTime('2016-05-20 23:00:00');
        $expectedEnd = new DateTime('2016-05-21 01:00:00');
        $this->assertEquals([$expectedStart, $expectedEnd], $value);
    }
}
