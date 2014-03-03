<?php

class CM_RenderAdapter_FormField extends CM_RenderAdapter_Abstract {

  public function fetch(array $params = array()) {
    /** @var CM_Form_Abstract $form */
    $form = $params['form'];
    $fieldName = $params['fieldName'];
    /** @var CM_FormField_Abstract $field */
    $field = $this->_getView();
    $field->prepare($params['params']);

    $field->setTplParam('field', $field);
    $field->setTplParam('id', $form->getTagAutoId($fieldName . '-input'));
    $field->setTplParam('name', $fieldName);
    $field->setTplParam('value', $field->getValue());
    $field->setTplParam('options', $field->getOptions());

    $html = '<div class="' . implode(' ', $field->getClassHierarchy()) . '" id="' . $form->getAutoId() . '-' . $fieldName . '">';
    $html .= trim($this->_renderTemplate('default.tpl', $field->getTplParams(), true));
    if (!$field instanceof CM_FormField_Hidden) {
      $html .= '<span class="messages"></span>';
    }
    $html .= '</div>';

    return $html;
  }

  /**
   * @param string $tplName
   * @param array  $params
   * @return string
   */
  public function fetchTemplate($tplName, array $params) {
    return $this->_renderTemplate($tplName, $params, true);
  }
}
