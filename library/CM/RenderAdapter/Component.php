<?php

class CM_RenderAdapter_Component extends CM_RenderAdapter_Abstract {

    /** @var CM_Frontend_ViewResponse */
    protected $_viewResponse;

    /**
     * @return string
     */
    public function fetch() {
        $component = $this->_getComponent();
        $render = $this->getRender();

        $component->checkAccessible($render->getEnvironment());

        $viewResponse = $this->_getViewResponse();
        $frontend = $render->getGlobalResponse();
        $frontend->treeExpand($viewResponse);

        $this->_prepareViewResponse($viewResponse);

        $html = '<div id="' . $viewResponse->getAutoId() . '" class="' . join(' ', $viewResponse->getCssClasses()) . '">';
        $html .= $render->fetchViewResponse($viewResponse);
        $html .= '</div>';

        $frontend->treeCollapse();
        return $html;
    }

    /**
     * @param CM_Frontend_ViewResponse $viewResponse
     */
    protected function _prepareViewResponse(CM_Frontend_ViewResponse $viewResponse) {
    }

    /**
     * @return CM_Frontend_ViewResponse
     */
    protected function _getViewResponse() {
        if (null === $this->_viewResponse) {
            $component = $this->_getComponent();
            $environment = $this->getRender()->getEnvironment();

            $viewResponse = new CM_Frontend_ViewResponse($component);
            $templateName = $component->getParams()->getString('template', 'default');
            $viewResponse->setTemplateName($templateName);
            $component->prepare($environment, $viewResponse);
            $viewResponse->set('viewObj', $component);
            $this->_viewResponse = $viewResponse;
        }
        return $this->_viewResponse;
    }

    /**
     * @return CM_Component_Abstract
     */
    private function _getComponent() {
        return $this->_getView();
    }

    /**
     * @param CM_Frontend_Render    $render
     * @param CM_Component_Abstract $view
     * @return CM_RenderAdapter_Component
     */
    public static function factory(CM_Frontend_Render $render, CM_Component_Abstract $view) {
        if ($view instanceof CM_Page_Abstract) {
            return new CM_RenderAdapter_Page($render, $view);
        }
        return new CM_RenderAdapter_Component($render, $view);
    }
}
