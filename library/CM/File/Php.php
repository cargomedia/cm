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

	/**
	 * @param string      $className
	 * @param string|null $parentClass
	 * @return CM_File_Php
	 */
	public static function createLibraryClass($className, $parentClass = null) {
		if (!$parentClass) {
			$parentClass = 'CM_Class_Abstract';
		}
		$content = array();
		$content[] = '<?php';
		$content[] = '';
		$content[] = 'class ' . $className . ' extends ' . $parentClass . ' {';
		$content[] = '';
		$content[] = '}';
		$content[] = '';
		$path = CM_Util::getNamespacePath(CM_Util::getNamespace($className)) . 'library/' . str_replace('_', DIRECTORY_SEPARATOR, $className) .
				'.php';
		CM_Util::mkDir(dirname($path));
		return CM_File_Php::create($path, implode(PHP_EOL, $content));
	}

	/**
	 * @param string      $access
	 * @param string|null $name
	 * @param array|null  $parameters
	 * @param string|null $body
	 */
	public function setMethod($access, $name, array $parameters = null, $body = null) {
		$reflection = $this->_getReflection();
		$code = $this->_generateMethodCode($access, $name, $parameters, $body);
		$lines = preg_split('#[\n\r]#', $this->read());
		if ($reflection->hasMethod($name)) {
			$method = $reflection->getMethod($name);
		}

		if (isset($method) && $method->getDeclaringClass() == $reflection) {
			$start = $method->getStartLine() - 1;
			$length = $method->getEndLine() - $start;
		} else {
			$code = PHP_EOL . $code;
			$start = array_search('}', $lines);
			$length = 0;
		}
		array_splice($lines, $start, $length, $code);
		$this->write(implode("\n", $lines));
	}

	/**
	 * @param string $className
	 * @param string $methodName
	 */
	public function setMethodFrom($className, $methodName) {
		$method = new ReflectionMethod($className, $methodName);
		$visibility = 'public';
		if ($method->isPrivate()) {
			$visibility = 'private';
		}
		if ($method->isProtected()) {
			$visibility = 'protected';
		}

		$parameters = array();
		foreach ($method->getParameters() as $parameter) {
			$type = null;
			if ($parameter->isArray()) {
				$type = 'array';
			}
			if ($parameter->getClass()) {
				$type = $parameter->getClass()->getName();
			}
			$parameters[$parameter->getName()] = $type;
		}
		$this->setMethod($visibility, $method->getName(), $parameters);
	}

	/**
	 * @param string $name
	 * @return boolean
	 */
	public function hasMethod($name) {
		return (bool) preg_match('#function\s+' . preg_quote($name) . '\s*\(#', $this->read());
	}

	/**
	 * @param string      $access
	 * @param string      $methodName
	 * @param array|null  $parameters
	 * @param string|null $body
	 * @throws CM_Exception_Invalid
	 * @return string
	 */
	private function _generateMethodCode($access, $methodName, array $parameters = null, $body = null) {
		$parametersCode = null;
		if ($parameters) {
			foreach ($parameters as $name => $type) {
				if (!is_string($name)) {
					throw new CM_Exception_Invalid('Parameter name needs to be string type');
				}
				if (!in_array(strtolower($type), array('null', 'int', 'integer', 'string', 'float', 'bool', 'boolean'))) {
					$parametersCode .= $type . ' ';
				}
				$parametersCode .= '$' . $name . ', ';
			}
			$parametersCode = trim($parametersCode, ' ,');
		}
		if ($body) {
			$body = preg_replace(array('/[\n\r]/', '/^/'), "$0\t\t", $body);
		}
		$code = "\t" . $access . ' function ' . $methodName . ' (' . $parametersCode . ') {' . PHP_EOL;
		$code .= $body . PHP_EOL;
		$code .= "\t}";

		return $code;
	}

	/**
	 * @return ReflectionClass
	 */
	private function _getReflection() {
		$className = $this->getClassName();
		$content = $this->read();
		$id = md5($content);
		$tmpClassName = $className . $id;
		if (!class_exists($tmpClassName, false)) {
			$path = DIR_TMP . $id;
			$content = preg_replace('#class\s+' . $className . '#', '\0' . $id, $content);
			$file = CM_File_Php::create($path, $content);
			require $file->getPath();
			$file->delete();
		}
		return new ReflectionClass($tmpClassName);
	}
}
