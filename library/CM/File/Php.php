<?php

class CM_File_Php extends CM_File {

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
		$path = CM_Util::getNamespacePath(CM_Util::getNamespace($className)) . 'library/' . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		CM_Util::mkDir(dirname($path));
		return CM_File_Php::create($path, implode(PHP_EOL, $content));
	}

	/**
	 * @param string       $access
	 * @param string       $name
	 * @param array|null   $parameters
	 * @param string|null  $body
	 */
	public function addMethod($access, $name, array $parameters = null, $body = null) {
		$parametersCode = null;
		if ($parameters) {
			foreach ($parameters as $name => $type) {
				if (!in_array(strtolower($type), array(null, 'int', 'integer', 'string', 'float', 'bool', 'boolean'))) {
					$parametersCode .= $type . ' ';
				}
				$parametersCode .= '$' . $name . ', ';
			}
			$parametersCode = trim($parametersCode, ' ,');
		}
		if ($body) {
			$body = preg_replace(array('/[\n\r]/', '/^/'), "$0\t\t", $body);
		}
		$code = "\t" . $access . ' function ' . $name . ' (' . $parametersCode . ') {' . PHP_EOL;
		$code .= $body . PHP_EOL;
		$code .= "\t}" . PHP_EOL . PHP_EOL;

		$content = $this->read();
		$position = strripos($content, '}');
		$content = substr($content, 0, $position) . $code . substr($content, $position);
		$this->write($content);
	}

	/**
	 * @param ReflectionMethod $method
	 */
	public function copyMethod(ReflectionMethod $method) {
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
		$this->addMethod($visibility, $method->getName(), $parameters);
	}
}
