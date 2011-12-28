<?php

abstract class CM_StreamAdapter_Abstract extends CM_Class_Abstract {
	/**
	 * @return CM_StreamAdapter_Abstract
	 */
	public static function factory() {
		$className = self::_getClassName();
		return new $className();
	}

	/**
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

	/**
	 * @param string $channel
	 * @return string
	 */
	abstract public function subscribe($channel);
}
