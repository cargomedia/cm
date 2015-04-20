<?php

class CM_Class_TypedTraitTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetTypeStatic() {
        CM_Config::get()->CM_ClassMockType = new stdClass();
        CM_Config::get()->CM_ClassMockType->type = 73;
        $this->assertSame(73, CM_ClassMockType::getTypeStatic());
    }

    /**
     * @expectedException CM_Class_Exception_TypeNotConfiguredException
     * @expectedMessageException Class `CM_ClassMockType` has no type configured.
     */
    public function testGetTypeStaticTypeNotConfigured() {
        CM_Config::get()->CM_ClassMockType = new stdClass();
        CM_Config::get()->CM_ClassMockType->foo = 'bar';
        $this->assertSame(73, CM_ClassMockType::getTypeStatic());
    }

    public function testGetClassName() {
        CM_Config::get()->CM_ClassMockType = new stdClass();
        CM_Config::get()->CM_ClassMockType->types = [74 => 'CM_ClassMockTypeChild'];

        $this->assertSame('CM_ClassMockType', CMTest_TH::callProtectedMethod('CM_ClassMockType', '_getClassName'));

        CM_Config::get()->CM_ClassMockType->class = 'CM_ClassNameOverwritten';
        $this->assertSame('CM_ClassNameOverwritten', CMTest_TH::callProtectedMethod('CM_ClassMockType', '_getClassName'));

        $this->assertSame('CM_ClassMockTypeChild', CMTest_TH::callProtectedMethod('CM_ClassMockType', '_getClassName', [74]));
        try {
            CMTest_TH::callProtectedMethod('CM_ClassMockType', '_getClassName', [75]);
        } catch (CM_Class_Exception_TypeNotConfiguredException $ex) {
            $this->assertSame('Type `75` not configured for class `CM_ClassMockType`.', $ex->getMessage());
        }
        unset(CM_Config::get()->CM_ClassMockType->types);
        try {
            CMTest_TH::callProtectedMethod('CM_ClassMockType', '_getClassName', [74]);
        } catch (CM_Class_Exception_TypeNotConfiguredException $ex) {
            $this->assertSame('Type `74` not configured for class `CM_ClassMockType`.', $ex->getMessage());
        }
    }
}



class CM_ClassMockType implements CM_Class_TypedInterface{

    use CM_Class_TypedTrait;
}
