<?php

class CM_FormField_Set_SelectTest extends CMTest_TestCase {

    public function testRender() {
        $field = new CM_FormField_Set_Select(['name' => 'test', 'values' => ['a', 'b']]);
        $doc = $this->_renderFormField($field);
        $this->assertSame(1, $doc->find("select")->count());
        $this->assertSame(2, $doc->find("select option")->count());
        $this->assertSame('a', $doc->find("select[@name='test'] option[@value='a'][@selected]")->getText());
        $this->assertSame('b', $doc->find("select[@name='test'] option[@value='b'][not(@selected)]")->getText());
    }

    public function testRenderPlaceholder() {
        $field = new CM_FormField_Set_Select(['name' => 'test', 'placeholder' => true, 'values' => ['a', 'b']]);
        $doc = $this->_renderFormField($field);
        var_dump($doc->getHtml());
        $this->assertSame(1, $doc->find("select")->count());
        $this->assertSame(3, $doc->find("select option")->count());
        $this->assertSame(' -Select- ', $doc->find("select[@name='test'] option[@value=''][@selected]")->getText());
        $this->assertSame('a', $doc->find("select[@name='test'] option[@value='a'][not(@selected)]")->getText());
        $this->assertSame('b', $doc->find("select[@name='test'] option[@value='b'][not(@selected)]")->getText());
    }
}
