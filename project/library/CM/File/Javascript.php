<?php

class CM_File_Javascript extends CM_File {

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
		$content[] = self::_getDoc('@type string', 1);
		$content[] = "\t_class: '" . $className . "'";
		$content[] = '});';
		$path = DIR_ROOT . 'library/' . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.js';
		return CM_File_Javascript::create($path, implode(PHP_EOL, $content));
	}

	/**
	 * @param array|string $docLines
	 * @param int|null     $indentation
	 * @return string
	 */
	private function _getDocBlock($docLines, $indentation = null) {
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
	private function _getDoc($doc, $indentation = null) {
		$indentation = str_repeat("\t", (int) $indentation);
		return $indentation . '/** ' . $doc . ' */';
	}

	public function validate() {
		$content = $this->read();
		$content = preg_replace('#(@param\s+)([^}{\s]+)(\s)#', '$1{$2}$3', $content);

		$typeReplaces = array(
			'string' => 'String',
			'null' => 'Null',
			'array' => 'Array',
			'bool' => 'Boolean',
			'boolean' => 'Boolean',
			'object' => 'Object',
			'function' => 'Function',
		);
		foreach ($typeReplaces as $existingType => $properType) {
			$content = preg_replace('#(@param\s+\{(?:[^}]+|)?)' . $existingType . '((?:|[^}]+)?\})#', '$1' . $properType . '$2', $content);
			$content = preg_replace('#(@type\s+)' . $existingType . '(\s+)#', '$1' . $properType . '$2', $content);
			$content = preg_replace('#(@return\s+)' . $existingType . '(\s+)#', '$1' . $properType . '$2', $content);
		}

		$this->write($content);
	}
}
