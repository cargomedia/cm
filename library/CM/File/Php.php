<?php

class CM_File_Php extends CM_File implements CM_File_ClassInterface {

	public function getClassName() {
		$meta = $this->getClassDeclaration();
		return $meta['class'];
	}

	public function getParentClassName() {
		$meta = $this->getClassDeclaration();
		return $meta['parent'];
	}

	public function getClassDeclaration() {
		$regexp = '#\bclass\s+(?<class>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s+#';
		if (!preg_match($regexp, $this->read(), $match)) {
			throw new CM_Exception('Cannot detect class');
		}
		$class = $match['class'];
		$parent = get_parent_class($class) ? : null;
		return array('class' => $class, 'parent' => $parent);
	}
}
