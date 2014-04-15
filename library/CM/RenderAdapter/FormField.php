<?php

class CM_RenderAdapter_FormField extends CM_RenderAdapter_Abstract {

    public function fetch(CM_Params $renderParams, CM_Form_Abstract $form, $fieldName) {
        $fieldName = (string) $fieldName;
        $field = $this->_getFormField();
        $field->prepare($renderParams);

        $field->setTplParam('field', $field);
        $field->setTplParam('id', $form->getTagAutoId($fieldName . '-input'));
        $field->setTplParam('name', $fieldName);
        $field->setTplParam('value', $field->getValue());
        $field->setTplParam('options', $field->getOptions());

        $html = '<div class="' . implode(' ', $field->getClassHierarchy()) . '" id="' . $form->getAutoId() . '-' . $fieldName . '">';
        $viewResponse = new CM_ViewResponse($field);
        $viewResponse->setTemplateName('default');
        $viewResponse->setData($field->getTplParams());
        $html .= trim($this->getRender()->fetchViewResponse($viewResponse));
        if (!$field instanceof CM_FormField_Hidden) {
            $html .= '<span class="messages"></span>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @return CM_FormField_Abstract
     */
    private function _getFormField() {
        return $this->_getView();
    }
}
