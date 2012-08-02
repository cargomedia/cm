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
		return array_filter($array, function($entry) use ($value) {
			return $value != $entry;
		});
	}

	/**
	 * @param string      $cmd
	 * @param array|null  $args
	 * @param string|null $inputPath
	 * @return string Output
	 * @throws CM_Exception If return-status != 0
	 */
	public static function exec($cmd, array $args = null, $inputPath = null) {
		if (null === $args) {
			$args = array();
		}
		foreach ($args as $arg) {
			if (!strlen($arg)) {
				throw new CM_Exception('Empty argument');
			}
			$cmd .= ' ' . escapeshellarg($arg);
		}
		if ($inputPath) {
			$cmd .= ' <' . escapeshellarg($inputPath);
		}
		exec($cmd, $output, $returnStatus);
		$output = implode(PHP_EOL, $output);
		if ($returnStatus != 0) {
			throw new CM_Exception('Command `' . $cmd . '` failed: `' . $output . '`');
		}
		return $output;
	}

	/**
	 * @param string       $url
	 * @param array|null   $params
	 * @param boolean|null $methodPost
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	public static function getContents($url, array $params = null, $methodPost = null) {
		$url = (string) $url;
		if (!empty($params)) {
			$url .= '?' . http_build_query($params);
		}

		$context = null;
		if ($methodPost) {
			$opts = array('http' => array('method' => 'POST'));
			$context = stream_context_create($opts);
		}

		$contents = @file_get_contents($url, null, $context);
		if ($contents === false) {
			throw new CM_Exception_Invalid('Fetching contents from `' . $url . '` failed.');
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
	 * @param string     $className
	 * @param array|null $libraryDirectories
	 * @return string[]
	 */
	public static function getClassChildren($className, array $libraryDirectories = null) {
		if (!$libraryDirectories) {
			$libraryDirectories = array(DIR_LIBRARY);
		}
		$classes = array();
		foreach ($libraryDirectories as $directory) {
			$paths = CM_Util::rglob('*.php', $directory);
			foreach ($paths as $path) {
				$file = new CM_File($path);
				$regexp = '#class\s+(?<name>.+?)\b#';
				if (preg_match($regexp, $file->read(), $matches)) {
					if (class_exists($matches['name'], true) && is_subclass_of($matches['name'], $className)) {
						$classes[] = $matches['name'];
					}
				}
			}
		}
		return $classes;
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
	 * @param string $string
	 * @return string
	 */
	public static function camelize($string) {
		return preg_replace('/[-_\s]([a-z])/e', 'strtoupper("$1")', ucfirst(strtolower($string)));
	}
}
