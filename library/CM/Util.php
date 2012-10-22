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
		$regexp = '#class\s+(?<name>.+?)\s+(extends\s+(?<parent>.+?))?\s*{#';

		// Detect class names and parents
		foreach ($paths as $path) {
			$file = new CM_File($path);

			if (!preg_match($regexp, $file->read(), $match)) {
				throw new CM_Exception('Cannot detect php-class inheritance of `' . $path . '`');
			}

			$classHierarchy = array_values(class_parents($match['name']));
			array_unshift($classHierarchy, $match['name']);

			$classes[] = array('classNames' => $classHierarchy, 'path' => $path);
		}

		// Order classes by inheritance
		for ($i1 = 0; $i1 < count($classes); $i1++) {
			$class1 = $classes[$i1];
			for ($i2 = $i1 + 1; $i2 < count($classes); $i2++) {
				$class2 = $classes[$i2];
				if (isset($class1['classNames'][1]) && $class1['classNames'][1] == $class2['classNames'][0]) {
					$tmp = $classes[$i1];
					$classes[$i1] = $classes[$i2];
					$classes[$i2] = $tmp;
					$i1--;
					break;
				}
			}
		}

		$classesAssociative = array();
		foreach ($classes as $classInfo) {
			$classesAssociative[$classInfo['path']] = $classInfo['classNames'][0];
		}
		return $classesAssociative;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public static function camelize($string) {
		return preg_replace('/[-_]([a-z])/e', 'strtoupper("$1")', ucfirst(strtolower($string)));
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public static function titleize($string) {
		return preg_replace('/[-_]([a-z])/e', 'strtoupper(" $1")', ucfirst(strtolower($string)));
	}

	/**
	 * @return string[]
	 */
	public static function getNamespaces() {
		$namespaces = array();
		foreach (CM_Site_Abstract::getClassChildren() as $siteClassName) {
			/** @var $site CM_Site_Abstract */
			$site = new $siteClassName();
			$namespaces = array_merge($namespaces, $site->getNamespaces());
		}
		return array_unique($namespaces);
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
	static public function benchmark($namespace = null) {
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
}
