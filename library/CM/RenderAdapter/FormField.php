<?php

class CM_RenderAdapter_FormField extends CM_RenderAdapter_Abstract {

    public function fetch(CM_Params $renderParams, CM_Frontend_ViewResponse &$viewResponse = null) {
        $field = $this->_getFormField();
        $frontend = $this->getRender()->getFrontend();

        $viewResponse = new CM_Frontend_ViewResponse($field);
        $viewResponse->setTemplateName('default');
        $field->prepare($renderParams, $viewResponse);
        $viewResponse->set('field', $field);
        $viewResponse->set('inputId', $viewResponse->getAutoIdTagged('input'));
        $viewResponse->set('name', $field->getName());
        $viewResponse->set('value', $field->getValue());
        $viewResponse->set('options', $field->getOptions());

        $frontend->treeExpand($viewResponse);

        $html = '<div class="' . implode(' ', $field->getClassHierarchy()) . '" id="' . $viewResponse->getAutoId() . '">';
        $html .= trim($this->getRender()->fetchViewResponse($viewResponse));
        if (!$field instanceof CM_FormField_Hidden) {
            $html .= '<span class="messages"></span>';
        }
        $html .= '</div>';

        $formViewResponse = $frontend->getClosestViewResponse('CM_Form_Abstract');
        if ($formViewResponse) {
            $formViewResponse->getJs()->append("this.registerField(cm.views['{$viewResponse->getAutoId()}']);");
        }

        $frontend->treeCollapse();
        return $html;
    }

    /**
     * @return CM_FormField_Abstract
     */
    private function _getFormField() {
        return $this->_getView();
    }
}
