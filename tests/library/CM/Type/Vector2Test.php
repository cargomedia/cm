<?php

class CM_Type_Vector2Test extends CMTest_TestCase {

    public function testConstruction() {
        $vector2 = new CM_Type_Vector2(2, 3.4);
        $this->assertInstanceOf('CM_Type_Vector2', $vector2);
        $this->assertSame(2.0, $vector2->getX());
        $this->assertSame(3.4, $vector2->getY());

        $exception = $this->catchException(function () {
            new CM_Type_Vector2('foo', 2);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Non numeric value `foo`', $exception->getMessage());

        $vector2 = CM_Type_Vector2::fromArray(['1.2', '3.4']);
        $this->assertInstanceOf('CM_Type_Vector2', $vector2);
        $this->assertSame(1.2, $vector2->getX());
        $this->assertSame(3.4, $vector2->getY());

        $exception = $this->catchException(function () {
            CM_Type_Vector2::fromArray(['1.2', '3.4', '5.6']);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Invalid source array size', $exception->getMessage());
    }

    public function testToArray() {
        $vector2 = new CM_Type_Vector2(2, 3.4);
        $this->assertSame([2.0, 3.4], $vector2->toArray());
    }
}
