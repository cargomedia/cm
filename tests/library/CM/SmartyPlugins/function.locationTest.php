<?php

require_once CM_Util::getNamespacePath('CM') . 'library/CM/SmartyPlugins/function.location.php';

class smarty_function_linkTest extends CMTest_TestCase {

    /** @var CM_Model_Location */
    protected $_location;

    /** @var CM_Render */
    protected $_render;

    /** @var Smarty_Internal_Template */
    protected $_template;

    public function setUp() {
        $smarty = new Smarty();
        $this->_render = new CM_Render();
        $this->_template = $smarty->createTemplate('string:');
        $this->_template->assignGlobal('render', $this->_render);
        $this->_location = CMTest_TH::createLocation();
    }

    public function testCaching() {
        $city = $this->_location->get(CM_Model_Location::LEVEL_CITY);
        $state = $this->_location->get(CM_Model_Location::LEVEL_STATE);
        $country = $this->_location->get(CM_Model_Location::LEVEL_COUNTRY);
        $debug = CM_Debug::getInstance();
        $debug->setEnabled(true);

        $urlFlag = $this->_render->getUrlResource('layout', 'img/flags/fo.png');
        $this->_assertSame('cityFoo, countryFoo <img class="flag" src="' . $urlFlag . '" />', array('location' => $this->_location));

        $debug->setEnabled(false);
        $stats = $debug->getStats();
        $this->assertFalse(isset($stats['apc-get']));
        $type = CM_Model_Location::getTypeStatic();
        $this->assertEquals(array(
            'CM_Model_StorageAdapter_Cache_type:' . $type . '_id:' . serialize($city->getIdRaw()),
            'CM_Model_StorageAdapter_Cache_type:' . $type . '_id:' . serialize($state->getIdRaw()),
            'CM_Model_StorageAdapter_Cache_type:' . $type . '_id:' . serialize($country->getIdRaw()),
            'CM_Model_StorageAdapter_Cache_type:' . $type . '_id:' . serialize($country->getIdRaw()),
        ), $stats['runtime-get']);
    }

    /**
     * @param string $expected
     * @param array  $params
     */
    private function _assertSame($expected, array $params) {
        $this->assertSame($expected, smarty_function_location($params, $this->_template));
    }
}
