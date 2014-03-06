<?php

require_once CM_Util::getNamespacePath('CM') . 'library/CM/SmartyPlugins/function.select.php';

class smarty_function_selectTest extends CMTest_TestCase {

  public function testNothingSelected() {
    $htmlObject = $this->_createSelect(array(
      'name'       => 'foo',
      'optionList' => array(
        0 => 'foo',
        1 => 'bar',
      ),
    ));

    $this->assertSame(2, $htmlObject->getCount('option'));
    $this->assertSame(1, $htmlObject->getCount('option[selected]'));
    $this->assertSame(1, $htmlObject->getCount('select'));
    $this->assertSame('foo', $htmlObject->getText('option[selected]'));
    $this->assertSame('foo', $htmlObject->getText('.label'));
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

    $this->assertSame(2, $htmlObject->getCount('option'));
    $this->assertSame(1, $htmlObject->getCount('option[selected]'));
    $this->assertSame(1, $htmlObject->getCount('select'));
    $this->assertSame('bar', $htmlObject->getText('option[selected]'));
    $this->assertSame('bar', $htmlObject->getText('.label'));
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

    $this->assertSame(3, $htmlObject->getCount('option'));
    $this->assertSame(1, $htmlObject->getCount('option[selected]'));
    $this->assertSame(1, $htmlObject->getCount('select'));
    $this->assertSame('please choose', $htmlObject->getText('option[selected]'));
    $this->assertSame('please choose', $htmlObject->getText('.label'));
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

    $this->assertSame(3, $htmlObject->getCount('option'));
    $this->assertSame(1, $htmlObject->getCount('option[selected]'));
    $this->assertSame(1, $htmlObject->getCount('select'));
    $this->assertSame(' -Select- ', $htmlObject->getText('option[selected]'));
    $this->assertSame(' -Select- ', $htmlObject->getText('.label'));
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

    $this->assertSame(2, $htmlObject->getCount('option'));
    $this->assertSame(1, $htmlObject->getCount('option[selected]'));
    $this->assertSame(1, $htmlObject->getCount('select'));
    $this->assertSame('foo', $htmlObject->getText('option[selected]'));
    $this->assertSame('foo', $htmlObject->getText('.label'));
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

    $this->assertSame(3, $htmlObject->getCount('option'));
    $this->assertSame(1, $htmlObject->getCount('option[selected]'));
    $this->assertSame(1, $htmlObject->getCount('select'));
    $this->assertSame('', $htmlObject->getText('option[selected]'));
    $this->assertSame('', $htmlObject->getText('.label'));
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

    $this->assertEquals('foobar', $htmlObject->getText('.labelPrefix'));
  }

  /**
   * @param array $params
   * @return CMTest_TH_Html
   */
  private function _createSelect(array $params) {
    $smarty = new Smarty();
    $render = new CM_Render();
    $template = $smarty->createTemplate('string:');
    $template->assignGlobal('render', $render);
    $html = smarty_function_select($params, $template);
    return new CMTest_TH_Html($html);
  }
}
