<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.select.php';

class smarty_function_selectTest extends CMTest_TestCase {

    public function testNothingSelected() {
        $htmlObject = $this->_createSelect(array(
            'name'       => 'foo',
            'optionList' => array(
                0 => 'foo',
                1 => 'bar',
            ),
        ));

        $this->assertSame(2, $htmlObject->find('option')->count());
        $this->assertSame(1, $htmlObject->find('option[selected]')->count());
        $this->assertSame(1, $htmlObject->find('select')->count());
        $this->assertSame('foo', $htmlObject->find('option[selected]')->getText());
        $this->assertSame('foo', $htmlObject->find('.label')->getText());
    }

    public function testSelectedValue() {
        $htmlObject = $this->_createSelect(array(
            'name'          => 'foo',
            'optionList'    => array(
                0 => 'foo',
                1 => 'bar',
            ),
            'selectedValue' => 1,
        ));

        $this->assertSame(2, $htmlObject->find('option')->count());
        $this->assertSame(1, $htmlObject->find('option[selected]')->count());
        $this->assertSame(1, $htmlObject->find('select')->count());
        $this->assertSame('bar', $htmlObject->find('option[selected]')->getText());
        $this->assertSame('bar', $htmlObject->find('.label')->getText());
    }

    public function testPlaceholder() {
        $htmlObject = $this->_createSelect(array(
            'name'        => 'foo',
            'optionList'  => array(
                0 => 'foo',
                1 => 'bar',
            ),
            'placeholder' => 'please choose',
        ));

        $this->assertSame(3, $htmlObject->find('option')->count());
        $this->assertSame(1, $htmlObject->find('option[selected]')->count());
        $this->assertSame(1, $htmlObject->find('select')->count());
        $this->assertSame('please choose', $htmlObject->find('option[selected]')->getText());
        $this->assertSame('please choose', $htmlObject->find('.label')->getText());
    }

    public function testPlaceholder_true() {
        $htmlObject = $this->_createSelect(array(
            'name'        => 'foo',
            'optionList'  => array(
                0 => 'foo',
                1 => 'bar',
            ),
            'placeholder' => true,
        ));

        $this->assertSame(3, $htmlObject->find('option')->count());
        $this->assertSame(1, $htmlObject->find('option[selected]')->count());
        $this->assertSame(1, $htmlObject->find('select')->count());
        $this->assertSame(' -Select- ', $htmlObject->find('option[selected]')->getText());
        $this->assertSame(' -Select- ', $htmlObject->find('.label')->getText());
    }

    public function testPlaceholder_false() {
        $htmlObject = $this->_createSelect(array(
            'name'        => 'foo',
            'optionList'  => array(
                0 => 'foo',
                1 => 'bar',
            ),
            'placeholder' => false,
        ));

        $this->assertSame(2, $htmlObject->find('option')->count());
        $this->assertSame(1, $htmlObject->find('option[selected]')->count());
        $this->assertSame(1, $htmlObject->find('select')->count());
        $this->assertSame('foo', $htmlObject->find('option[selected]')->getText());
        $this->assertSame('foo', $htmlObject->find('.label')->getText());
    }

    public function testPlaceholder_empty() {
        $htmlObject = $this->_createSelect(array(
            'name'        => 'foo',
            'optionList'  => array(
                0 => 'foo',
                1 => 'bar',
            ),
            'placeholder' => '',
        ));

        $this->assertSame(3, $htmlObject->find('option')->count());
        $this->assertSame(1, $htmlObject->find('option[selected]')->count());
        $this->assertSame(1, $htmlObject->find('select')->count());
        $this->assertSame('', $htmlObject->find('option[selected]')->getText());
        $this->assertSame('', $htmlObject->find('.label')->getText());
    }

    public function testLabelPrefix() {
        $htmlObject = $this->_createSelect(array(
            'name'        => 'foo',
            'optionList'  => array(
                0 => 'foo',
                1 => 'bar',
            ),
            'labelPrefix' => 'foobar',
        ));

        $this->assertEquals('foobar', $htmlObject->find('.labelPrefix')->getText());
    }

    /**
     * @param array $params
     * @return CM_Dom_NodeList
     */
    private function _createSelect(array $params) {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();
        $template = $smarty->createTemplate('string:');
        $template->assignGlobal('render', $render);
        $html = smarty_function_select($params, $template);
        return new CM_Dom_NodeList($html);
    }
}
