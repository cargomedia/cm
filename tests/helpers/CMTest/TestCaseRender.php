<?php

abstract class CMTest_TestCaseRender extends CMTest_TestCase {

	/** @var CM_Render */
	private $_render;

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

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
}
