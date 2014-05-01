<?php

class CM_RenderAdapter_FormField extends CM_RenderAdapter_Abstract {

    public function fetch(CM_Params $renderParams, $fieldName) {
        $fieldName = (string) $fieldName;
        $field = $this->_getFormField();
        $form = $this->getRender()->getStackLast('forms');

        $viewResponse = new CM_ViewResponse($field);
        $viewResponse->setTemplateName('default');
        $field->prepare($renderParams, $viewResponse);


        $viewResponse->set('field', $field);
        $viewResponse->set('id', $form->getTagAutoId($fieldName . '-input'));
        $viewResponse->set('name', $fieldName);
        $viewResponse->set('value', $field->getValue());
        $viewResponse->set('options', $field->getOptions());

        $html = '<div class="' . implode(' ', $field->getClassHierarchy()) . '" id="' . $form->getAutoId() . '-' . $fieldName . '">';
        $html .= trim($this->getRender()->fetchViewResponse($viewResponse));
        $this->getRender()->getJs()->registerViewResponse($viewResponse);
        if (!$field instanceof CM_FormField_Hidden) {
            $html .= '<span class="messages"></span>';
        }
        $html .= '</div>';

        if ($form) {
            $form->getJs()->append("this.registerField('{$fieldName}', cm.views[{$viewResponse->getAutoId()});");
        }
        return $html;
    }

    /**
     * @return CM_FormField_Abstract
     */
    private function _getFormField() {
        return $this->_getView();
    }
}
