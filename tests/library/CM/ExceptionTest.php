<?php

class CM_ExceptionTest extends CMTest_TestCase {

    public function testConstructor() {
        $user = CMTest_TH::createUser();
        $exception = new CM_Exception('foo',
            array('meta' => 'foo', 'user' => $user),
            array('messagePublic' => 'foo {$bar}', 'messagePublicVariables' => array('bar' => 'foo'), 'severity' => CM_Exception::ERROR));
        $render = new CM_Render();

        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame('foo foo', $exception->getMessagePublic($render));
        $this->assertSame(CM_Exception::ERROR, $exception->getSeverity());
        $this->assertSame(array('meta' => '\'foo\'', 'user' => 'CM_Model_User(' . $user->getId() . ')'), $exception->getMetaInfo());
    }

    public function testGetSetSeverity() {
        $exception = new CM_Exception();
        $this->assertSame(CM_Exception::ERROR, $exception->getSeverity());

        $exception->setSeverity(CM_Exception::WARN);
        $this->assertSame(CM_Exception::WARN, $exception->getSeverity());
    }

    public function testGetSetSeverityInvalid() {
        $exception = new CM_Exception();

        try {
            $exception->setSeverity(9999);
            $this->fail('Could set invalid severity');
        } catch (CM_Exception_Invalid $e) {
            $this->assertSame('Invalid severity `9999`', $e->getMessage());
        }

        try {
            $exception->setSeverity('1');
            $this->fail('Could set invalid severity');
        } catch (CM_Exception_Invalid $e) {
            $this->assertSame('Invalid severity `1`', $e->getMessage());
        }
    }
}
