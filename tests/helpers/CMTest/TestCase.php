<?php

abstract class CMTest_TestCase extends PHPUnit_Framework_TestCase {

	/** @var CM_Render */
	private $_render;

	/**
	 * @return CM_Render
	 */
	protected function _getRender() {
		if (!$this->_render) {
			$this->_render = new CM_Render($this->_getSite());
		}
		return $this->_render;
	}

	/**
	 * @param CM_Form_Abstract           $form
	 * @param CM_FormField_Abstract      $formField
	 * @param array|null                 $params
	 * @return CMTest_TH_Html
	 */
	protected function _renderFormField(CM_Form_Abstract $form, CM_FormField_Abstract $formField, array $params = null) {
		if (null === $params) {
			$params = array();
		}
		$formField->prepare($params);
		$html = $this->_getRender()->render($formField, array('form' => $form));
		return new CMTest_TH_Html($html);
	}

	/**
	 * @param string $table
	 * @param array  $where WHERE conditions: ('attr' => 'value', 'attr2' => 'value')
	 * @param int    $rowCount
	 */
	public static function assertRow($table, $where = null, $rowCount = 1) {
		$result = CM_Mysql::select($table, '*', $where);
		self::assertEquals($rowCount, $result->numRows());
	}

	public static function assertNotRow($table, $columns) {
		self::assertRow($table, $columns, 0);
	}

	public static function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = true) {
		if ($expected instanceof CM_Comparable) {
			self::assertTrue($expected->equals($actual), 'Models differ');
		} else {
			parent::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
		}
	}

	public static function assertNotEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE) {
		if ($expected instanceof CM_Comparable) {
			self::assertFalse($expected->equals($actual), 'Models do not differ');
		} else {
			parent::assertNotEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
		}
	}

	/**
	 * @param mixed|CM_Comparable $needle
	 * @param Traversable         $haystack
	 * @param string              $message
	 * @param boolean             $ignoreCase
	 * @param boolean             $checkForObjectIdentity
	 * @throws CM_Exception_Invalid
	 */
	public static function assertNotContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true) {
		if ($needle instanceof CM_Comparable) {
			if (!(is_array($haystack) || $haystack instanceof Traversable)) {
				throw new CM_Exception_Invalid('Haystack is not traversable.');
			}
			$match = false;
			foreach ($haystack as $hay) {
				if ($needle->equals($hay)) {
					$match = true;
					break;
				}
			}
			self::assertFalse($match, 'Needle contained.');
		} else {
			parent::assertNotContains($needle, $haystack, $message, $ignoreCase, $checkForObjectIdentity);
		}
	}

	/**
	 * @param mixed|CM_Comparable $needle
	 * @param Traversable         $haystack
	 * @param string              $message
	 * @param boolean             $ignoreCase
	 * @param boolean             $checkForObjectIdentity
	 * @throws CM_Exception_Invalid
	 */
	public static function assertContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true) {
		if ($needle instanceof CM_Comparable) {
			if (!(is_array($haystack) || $haystack instanceof Traversable)) {
				throw new CM_Exception_Invalid('Haystack is not traversable.');
			}
			$match = false;
			foreach ($haystack as $hay) {
				if ($needle->equals($hay)) {
					$match = true;
					break;
				}
			}
			self::assertTrue($match, 'Needle not contained.');
		} else {
			parent::assertContains($needle, $haystack, $message, $ignoreCase, $checkForObjectIdentity);
		}
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
	 * @param number          $expected
	 * @param number          $actual
	 * @param number|null
	 */
	public static function assertSameTime($expected, $actual, $delta = null) {
		if (null === $delta) {
			$delta = 1;
		}
		self::assertEquals($expected, $actual, '', $delta);
	}

	/**
	 * @param CM_Response_View_Ajax $response
	 * @param array|null            $data
	 */
	public static function assertAjaxResponseSuccess(CM_Response_View_Ajax $response, array $data = null) {
		$responseContent = json_decode($response->getContent(), true);
		self::assertArrayHasKey('success', $responseContent, 'AjaxCall not successful');
		if (null !== $data) {
			self::assertSame($data, $responseContent['success']['data']);
		}
	}

	/**
	 * @param CM_Response_View_Form $response
	 * @param string|null           $msg
	 */
	public static function assertFormResponseSuccess(CM_Response_View_Form $response, $msg = null) {
		$responseContent = json_decode($response->getContent(), true);
		self::assertFalse($response->hasErrors(), 'Response has errors.');
		if (null !== $msg) {
			$msg = (string) $msg;
			self::assertContains($msg, $responseContent['success']['messages'], 'Response has no message `' . $msg . '`.');
		}
	}

	/**
	 * @param CM_Response_View_Form $response
	 * @param string|null           $errorMsg
	 * @param string|null           $formFieldName
	 */
	public static function assertFormResponseError(CM_Response_View_Form $response, $errorMsg = null, $formFieldName = null) {
		$responseContent = json_decode($response->getContent(), true);
		self::assertTrue($response->hasErrors());
		if (null !== $errorMsg) {
			$errorMsg = (string) $errorMsg;
			$error = $errorMsg;
			if (null !== $formFieldName) {
				$formFieldName = (string) $formFieldName;
				$error = array($errorMsg, $formFieldName);
			}
			self::assertContains($error, $responseContent['success']['errors']);
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
	 * @param string             $methodName
	 * @param string             $viewClassName
	 * @param array|null         $params
	 * @param CM_Model_User|null $viewer
	 * @param array|null         $viewParams
	 * @param int|null           $siteId
	 * @return CM_Response_View_Ajax
	 */
	public function getResponseAjax($methodName, $viewClassName, array $params = null, CM_Model_User $viewer = null, array $viewParams = null, $siteId = null) {
		if (null === $viewParams) {
			$viewParams = array();
		}
		if (null === $params) {
			$params = array();
		}
		if (null === $siteId) {
			$siteId = $this->_getSite()->getId();
		}
		$session = new CM_Session();
		if ($viewer) {
			$session->setUser($viewer);
		}
		$headers = array('Cookie' => 'sessionId=' . $session->getId());
		unset($session); // Make sure session is stored persistently

		$viewArray = array('className' => $viewClassName, 'id' => 'mockViewId', 'params' => $viewParams);
		$body = CM_Params::encode(array('view' => $viewArray, 'method' => $methodName, 'params' => $params), true);
		$request = new CM_Request_Post('/ajax/' . $siteId, $headers, $body);

		$response = new CM_Response_View_Ajax($request);
		$response->process();
		return $response;
	}

	/**
	 * @param string               $formClassName
	 * @param string               $actionName
	 * @param array                $data
	 * @param string|null          $componentClassName Component that uses that form
	 * @param CM_Model_User|null   $viewer
	 * @param array|null           $componentParams
	 * @param CM_Request_Post|null $request
	 * @param int|null             $siteId
	 * @return CM_Response_View_Form
	 */
	public function getResponseForm($formClassName, $actionName, array $data, $componentClassName = null, CM_Model_User $viewer = null, array $componentParams = null, &$request = null, $siteId = null) {
		if (null === $componentParams) {
			$componentParams = array();
		}
		if (null === $siteId) {
			$siteId = $this->_getSite()->getId();
		}
		$session = new CM_Session();
		if ($viewer) {
			$session->setUser($viewer);
		}
		$headers = array('Cookie' => 'sessionId=' . $session->getId());
		unset($session); // Make sure session is stored persistently

		$formArray = array('className' => $formClassName, 'params' => array(), 'id' => 'mockFormId');
		$viewArray = array('className' => $componentClassName, 'params' => $componentParams, 'id' => 'mockFormComponentId');
		$body = CM_Params::encode(array('view' => $viewArray, 'form' => $formArray, 'actionName' => $actionName, 'data' => $data), true);
		$request = new CM_Request_Post('/form/' . $siteId, $headers, $body);

		$response = new CM_Response_View_Form($request);
		$response->process();
		return $response;
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
