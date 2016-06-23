<?php

class CM_Type_Vector3Test extends CMTest_TestCase {

    public function testConstruction() {
        $vector3 = new CM_Type_Vector3(2, 3.4, 5);
        $this->assertInstanceOf('CM_Type_Vector3', $vector3);
        $this->assertSame(2.0, $vector3->getX());
        $this->assertSame(3.4, $vector3->getY());
        $this->assertSame(5.0, $vector3->getZ());

        $exception = $this->catchException(function () {
            new CM_Type_Vector3(2, 3, 'bar');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Non numeric value `bar`', $exception->getMessage());

        $vector3 = CM_Type_Vector3::fromArray(['1.2', '3.4', '5.6']);
        $this->assertInstanceOf('CM_Type_Vector3', $vector3);
        $this->assertSame(1.2, $vector3->getX());
        $this->assertSame(3.4, $vector3->getY());
        $this->assertSame(5.6, $vector3->getZ());

        $exception = $this->catchException(function () {
            CM_Type_Vector3::fromArray(['1.2', '3.4']);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Invalid source array size', $exception->getMessage());
    }

    public function testToArray() {
        $vector3 = new CM_Type_Vector3(2, 3.4, 5);
        $this->assertSame([2.0, 3.4, 5.0], $vector3->toArray());
    }
}
