<?php

class CM_RenderAdapter_FormField extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Form_Abstract $form */
		$form = $params['form'];
		/** @var CM_FormField_Abstract $field */
		$field = $this->_getView();

		$field->setTplParam('field', $field);
		$field->setTplParam('id', $form->getTagAutoId($field->getName() . '-input'));
		$field->setTplParam('name', $field->getName());
		$field->setTplParam('value', $field->getValue());
		$field->setTplParam('options', $field->getOptions());

		$html = '<div class="input" id="' . $form->getAutoId() . '-' . $field->getName() . '">';
		$html .= '<div class="input-inner">';

		$html .= trim($this->_renderTemplate('default.tpl', $field->getTplParams(), true));

		if (!$field instanceof CM_FormField_Hidden) {
			$html .= '<span class="messages"></span>';
		}
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}
}
