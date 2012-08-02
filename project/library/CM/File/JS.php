<?php

class CM_File_JS extends CM_File {

	/**
	 * @param $className
	 * @return CM_File_JS
	 */
	public static function createLibraryClass($className) {
		$path = DIR_ROOT . 'library/' . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.js';
		touch($path);
		$file = new self($path);
		$file->wrapWithClassDeclaration($className);
		$file->validate();
		return $file;
	}

	/**
	 * @param $className
	 * @return string
	 */
	public function hasClassDeclaration($className) {
		return strstr($this->read(), '@class ' . $className);
	}

	/**
	 * @param string $className
	 */
	public function wrapWithClassDeclaration($className) {
		$parentClass = get_parent_class($className);
		if (!$parentClass || $parentClass === 'CM_Class_Abstract') {
			$parentClass = 'Backbone.View';
		}

		// Wrapper start
		$content = array();
		$content[] = $this->_createDocBlock(array('class' => $className, 'extends' => $parentClass));
		$content[] = 'var ' . $className . ' = ' . $parentClass . '.extend({';
		$content[] = '';
		$content[] = $this->_createDoc('@type string', 1);
		$content[] = "\t_class: '" . $className . "',";

		// Getting and formatting content
		if ($fileContent = trim($this->read())) {
			$lines = preg_split('#[\n\r]#', $fileContent);
			$lines = array_map(function ($input) {
				return "\t" . $input;
			}, $lines);
			array_unshift($lines, '');
			$content = array_merge($content, $lines);
		}
		$content[] = trim(array_pop($content), ',');

		// Wrapper end
		$content[] = '});';
		$this->write(implode(PHP_EOL, $content));
	}

	/**
	 * @param array|string $docLines
	 * @param int|null     $indentation
	 * @return string
	 */
	private function _createDocBlock($docLines, $indentation = null) {
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
	private function _createDoc($doc, $indentation = null) {
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
