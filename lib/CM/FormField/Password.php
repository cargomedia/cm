<?php

class CM_FormField_Password extends CM_FormField_Text {

	public function __construct($name) {
		parent::__construct($name, 4, 1000);
	}
}
