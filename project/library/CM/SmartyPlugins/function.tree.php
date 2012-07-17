<?php

function smarty_function_tree(array $params, Smarty_Internal_Template $template) {
	if (!isset($params['tree']) || !($params['tree'] instanceof CM_Tree_Abstract)) {
		throw new CM_Exception_InvalidParam('`tree` function needs `CM_Tree_Abstract` object');
	}
	/** @var $treeObject CM_Tree_Abstract */
	$treeObject = $params['tree'];
	return smarty_functionHelper_treeRenderNodes($treeObject->getRoot());
}

function smarty_functionHelper_treeRenderNodes(CM_TreeNode_Abstract $node) {
	$html = '';
	if (count($node->getNodes()) > 1 || $node->hasGrandNodes()) {
		$html .= '<ul>';
		foreach ($node->getNodes() as $child) {
			if (!is_numeric($child->getName())) {
				$html .= '<li><span class="node" data-id="' . htmlspecialchars($child->getId()) . '">' . $child->getName() . ' <span class="count">(' . count($child->getNodes()) . ')</span></span>';
				$html .= smarty_functionHelper_treeRenderNodes($child);
				$html .= '</li>';
			}
		}
		$html .= '</ul>';
	}
	return $html;
}