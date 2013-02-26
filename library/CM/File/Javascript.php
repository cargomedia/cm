<?php

class CM_File_Javascript extends CM_File implements CM_File_ClassInterface {

	/**
	 * @return string
	 */
	public function getClassName() {
		$meta = $this->getClassDeclaration();
		return $meta['class'];
	}

	/**
	 * @return string
	 */
	public function getParentClassName() {
		$meta = $this->getClassDeclaration();
		return $meta['parent'];
	}

	/**
	 * @return array
	 * @throws CM_Exception
	 */
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

	/**
	 * @param $className
	 * @return CM_File_Javascript
	 */
	public static function createLibraryClass($className) {
		$parentClass = get_parent_class($className);
		if (!$parentClass || $parentClass === 'CM_Class_Abstract') {
			$parentClass = 'Backbone.View';
		}
		$content = array();
		$content[] = self::_getDocBlock(array('class' => $className, 'extends' => $parentClass));
		$content[] = 'var ' . $className . ' = ' . $parentClass . '.extend({';
		$content[] = '';
		$content[] = self::_getDoc('@type String', 1);
		$content[] = "\t_class: '" . $className . "'";
		$content[] = '});';
		$content[] = '';
		$path = CM_Util::getNamespacePath(CM_Util::getNamespace($className)) . 'library/' . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.js';
		return CM_File_Javascript::create($path, implode(PHP_EOL, $content));
	}

	/**
	 * @param array|string $docLines
	 * @param int|null     $indentation
	 * @return string
	 */
	private static function _getDocBlock($docLines, $indentation = null) {
		$docLines = (array) $docLines;
		$indentation = (int) $indentation;
		$docBlock = '/**' . PHP_EOL;
		foreach ($docLines as $param => $value) {
			$docBlock .= str_repeat("\t", $indentation);
			$docBlock .= ' * ';
			if (is_string($param)) {
				$docBlock .= '@' . $param . ' ';
			}
			$docBlock .= $value;
			$docBlock .= PHP_EOL;
		}
		$docBlock .= ' */';
		return $docBlock;
	}

	/**
	 * @param string   $doc
	 * @param int|null $indentation
	 * @return string
	 */
	private static function _getDoc($doc, $indentation = null) {
		$indentation = str_repeat("\t", (int) $indentation);
		return $indentation . '/** ' . $doc . ' */';
	}
}
