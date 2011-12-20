<?php

abstract class CM_Stream_Abstract {

	/**
	 * Publishes data $data with the respective implemented method over a channel with the given ID $channel 
	 * @param string $channel
	 * @param mixed $data
	 */
	abstract public function publish($channel, $data);

	/**
	 * @param string $channel
	 * @return string
	 */
	abstract public function subscribe($channel);
}
