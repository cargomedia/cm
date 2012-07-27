<?php

class CM_Form_Example extends CM_Form_Abstract {
	public function setup() {
		$this->registerField(new CM_FormField_Text('text'));
		$this->registerField(new CM_FormField_Integer('int', -10, 20, 2));
		$this->registerField(new CM_FormField_Distance('locationSlider'));
		$this->registerField(new CM_FormField_Location('location', CM_Model_Location::LEVEL_COUNTRY, $this->getField('locationSlider')));
		$this->registerField(new CM_FormField_FileImage('image', 2));
		$this->registerField(new CM_FormField_Color('color'));
		$this->registerField(new CM_FormField_Set('set', array(1 => 'Eins', 2 => 'Zwei'), true));
		$this->registerField(new CM_FormField_Boolean('boolean'));
		$this->registerField(new CM_FormField_Set_Select('setSelect1', array(1 => 'Eins', 2 => 'Zwei'), true));
		$this->registerField(new CM_FormField_Set_Select('setSelect2', array(1 => 'Eins', 2 => 'Zwei'), true));

		$this->registerAction(new CM_FormAction_Example_Go());
	}

	public function renderStart(array $params = null) {
		$ip = CM_Request_Abstract::getInstance()->getIp();
		if ($locationGuess = CM_Model_Location::findByIp($ip)) {
			$this->getField('location')->setValue($locationGuess);
		}
	}
}
