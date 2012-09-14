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
	 * @param array|null $namespaces
	 * @return CM_Site_Abstract
	 */
	protected function _getSite(array $namespaces = null) {
		if (null === $namespaces) {
			$namespaces = array('CM');
		}
		/** @var CM_Site_Abstract $site */
		$site = $this->getMockForAbstractClass('CM_Site_Abstract', array(), '', true, true, true, array('getId', 'getNamespaces'));
		$site->expects($this->any())->method('getId')->will($this->returnValue(1));
		$site->expects($this->any())->method('getNamespaces')->will($this->returnValue($namespaces));
		return $site;
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
		$componentParams = array();

		$site = $this->_getSite();

		$requestArgs = array('uri' => '/form/' . $site->getId());
		$requestMock = $this->getMockBuilder('CM_Request_Post')->setConstructorArgs($requestArgs)->setMethods(array('getViewer', 'getQuery'))->getMock();
		$requestMock->expects($this->any())->method('getViewer')->will($this->returnValue($viewer));
		$viewArray = array('className' => $componentClassName, 'params' => $componentParams, 'id' => 'mockFormComponentId');
		$formArray = array('className' => $formClassName, 'params' => array(), 'id' => 'mockFormId');
		$requestMock->expects($this->any())->method('getQuery')->will($this->returnValue(array('view' => $viewArray, 'form' => $formArray,
			'actionName' => $actionName, 'data' => $data)));
		$response = new CM_Response_View_Form($requestMock);
		$response->process();
		$responseArray = json_decode($response->getContent(), true);
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
}
