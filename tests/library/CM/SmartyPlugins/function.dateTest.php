<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.date.php';

class smarty_function_dateTest extends CMTest_TestCase {

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
        $time = strtotime('2003-02-01 12:34');
        foreach ([[
            'params'   => ['time' => $time],
            'expected' => '2/1/03',
        ], [
            'params'   => ['time' => $time, 'showTime' => true],
            'expected' =>
                (CMTest_TH::getVersionICU() < 50)
                    ? '2/1/03 12:34 PM'
                    : '2/1/03, 12:34 PM'
            ,
        ], [
            'params'   => ['time' => $time, 'showTime' => true, 'timeZone' => new DateTimeZone('US/Eastern')],
            'expected' =>
                (CMTest_TH::getVersionICU() < 50)
                    ? '2/1/03 7:34 AM'
                    : '2/1/03, 7:34 AM'
            ,
        ], [
            'params'   => ['time' => $time, 'showTime' => true, 'timeZone' => 'US/Eastern'],
            'expected' =>
                (CMTest_TH::getVersionICU() < 50)
                    ? '2/1/03 7:34 AM'
                    : '2/1/03, 7:34 AM'
            ,
        ], [
            'params'   => ['time' => $time, 'showWeekday' => true],
            'expected' => 'Sat 2/1/03',
        ]] as $testData) {
            $this->_assertSame($testData['expected'], $testData['params']);
        }
    }

    /**
     * @param string $expected
     * @param array  $params
     */
    private function _assertSame($expected, array $params) {
        $this->assertSame($expected, smarty_function_date($params, $this->_template));
    }
}
