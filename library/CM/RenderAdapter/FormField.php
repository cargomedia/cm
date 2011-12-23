<?php

class CM_RenderAdapter_FormField extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Form_Abstract $form */
		$form = $params['form'];
		/** @var CM_FormField_Abstract $field */
		$field = $this->_getObject();

		$field->setTplParam('field', $field);
		$field->setTplParam('id', $form->getTagAutoId($field->getName() . '-input'));
		$field->setTplParam('name', $field->getName());
		$field->setTplParam('value', $field->getValue());
		$field->setTplParam('options', $field->getOptions());

		$tpl = $this->getTemplate();
		$tpl->assign($field->getTplParams());

		$html = '<span id="' . $form->frontend_data['auto_id'] . '-' . $field->getName() . '">';
		$html .= trim($tpl->fetch());
		if (!$field instanceof CM_FormField_Hidden) {
			$html .= '<span class="messages"></span>';
		}
		$html .= '</span>';

		return $html;
	}
}
