<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.date_range.php';

class smarty_function_date_rangeTest extends CMTest_TestCase {

    /** @var CM_Frontend_Render */
    private $_render;

    /** @var Smarty_Internal_Template */
    private $_template;

    public function setUp() {
        $language = CMTest_TH::createLanguage();
        $language->setTranslation('.date.period.years', '{$count} years', ['count']);
        $language->setTranslation('.date.period.hour', '1 hour');
        $language->setTranslation('.date.period.day', '1 day');

        $smarty = new Smarty();
        $environment = new CM_Frontend_Environment(null, null, $language);
        $this->_render = new CM_Frontend_Render($environment);
        $this->_template = $smarty->createTemplate('string:');
        $this->_template->assignGlobal('render', $this->_render);
    }

    public function testRender() {
        $start = strtotime('2003-02-01 12:34:00');
        $stop1 = strtotime('2003-02-01 13:45:10');
        $stop2 = strtotime('2003-02-02 13:45:20');
        $yearsAgo = floor((time() - $start) / (365 * 86400));
        foreach ([[
            'params'   => [],
            'expected' => '<span class="date-range"></span>',
        ], [
            'params'   => ['class' => 'myClass'],
            'expected' => '<span class="date-range myClass"></span>',
        ], [
            'params'   => ['start' => $start],
            'expected' => '<span class="date-range">2/1/03 – now (<span class="date-period">' . $yearsAgo .
                ' years</span>)</span>',
        ], [
            'params'   => ['start' => $start, 'showTime' => true],
            'expected' => '<span class="date-range">2/1/03 12:34 – now (<span class="date-period">' . $yearsAgo . ' years</span>)</span>',
        ], [
            'params'   => ['start' => $start, 'short' => true],
            'expected' => '<span class="date-range">2/1/03 – now (<span class="date-period">' . $yearsAgo .
                ' years</span>)</span>',
        ], [
            'params'   => ['start' => $start, 'showTime' => true, 'short' => true],
            'expected' => '<span class="date-range">2/1/03 12:34 – now (<span class="date-period">' . $yearsAgo . ' years</span>)</span>',
        ], [
            'params'   => ['start' => $start, 'stop' => $stop1],
            'expected' => '<span class="date-range">2/1/03 – 2/1/03 (<span class="date-period">1 hour</span>)</span>',
        ], [
            'params'   => ['start' => $start, 'stop' => $stop1, 'showTime' => true],
            'expected' => '<span class="date-range">2/1/03 12:34 – 2/1/03 13:45 (<span class="date-period">1 hour</span>)</span>',
        ], [
            'params'   => ['start' => $start, 'stop' => $stop1, 'short' => true],
            'expected' => '<span class="date-range">2/1/03 – 2/1/03 (<span class="date-period">01:11:10</span>)</span>',
        ], [
            'params'   => ['start' => $start, 'stop' => $stop1, 'showTime' => true, 'short' => true],
            'expected' => '<span class="date-range">2/1/03 12:34 – 2/1/03 13:45 (<span class="date-period">01:11:10</span>)</span>',
        ], [
            'params'   => ['start' => $start, 'stop' => $stop1, 'showTime' => true, 'short' => true, 'showSeconds' => true],
            'expected' => '<span class="date-range">2/1/03 12:34:00 – 2/1/03 13:45:10 (<span class="date-period">01:11:10</span>)</span>',
        ], [
            'params'   => ['start' => $start, 'stop' => $stop2],
            'expected' => '<span class="date-range">2/1/03 – 2/2/03 (<span class="date-period">1 day</span>)</span>',
        ], [
            'params'   => ['start' => $start, 'stop' => $stop2, 'showTime' => true],
            'expected' => '<span class="date-range">2/1/03 12:34 – 2/2/03 13:45 (<span class="date-period">1 day</span>)</span>',
        ], [
            'params'   => ['start' => $start, 'stop' => $stop2, 'short' => true],
            'expected' => '<span class="date-range">2/1/03 – 2/2/03 (<span class="date-period">1 day</span>)</span>',
        ], [
            'params'   => ['start' => $start, 'stop' => $stop2, 'showTime' => true, 'short' => true],
            'expected' => '<span class="date-range">2/1/03 12:34 – 2/2/03 13:45 (<span class="date-period">1 day</span>)</span>',
        ]] as $testData) {
            $this->_assertSame($testData['expected'], $testData['params']);
        }
    }

    /**
     * @param string $expected
     * @param array  $params
     */
    private function _assertSame($expected, array $params) {
        $this->assertSame($expected, smarty_function_date_range($params, $this->_template));
    }
}
