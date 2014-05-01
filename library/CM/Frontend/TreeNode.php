<?php

class CM_Frontend_TreeNode implements Tree\Node\NodeInterface {

    use \Tree\Node\NodeTrait;

    /** @var CM_ViewResponse */
    private $_viewResponse;

    /**
     * @param CM_ViewResponse $viewResponse
     */
    public function __construct(CM_ViewResponse $viewResponse) {
        $this->_setViewResponse($viewResponse);
    }

    /**
     * @return CM_ViewResponse
     */
    public function getValue() {
        return $this->_viewResponse;
    }

    /**
     * @param CM_ViewResponse $value
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
     * @param string $className
     * @return self|null
     */
    public function getClosest($className) {
        if ($this->_viewResponse->getView() instanceof $className) {
            return $this;
        }
        if (!$this->isRoot()) {
            return $this->getParent()->getClosest($className);
        }
        return null;
    }

    /**
     * @param CM_ViewResponse $viewResponse
     */
    private function _setViewResponse(CM_ViewResponse $viewResponse) {
        $this->_viewResponse = $viewResponse;
    }
}
