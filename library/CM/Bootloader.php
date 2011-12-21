<?php

class CM_Bootloader {

	public function autoloader() {
		spl_autoload_register(array(__CLASS__, '_cmAutoload'));
	}

	protected function _cmAutoload($className) {
		$filename = str_replace('_', '/', $className) . '.php';
		foreach (array(DIR_ROOT . 'internals/', DIR_ROOT . 'library/') as $dir) {
			if (is_file($dir . $filename)) {
				require_once $dir . $filename;
				return;
			}
		}
	}

	public static function load(array $items) {
		foreach ($items as $function) {
			$object = new static();

			if (!method_exists($object, $function)) {
				throw new Exception('Not existing function ' . $function);
			}

			$object->$function();
		}
	}
}