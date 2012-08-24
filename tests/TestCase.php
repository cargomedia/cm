<?php
require_once 'PHPUnit/Autoload.php';
require_once 'TH.php';
TH::init();

abstract class TestCase extends PHPUnit_Framework_TestCase {
	/**
	 * @param string $table
	 * @param array  $where WHERE conditions: ('attr' => 'value', 'attr2' => 'value')
	 * @param int	$rowCount
	 */
	public static function assertRow($table, $where = null, $rowCount = 1) {
		$result = CM_Mysql::select($table, '*', $where);
		self::assertEquals($rowCount, $result->numRows());
	}

	public static function assertNotRow($table, $columns) {
		self::assertRow($table, $columns, 0);
	}

	public static function assertModelEquals(CM_Model_Abstract $expected, CM_Model_Abstract $actual) {
		self::assertTrue($expected->equals($actual), 'Models differ');
	}

	public static function assertModelNotEquals(CM_Model_Abstract $expected, CM_Model_Abstract $actual) {
		self::assertFalse($expected->equals($actual), 'Models do not differ');
	}

	/**
	 * @param array $needles
	 * @param mixed $haystack
	 */
	public static function assertContainsAll(array $needles, $haystack) {
		foreach ($needles as $needle) {
			self::assertContains($needle, $haystack);
		}
	}

	/**
	 * @param array $needles
	 * @param mixed $haystack
	 */
	public static function assertNotContainsAll(array $needles, $haystack) {
		foreach ($needles as $needle) {
			self::assertNotContains($needle, $haystack);
		}
	}

	/**
	 *
	 * @param array $needles
	 * @param array $haystacks
	 */
	public static function assertArrayContains(array $needles, array $haystacks) {
		if (count($haystacks) < count($needles)) {
			self::fail('not enough elements to compare each');
		}
		for ($i = 0; $i < count($needles); $i++) {
			self::assertContains($needles[$i], $haystacks[$i]);
		}
	}

	/**
	 * Checks a TH_Html content (html) with tidy
	 *
	 * Test is skipeed it tidy not installed
	 *
	 * @param TH_Html $page
	 * @param bool	$warning If warnings should be checked (default = true)
	 */
	public static function assertTidy(TH_Html $page, $warnings = true) {

		if (!extension_loaded('tidy')) {
			self::markTestSkipped('The tidy extension is not available.');
		}

		$html = $page->getHtml();
		$tidy = new tidy();

		$tidyConfig = array('show-errors' => 1, 'show-warnings' => $warnings);
		$tidy->parseString($html, $tidyConfig, 'UTF8');

		//$tidy->cleanRepair();
		$tidy->diagnose();
		$lines = array_reverse(explode("\n", $tidy->errorBuffer));
		$content = '';

		foreach ($lines as $line) {
			if (empty($line) || $line == 'No warnings or errors were found.' || strpos($line, 'Info:') === 0 ||
					strpos($line, 'errors were found!') > 0 || strpos($line, 'proprietary attribute') != false
			) {
				// ignore
			} else {
				$content .= $line . PHP_EOL;
			}
		}

		self::assertEmpty($content, $content);
	}

	/**
	 * @param string				   $formClassName
	 * @param string				   $actionName
	 * @param array					$data
	 * @param string|null			  $componentClassName Component that uses that form
	 * @param CM_Model_User|null	   $viewer
	 * @return mixed
	 */
	public function getMockFormResponse($formClassName, $actionName, array $data, $componentClassName = null, CM_Model_User $viewer = null) {
		$requestMock = $this->getMockBuilder('CM_Request_Post')->disableOriginalConstructor()->getMock();
		$requestMock->expects($this->any())->method('getViewer')->will($this->returnValue($viewer));
		$viewArray = array('className' => $componentClassName, 'params' => array(), 'id' => 'mockFormComponentId');
		$formArray = array('className' => $formClassName, 'params' => array(), 'id' => 'mockFormId');
		$requestMock->expects($this->any())->method('getQuery')->will($this->returnValue(array('view' => $viewArray, 'form' => $formArray,
			'actionName' => $actionName, 'data' => $data)));
		$response = new CM_Response_View_Form($requestMock);
		$responseArray = json_decode($response->process(), true);
		return $responseArray['success'];
	}

	/**
	 * @return CM_Form_Abstract
	 */
	public function getMockForm() {
		$formMock = $this->getMockForAbstractClass('CM_Form_Abstract');
		$formMock->expects($this->any())->method('getName')->will($this->returnValue('formName'));
		$formMock->frontend_data['auto_id'] = 'formId';
		return $formMock;
	}

	/**
	 * @return CM_Render
	 */
	protected function _getRender() {
		/** @var CM_Site_Abstract $siteMock */
		$siteMock = $this->getMockForAbstractClass('CM_Site_Abstract', array(), '', true, true, true, array('getId', 'getNamespaces'));
		$siteMock->expects($this->any())->method('getNamespaces')->will($this->returnValue(array('TEST', 'CM')));
		$siteMock->expects($this->any())->method('getId')->will($this->returnValue(1));
		return new CM_Render($siteMock);
	}
}
