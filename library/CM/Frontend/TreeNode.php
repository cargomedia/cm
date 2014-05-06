<?php

class CM_Frontend_TreeNode implements Tree\Node\NodeInterface {

    use \Tree\Node\NodeTrait;

    /** @var CM_Frontend_ViewResponse */
    private $_viewResponse;

    /**
     * @param CM_Frontend_ViewResponse $viewResponse
     */
    public function __construct(CM_Frontend_ViewResponse $viewResponse) {
        $this->_setViewResponse($viewResponse);
    }

    /**
     * @return CM_Frontend_ViewResponse
     */
    public function getValue() {
        return $this->_viewResponse;
    }

    /**
     * @param CM_Frontend_ViewResponse $value
     * @return \Tree\Node\NodeInterface
     */
    public function setValue($value) {
        $this->_setViewResponse($value);
        return $this;
    }

    /**
     * @return self
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param CM_Frontend_ViewResponse $viewResponse
     */
    private function _setViewResponse(CM_Frontend_ViewResponse $viewResponse) {
        $this->_viewResponse = $viewResponse;
    }
}
