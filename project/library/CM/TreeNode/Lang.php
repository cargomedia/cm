<?php

class CM_TreeNode_Lang extends CM_TreeNode_Abstract {
	public function section($name) {
		return $this->getNode($name);
	}

	public function key_exists($key) {
		return $this->hasLeaf($key);
	}

	public function cdata($key) {
		return $this->getLeaf($key);
	}

	public function text($key, array $vars = null) {
		$text = $this->cdata($key);

		if (strpos($text, '{') !== false) {
			$text = CM_Language::exec($text, $vars);
		}

		return $text;
	}
}
