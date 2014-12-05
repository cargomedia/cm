<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.location.php';

class smarty_function_locationTest extends CMTest_TestCase {

    /** @var CM_Model_Location */
    protected $_location;

    /** @var CM_Frontend_Render */
    protected $_render;

    /** @var Smarty_Internal_Template */
    protected $_template;

    public function setUp() {
        $smarty = new Smarty();
        $this->_render = new CM_Frontend_Render();
        $this->_template = $smarty->createTemplate('string:');
        $this->_template->assignGlobal('render', $this->_render);
        $this->_location = CMTest_TH::createLocation();
    }

    public function testCaching() {
        $debug = CM_Debug::getInstance();
        $debug->setEnabled(true);

        $flag = '<img class="flag" src="' . $this->_render->getUrlResource('layout', 'img/flags/fo.png') . '" title="countryFoo" />';
        $expected = '<span class="function-location">cityFoo, stateFoo, countryFoo' . $flag . '</span>';
        $this->_assertSame($expected, array('location' => $this->_location));

        $debug->setEnabled(false);
        $this->assertEmpty($debug->getStats());
    }

    public function testLabeler() {
        $partLabeler = function (CM_Model_Location $locationPart, CM_Model_Location $location) {
            return '(' . $locationPart->getName() . ')';
        };
        $flagLabeler = function (CM_Model_Location $locationPart, CM_Model_Location $location) {
            return '[' . $locationPart->getName() . ']';
        };
        $expected = '<span class="function-location">(cityFoo), (stateFoo), (countryFoo)[countryFoo]</span>';
        $this->_assertSame($expected, array('location' => $this->_location, 'partLabeler' => $partLabeler, 'flagLabeler' => $flagLabeler));
    }

    public function testLabelerEmpty() {
        $partLabeler = function (CM_Model_Location $locationPart, CM_Model_Location $location) {
            if (CM_Model_Location::LEVEL_COUNTRY === $locationPart->getLevel()) {
                return null;
            } else {
                return 'foo';
            }
        };
        $flagLabeler = function (CM_Model_Location $locationPart, CM_Model_Location $location) {
            return null;
        };
        $expected = '<span class="function-location">foo, foo</span>';
        $this->_assertSame($expected, array('location' => $this->_location, 'partLabeler' => $partLabeler, 'flagLabeler' => $flagLabeler));
    }

    /**
     * @param string $expected
     * @param array  $params
     */
    private function _assertSame($expected, array $params) {
        $this->assertSame($expected, smarty_function_location($params, $this->_template));
    }
}
