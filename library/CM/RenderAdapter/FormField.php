<?php

class CM_RenderAdapter_FormField extends CM_RenderAdapter_Abstract {

    public function fetch(array $params = array()) {
        /** @var CM_Form_Abstract $form */
        $form = $params['form'];
        $fieldName = $params['fieldName'];
        /** @var CM_FormField_Abstract $field */
        $field = $this->_getView();

        $field->setTplParam('field', $field);
        $field->setTplParam('id', $form->getTagAutoId($fieldName . '-input'));
        $field->setTplParam('name', $fieldName);
        $field->setTplParam('value', $field->getValue());
        $field->setTplParam('options', $field->getOptions());

        $cssClass = implode(' ', $field->getClassHierarchy());
        if (preg_match('#([^/]+)\.tpl$#', $field->getTplName(), $match)) {
            if ($match[1] != 'default') {
                $cssClass .= ' ' . $match[1]; // Include special-tpl name in class (e.g. 'mini')
            }
        }

        $html = '<div class="' . $cssClass . '" id="' . $form->getAutoId() . '-' . $fieldName . '">';
        $html .= trim($this->_renderTemplate($field->getTplName(), $field->getTplParams(), true));
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
