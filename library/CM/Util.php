<?php

class CM_Util {

	/**
	 * @param int $number
	 * @return int[]
	 */
	public static function decbinarr($number) {
		$bin = decbin($number);
		$binarr = array();
		for ($i = 0; $i < strlen($bin); $i++) {
			if (substr($bin, -$i - 1, 1) == 1) {
				$binarr[] = pow(2, $i);
			}
		}
		return $binarr;
	}

	/**
	 * Return human-readable information on one line about a variable
	 *
	 * @param mixed $expression
	 * @return string
	 */
	public static function var_line($expression) {
		$line = print_r($expression, true);
		$line = str_replace(PHP_EOL, ' ', $line);
		$line = trim($line);
		return $line;
	}

	/**
	 * @param string $pattern OPTIONAL
	 * @param string $path    OPTIONAL
	 * @return array
	 */
	public static function rglob($pattern = '*', $path = './') {
		$paths = glob($path . '*', GLOB_MARK | GLOB_ONLYDIR | GLOB_NOSORT);
		$files = glob($path . $pattern);
		foreach ($paths as $path) {
			$files = array_merge($files, self::rglob($pattern, $path));
		}
		return $files;
	}

	/**
	 * @param string $pattern
	 * @param CM_Site_Abstract $site
	 * @return array
	 */
	public static function rglobLibraries($pattern, CM_Site_Abstract $site) {
		$paths = array();
		foreach ($site->getNamespaces() as $namespace) {
			$libraryPath = CM_Util::getNamespacePath($namespace) . 'library/' . $namespace . '/';
			$paths = array_merge($paths, CM_Util::rglob($pattern, $libraryPath));
		}
		return $paths;
	}

	/**
	 * @param array $array
	 * @param mixed $value
	 * @return array
	 */
	public static function array_remove(array $array, $value) {
		return array_filter($array, function ($entry) use ($value) {
			return $value != $entry;
		});
	}

	/**
	 * @param string       $className
	 * @param boolean|null $ignoreInvalid
	 * @throws CM_Exception_Invalid
	 * @return string
	 */
	public static function getNamespace($className, $ignoreInvalid = null) {
		if (null === $ignoreInvalid) {
			$ignoreInvalid = false;
		}
		$ignoreInvalid = (boolean) $ignoreInvalid;
		$className = (string) $className;
		$tail = strpbrk($className, '_\\');
		$namespace = substr($className, 0, -strlen($tail));
		if (!$namespace) {
			if ($ignoreInvalid) {
				return null;
			}
			throw new CM_Exception_Invalid('Could not detect namespace of `' . $className . '`.');
		}
		return $namespace;
	}

	/**
	 * @param string       $url
	 * @param array|null   $params
	 * @param boolean|null $methodPost
	 * @param int|null     $timeout
	 * @throws CM_Exception_Invalid
	 * @return string
	 */
	public static function getContents($url, array $params = null, $methodPost = null, $timeout = null) {
		$url = (string) $url;
		if (!empty($params)) {
			$params = http_build_query($params);
		}
		if (null === $timeout) {
			$timeout = 10;
		}
		$timeout = (int) $timeout;

		$curlConnection = curl_init();
		curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlConnection, CURLOPT_TIMEOUT, $timeout);
		if ($methodPost) {
			curl_setopt($curlConnection, CURLOPT_POST, 1);
			if (!empty($params)) {
				curl_setopt($curlConnection, CURLOPT_POSTFIELDS, $params);
			}
		} else {
			if (!empty($params)) {
				$url .= '?' . $params;
			}
		}
		curl_setopt($curlConnection, CURLOPT_URL, $url);

		$contents = curl_exec($curlConnection);
		$curlError = null;
		if ($contents === false) {
			$curlError = 'Fetching contents from `' . $url . '` failed: `' . curl_error($curlConnection) . '`';
		}
		curl_close($curlConnection);
		if ($curlError) {
			throw new CM_Exception_Invalid($curlError);
		}
		return $contents;
	}

	/**
	 * @param string $path
	 * @throws CM_Exception
	 */
	public static function mkDir($path) {
		$path = (string) $path;
		if (is_dir($path)) {
			return;
		}
		if (false === mkdir($path, 0777, true)) {
			throw new CM_Exception('Cannot mkdir `' . $path . '`.');
		}
	}

	/**
	 * @param string $path
	 * @throws CM_Exception_Invalid
	 */
	public static function rmDir($path) {
		$path = (string) $path;
		self::rmDirContents($path);
		if (!rmdir($path)) {
			throw new CM_Exception_Invalid('Could not delete directory `' . $path . '`');
		}
	}

	/**
	 * @param string $path
	 * @throws CM_Exception_Invalid
	 */
	public static function rmDirContents($path) {
		$path = (string) $path;
		foreach (glob($path . '*') as $file) {
			if (is_dir($file)) {
				self::rmDir($file . '/');
			} else {
				if (!unlink($file)) {
					throw new CM_Exception_Invalid('Could not delete file `' . $file . '`');
				}
			}
		}
	}

	/**
	 * @param string  $path
	 * @param array   $params Query parameters
	 * @return string
	 */
	public static function link($path, array $params = null) {
		$link = $path;

		if (!empty($params)) {
			$params = CM_Params::encode($params);
			$query = http_build_query($params);
			$link .= '?' . $query;
		}

		return $link;
	}

	/**
	 * @param string $string
	 * @param int    $quote_style
	 * @param string $charset
	 * @return string
	 */
	public static function htmlspecialchars($string, $quote_style = ENT_COMPAT, $charset = 'UTF-8') {
		return htmlspecialchars($string, $quote_style, $charset);
	}

	/**
	 * @param string[] $paths
	 * @return array[]
	 * @throws CM_Exception
	 */
	public static function getClasses(array $paths) {
		$classes = array();
		foreach ($paths as $path) {
			$file = CM_File::factory($path);
			$meta = $file->getMeta();
			$classes[$meta['class']] = array('parent' => $meta['parent'], 'path' => $path);
		}

		$paths = array();
		while (count($classes)) {
			foreach ($classes as $class => $data) {
				if (!isset($classes[$data['parent']])) {
					$paths[$data['path']] = $class;
					unset($classes[$class]);
				}
			}
		}
		return $paths;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public static function camelize($string) {
		return preg_replace('/[-_]([a-z])/e', 'strtoupper("$1")', ucfirst(strtolower($string)));
	}

	/**
	 * @param string        $string
	 * @param string|null   $separator
	 * @return string
	 */
	public static function uncamelize($string, $separator = null) {
		if (null === $separator) {
			$separator = '-';
		}
		return strtolower(preg_replace('/([A-Z])/', $separator . '\1', lcfirst($string)));
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public static function titleize($string) {
		return preg_replace('/[-_]([a-z])/e', 'strtoupper(" $1")', ucfirst(strtolower($string)));
	}

	/**
	 * @param string    $namespace
	 * @param bool|null $relative
	 * @return string
	 */
	public static function getNamespacePath($namespace, $relative = null) {
		$path = CM_Bootloader::getInstance()->getNamespacePath($namespace);
		if (!$relative) {
			$path = DIR_ROOT . $path;
		}
		return $path;
	}

	/**
	 * @param string $pathRelative
	 * @return CM_File[]
	 */
	public static function getResourceFiles($pathRelative) {
		$pathRelative = (string) $pathRelative;
		$paths = array();
		foreach (CM_Bootloader::getInstance()->getNamespaces() as $namespace) {
			$paths[] = CM_Util::getNamespacePath($namespace) . 'resources/' . $pathRelative;
		}
		$paths[] = DIR_ROOT . 'resources/' . $pathRelative;

		$files = array();
		foreach (array_unique($paths) as $path) {
			if (CM_File::exists($path)) {
				$files[] = new CM_File($path);
			}
		}
		return $files;
	}

	/**
	 * @param string      $command
	 * @param array|null  $args
	 * @param string|null $input
	 * @param string|null $inputPath
	 * @throws CM_Exception
	 * @return string Output
	 */
	public static function exec($command, array $args = null, $input = null, $inputPath = null) {
		if (null === $args) {
			$args = array();
		}
		foreach ($args as $arg) {
			if (!strlen($arg)) {
				throw new CM_Exception('Empty argument');
			}
			$command .= ' ' . escapeshellarg($arg);
		}
		if ($inputPath) {
			$command .= ' <' . escapeshellarg($inputPath);
		}
		return self::_exec($command, $input);
	}

	/**
	 * @static
	 * @param $command
	 * @param $stdin
	 * @return string
	 * @throws CM_Exception
	 */
	private static function _exec($command, $stdin) {
		$descriptorSpec = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("pipe", "w"));
		$process = proc_open($command, $descriptorSpec, $pipes);
		if (!is_resource($process)) {
			throw new CM_Exception('Cannot open command file pointer to `' . $command . '`');
		}

		if ($stdin) {
			fwrite($pipes[0], $stdin);
		}
		fclose($pipes[0]);

		$stdout = stream_get_contents($pipes[1]);
		fclose($pipes[1]);

		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[2]);

		$returnStatus = proc_close($process);
		if ($returnStatus != 0) {
			throw new CM_Exception('Command `' . $command . '` failed. STDERR: `' . trim($stderr) . '` STDOUT: `' . trim($stdout) . '`.');
		}
		return $stdout;
	}

	/**
	 * @param string|null $namespace
	 * @return string
	 */
	public static function benchmark($namespace = null) {
		static $times;
		if (!$times) {
			$times = array();
		}
		$now = microtime(true) * 1000;
		$previousValue = null;
		if ($times[$namespace]) {
			$difference = $now - $times[$namespace];
		} else {
			$difference = null;
		}
		$times[$namespace] = $now;
		return sprintf('%.2f ms', $difference);

	}

	/**
	 * @param string       $className
	 * @param boolean|null $includeAbstracts
	 * @return string[]
	 */
	public static function getClassChildren($className, $includeAbstracts = null) {
		$key = CM_CacheConst::ClassChildren . '_className:' . $className . '_abstracts:' . (int) $includeAbstracts;
		if (false === ($classNames = CM_CacheLocal::get($key))) {
			$pathsFiltered = array();
			$paths = array();
			foreach (CM_Bootloader::getInstance()->getNamespaces() as $namespace) {
				$namespacePaths = CM_Util::rglob('*.php', CM_Util::getNamespacePath($namespace) . 'library/');
				sort($namespacePaths);
				$paths = array_merge($paths, $namespacePaths);
			}
			$regexp = '#\bclass\s+(?<name>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s+#';
			foreach ($paths as $path) {
				$file = new CM_File($path);
				$fileContents = $file->read();
				if (preg_match($regexp, $fileContents, $matches)) {
					if (class_exists($matches['name'], true)) {
						$reflectionClass = new ReflectionClass($matches['name']);
						if (($reflectionClass->isSubclassOf($className) ||
								interface_exists($className) && $reflectionClass->implementsInterface($className)) &&
								(!$reflectionClass->isAbstract() || $includeAbstracts)
						) {
							$pathsFiltered[] = $path;
						}
					}
				}
			}
			$classNames = self::getClasses($pathsFiltered);
			CM_CacheLocal::set($key, $classNames);
		}
		return $classNames;

	}
}
