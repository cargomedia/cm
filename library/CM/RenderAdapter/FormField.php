<?php

class CM_RenderAdapter_FormField extends CM_RenderAdapter_Abstract {

    public function fetch(array $params, CM_FormField_Abstract $field, CM_Form_Abstract $form, $fieldName) {
        $fieldName = (string) $fieldName;
        /** @var CM_FormField_Abstract $field */
        $field->prepare($params);

        $field->setTplParam('field', $field);
        $field->setTplParam('id', $form->getTagAutoId($fieldName . '-input'));
        $field->setTplParam('name', $fieldName);
        $field->setTplParam('value', $field->getValue());
        $field->setTplParam('options', $field->getOptions());

        $html = '<div class="' . implode(' ', $field->getClassHierarchy()) . '" id="' . $form->getAutoId() . '-' . $fieldName . '">';
        $viewResponse = new CM_ViewResponse($this->_getView());
        $viewResponse->setTemplateName('default');
        $viewResponse->setData($field->getTplParams());
        $html .= trim($this->getRender()->renderViewResponse($viewResponse));
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
