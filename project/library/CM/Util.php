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
	 * @param string $path	OPTIONAL
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
	 * @param string	  $cmd
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
}
