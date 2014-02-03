<?php

class CM_Form_Example extends CM_Form_Abstract {

	public function setup() {
		$this->registerField('text', new CM_FormField_Text());
		$this->registerField('int', new CM_FormField_Integer(-10, 20, 2));
		$this->registerField('locationSlider', new CM_FormField_Distance());
		$this->registerField('location', new CM_FormField_Location(null, null, 'locationSlider'));
		$this->registerField('file', new CM_FormField_File(2));
		$this->registerField('image', new CM_FormField_FileImage(2));
		$this->registerField('color', new CM_FormField_Color());
		$this->registerField('date', new CM_FormField_Date());
		$this->registerField('set', new CM_FormField_Set(array(1 => 'Eins', 2 => 'Zwei'), true));
		$this->registerField('boolean', new CM_FormField_Boolean());
		$this->registerField('setSelect1', new CM_FormField_Set_Select(array(1 => 'Eins', 2 => 'Zwei'), true));
		$this->registerField('setSelect2', new CM_FormField_Set_Select(array(1 => 'Eins', 2 => 'Zwei'), true));
		$this->registerField('setSelect3', new CM_FormField_Set_Select(array(1 => 'Female', 2 => 'Male'), true));

		$this->registerAction(new CM_FormAction_Example_Go($this));
	}

	public function _renderStart(CM_Params $params) {
		$ip = CM_Request_Abstract::getInstance()->getIp();
		if ($locationGuess = CM_Model_Location::findByIp($ip)) {
			$this->getField('location')->setValue($locationGuess);
		}
	}
}
