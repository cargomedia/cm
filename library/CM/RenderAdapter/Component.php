<?php

class CM_RenderAdapter_Component extends CM_RenderAdapter_Abstract {

    public function fetch(CM_Params $viewParams) {
        /** @var CM_Component_Abstract $component */
        $component = $this->_getView();
        $component->checkAccessible();

        $viewResponse = $this->_getPreparedViewResponse($viewParams);

        $parentViewId = null;
        if (count($this->getRender()->getStack('views'))) {
            /** @var CM_View_Abstract $parentView */
            $parentView = $this->getRender()->getStackLast('views');
            $parentViewId = $parentView->getAutoId();
        }

        $this->getRender()->pushStack($this->_getStackKey(), $component);
        $this->getRender()->pushStack('views', $component);

        $cssClass = implode(' ', $component->getClassHierarchy());
        if (preg_match('#([^/]+)\.tpl$#', $viewResponse->getTemplateName(), $match)) {
            if ($match[1] != 'default') {
                $cssClass .= ' ' . $match[1]; // Include special-tpl name in class (e.g. 'mini')
            }
        }
        $html = '<div id="' . $component->getAutoId() . '" class="' . $cssClass . '">';

        $viewResponse->addData('viewOjb', $component);
        $html .=  $this->getRender()->renderViewResponse($viewResponse);

        $html .= '</div>';

        $this->getRender()->getJs()->registerComponent($component, $parentViewId);
        $this->getRender()->popStack($this->_getStackKey());
        $this->getRender()->popStack('views');

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

    /**
     * @return string
     */
    protected function _getStackKey() {
        return 'components';
    }

    /**
     * @param CM_Params $viewParams
     * @return CM_ViewResponse
     */
    protected function _getPreparedViewResponse(CM_Params $viewParams) {
        /** @var CM_Component_Abstract $component */
        $component = $this->_getView();
        $viewResponse = new CM_ViewResponse($component);
        $viewResponse->setTemplateName('default');
        $component->prepare($viewParams, $viewResponse);
        return $viewResponse;
    }
}
