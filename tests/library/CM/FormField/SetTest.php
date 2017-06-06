<?php

class CM_FormField_SetTest extends CMTest_TestCase {

    public function testConstructor() {
        $field = new CM_FormField_Set(['name' => 'foo']);
        $this->assertInstanceOf('CM_FormField_Set', $field);
    }

    public function testSetGetValue() {
        $field = new CM_FormField_Set(['name' => 'foo']);

        $values = array(32 => 'apples');
        $field->setValue($values);
        $this->assertSame($values, $field->getValue());

        $value = 'bar';
        $field->setValue($value);
        $this->assertSame($value, $field->getValue());
    }

    public function testGetValues() {
        $data = array(32 => 'apples', 64 => 'oranges', 128 => 'bananas');
        $field = new CM_FormField_Set(['name' => 'foo', 'values' => $data, 'labelsInValues' => true]);
        $this->assertSame(array_keys($data), $field->getValues());
    }

    public function testValidate() {
        $data = array(32 => 'apples', 64 => 'oranges', 128 => 'bananas');
        $field = new CM_FormField_Set(['name' => 'foo', 'values' => $data, 'labelsInValues' => true]);

        $environment = new CM_Frontend_Environment();
        $userInputGood = array(32, 64, 128);
        $validationResult = $field->validate($environment, $userInputGood);
        $this->assertSame($userInputGood, $validationResult);

        $userInputTainted = array(32, 23, 132);
        $validationResult = $field->validate($environment, $userInputTainted);
        $this->assertSame(array(32), $validationResult);
    }

    public function testRender() {
        $name = 'foo';
        $data = array(32 => 'apples', 64 => 'oranges', 128 => 'bananas');
        $field = new CM_FormField_Set(['name' => $name, 'values' => $data, 'labelsInValues' => true]);
        $values = array(64, 128);
        $field->setValue($values);

        $doc = $this->_renderFormField($field);

        $this->assertSame(count($data), $doc->find('label')->count());
        $this->assertSame(count($data), $doc->find('input')->count());
        foreach ($data as $value => $label) {
            $this->assertTrue($doc->has('li.' . $name . '-value-' . $value));
            $this->assertTrue($doc->has('label[for]'));
            $this->assertTrue($doc->has('input[value="' . $value . '"]'));
            if (in_array($value, $values)) {
                $this->assertSame('checked', $doc->find('input[value="' . $value . '"]')->getAttribute('checked'));
            }
        }
    }
}
