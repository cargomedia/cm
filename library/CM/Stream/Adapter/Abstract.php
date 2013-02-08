<?php

abstract class CM_Stream_Adapter_Abstract extends CM_Class_Abstract {
	/**
	 * @return CM_Stream_Adapter_Abstract
	 */
	public static function factory() {
		$className = self::_getClassName();
		return new $className();
	}

	/**
	 * @throws CM_Exception_Invalid
	 * @return array ('host', 'port')
	 */
	public static function getServer() {
		$servers = self::_getConfig()->servers;
		if (empty($servers)) {
			throw new CM_Exception_Invalid('No servers configured');
		}
		$server = $servers[array_rand($servers)];
		if (self::_getConfig()->hostPrefix) {
			$server['host'] = rand(1, 9999) . '.' . $server['host'];
		}
		return $server;
	}

	/**
	 * Publishes data $data with the respective implemented method over a channel with the given ID $channel
	 * @param string $channel
	 * @param mixed  $data
	 */
	abstract public function publish($channel, $data);

	abstract public function runSynchronization();
}
