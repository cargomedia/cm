<?php

class CM_Frontend_GlobalResponse {

    /** @var CM_Frontend_TreeNode|null */
    protected $_treeRoot;

    /** @var CM_Frontend_TreeNode|null */
    protected $_treeCurrent;

    /** @var CM_Frontend_JavascriptContainer */
    protected $_onloadHeaderJs;

    /** @var CM_Frontend_JavascriptContainer */
    protected $_onloadPrepareJs;

    /** @var CM_Frontend_JavascriptContainer */
    protected $_onloadJs;

    /** @var CM_Frontend_JavascriptContainer */
    protected $_onloadReadyJs;

    public function __construct() {
        $this->_onloadHeaderJs = new CM_Frontend_JavascriptContainer();
        $this->_onloadPrepareJs = new CM_Frontend_JavascriptContainer();
        $this->_onloadJs = new CM_Frontend_JavascriptContainer();
        $this->_onloadReadyJs = new CM_Frontend_JavascriptContainer();
    }

    /**
     * @param CM_Frontend_ViewResponse $viewResponse
     */
    public function treeExpand(CM_Frontend_ViewResponse $viewResponse) {
        $node = new CM_Frontend_TreeNode($viewResponse);
        if (null === $this->_treeRoot) {
            $this->_treeRoot = $node;
        } else {
            $this->getTreeCurrent()->addChild($node);
        }
        $this->_treeCurrent = $node;
    }

    public function treeCollapse() {
        if ($this->getTreeCurrent()->isRoot()) {
            $this->_treeCurrent = null;
        } else {
            $this->_treeCurrent = $this->getTreeCurrent()->getParent();
        }
    }

    /**
     * @return CM_Frontend_TreeNode
     * @throws CM_Exception_Invalid
     */
    public function getTreeCurrent() {
        if (null === $this->_treeCurrent) {
            throw new CM_Exception_Invalid('No current tree node set');
        }
        return $this->_treeCurrent;
    }

    /**
     * @throws CM_Exception_Invalid
     * @return CM_Frontend_TreeNode
     */
    public function getTreeRoot() {
        if (null === $this->_treeRoot) {
            throw new CM_Exception_Invalid('No root tree set');
        }
        return $this->_treeRoot;
    }

    /**
     * @param string $viewClassName
     * @return CM_Frontend_ViewResponse|null
     */
    public function getClosestViewResponse($viewClassName) {
        $node = $this->getTreeCurrent();
        while (!$node->getValue()->getView() instanceof $viewClassName) {
            if ($node->isRoot()) {
                return null;
            }
            $node = $node->getParent();
        };
        return $node->getValue();
    }

    public function clear() {
        $this->_onloadHeaderJs->clear();
        $this->_onloadPrepareJs->clear();
        $this->_onloadJs->clear();
        $this->_onloadReadyJs->clear();
        $this->_treeCurrent = null;
        $this->_treeRoot = null;
    }

    /**
     * @return CM_Frontend_JavascriptContainer
     */
    public function getOnloadHeaderJs() {
        return $this->_onloadHeaderJs;
    }

    /**
     * @return CM_Frontend_JavascriptContainer
     */
    public function getOnloadPrepareJs() {
        return $this->_onloadPrepareJs;
    }

    /**
     * @return CM_Frontend_JavascriptContainer
     */
    public function getOnloadJs() {
        return $this->_onloadJs;
    }

    /**
     * @return CM_Frontend_JavascriptContainer
     */
    public function getOnloadReadyJs() {
        return $this->_onloadReadyJs;
    }

    /**
     * @return string
     */
    public function getJs() {
        return $this->_getJs();
    }

    /**
     * @return string
     */
    public function getHtml() {
        $html = '<script type="text/javascript">' . PHP_EOL;
        $html .= '$(function() {' . PHP_EOL;
        $html .= $this->_getJs();
        $html .= '});' . PHP_EOL;
        $html .= '</script>' . PHP_EOL;
        return $html;
    }

    /**
     * @return string
     */
    private function _getJs() {
        $treeInitJs = new CM_Frontend_JavascriptContainer();
        $treeStoredJs = new CM_Frontend_JavascriptContainer();
        foreach ($this->_getTreeNodes() as $node) {
            $treeInitJs->append($this->_getTreeNodeInitJs($node));
            $treeStoredJs->append($this->_getTreeNodeStoredJs($node));
        }
        $operations = array(
            $this->_onloadHeaderJs->compile(),
            $treeInitJs->compile(),
            $this->_onloadPrepareJs->compile(),
            $treeStoredJs->compile(),
            $this->_onloadJs->compile(),
            $this->_onloadReadyJs->compile(),
        );
        $operations = array_filter($operations);
        $code = implode(PHP_EOL, $operations);
        return $code;
    }

    /**
     * @return CM_Frontend_TreeNode[]
     */
    private function _getTreeNodes() {
        if (!$this->_treeRoot) {
            return [];
        }
        $gather = function (CM_Frontend_TreeNode $node) use (&$gather) {
            $nodes = array($node);
            foreach ($node->getChildren() as $child) {
                $nodes = array_merge($nodes, $gather($child));
            }
            return $nodes;
        };
        return $gather($this->getTreeRoot());
    }

    /**
     * @param CM_Frontend_TreeNode $node
     * @return string
     */
    private function _getTreeNodeInitJs(CM_Frontend_TreeNode $node) {
        $viewResponse = $node->getValue();
        $reference = 'cm.views["' . $viewResponse->getAutoId() . '"]';
        $view = $viewResponse->getView();
        $code = $reference . ' = new ' . get_class($view) . '({';
        $code .= 'el:$("#' . $viewResponse->getAutoId() . '").get(0),';
        $code .= 'params:' . CM_Util::jsonEncode($view->getParams()->getParamsEncoded());
        if (!$node->isRoot()) {
            $code .= ',parent: cm.views["' . $node->getParent()->getValue()->getAutoId() . '"]';
        }
        $code .= '});';
        return $code;
    }

    /**
     * @param CM_Frontend_TreeNode $node
     * @return string
     */
    private function _getTreeNodeStoredJs(CM_Frontend_TreeNode $node) {
        $viewResponse = $node->getValue();
        return $viewResponse->getJs()->compile("cm.views['{$viewResponse->getAutoId()}']");
    }
}
