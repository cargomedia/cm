<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.date_time.php';

class smarty_function_date_timeTest extends CMTest_TestCase {

    /** @var CM_Frontend_Render */
    private $_render;

    /** @var Smarty_Internal_Template */
    private $_template;

    public function setUp() {
        $smarty = new Smarty();
        $this->_render = new CM_Frontend_Render();
        $this->_template = $smarty->createTemplate('string:');
        $this->_template->assignGlobal('render', $this->_render);
    }

    public function testRender() {
        $time = strtotime('2003-02-01 12:34:56');
        $date = new DateTime('2003-02-01 11:22:33');
        foreach ([[
            'params'   => ['date' => $date],
            'expected' => '11:22:33',
        ], [
            'params'   => ['time' => $time],
            'expected' => '12:34:56',
        ], [
            'params'   => ['time' => $time, 'timeZone' => new DateTimeZone('US/Eastern')],
            'expected' => '7:34:56',
        ]] as $testData) {
            $this->_assertSame($testData['expected'], $testData['params']);
        }
    }

    /**
     * @param string $expected
     * @param array  $params
     */
    private function _assertSame($expected, array $params) {
        $this->assertSame($expected, smarty_function_date_time($params, $this->_template));
    }
}
