<?php
require_once __DIR__ . '/../../../TestCase.php';

require_once CM_Util::getNamespacePath('CM') . 'library/CM/SmartyPlugins/function.money.php';

class smarty_function_moneyTest extends TestCase {

	/**
	 * @var Smarty_Internal_Template
	 */
	private $_template;

	public function setUp() {
		$smarty = new Smarty();
		$render = new CM_Render($this->_getSite());
		$this->_template = $smarty->createTemplate('string:');
		$this->_template->assignGlobal('render', $render);
	}

	public function testFormat() {
		$this->_assertSame("$23.33", array('amount' => 23.33333));
		$this->_assertSame("$23.00", array('amount' => 23));
	}

	public function testCurrency() {
		$this->_assertSame("CHF23.35", array('amount' => 23.33333, 'currency' => 'CHF'));
		$this->_assertSame("£23.33", array('amount' => 23.33333, 'currency' => 'GBP'));
	}

	/**
	 * @param string $expected
	 * @param array  $params
	 */
	private function _assertSame($expected, array $params) {
		$this->assertSame($expected, smarty_function_money($params, $this->_template));
	}
}
