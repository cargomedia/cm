<?php

class CM_File_Javascript extends CM_File implements CM_File_ClassInterface {

	public function getClassName() {
		$meta = $this->getClassDeclaration();
		return $meta['class'];
	}

	public function getParentClassName() {
		$meta = $this->getClassDeclaration();
		return $meta['parent'];
	}

	public function getClassDeclaration() {
		$classRegexp = '\*\s+@class\s+(?<class>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)';
		$parentRegexp = '(?:\s+\*\s+@extends\s+(?<parent>[a-zA-Z_\x7f-\xff][.a-zA-Z0-9_\x7f-\xff]*)\s+)?';
		$regexp = '#' . $classRegexp . '[\s\n+]' . $parentRegexp . '#';
		if (!preg_match($regexp, $this->read(), $match)) {
			throw new CM_Exception('Cannot detect class');
		}
		$declaration = array();
		$declaration['class'] = $match['class'];
		$declaration['parent'] = isset($match['parent']) ? $match['parent'] : null;
		return $declaration;
	}
}
