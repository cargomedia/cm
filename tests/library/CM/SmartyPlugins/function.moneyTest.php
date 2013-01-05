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

	public function testNiceDiscount() {
		$this->_assertSame("$99.95", array('amount' => 96, 'format' => 'discount'));
		$this->_assertSame("$16.95", array('amount' => 17, 'format' => 'discount'));
		$this->_assertSame("$12.95", array('amount' => 13, 'format' => 'discount'));
		$this->_assertSame("$1.50", array('amount' => 1.5, 'format' => 'discount'));
		$this->_assertSame("$2.95", array('amount' => 3.1, 'format' => 'discount'));
	}

	/**
	 * @param string $expected
	 * @param array  $params
	 */
	private function _assertSame($expected, array $params) {
		$this->assertSame($expected, smarty_function_money($params, $this->_template));
	}
}
