<?php

abstract class CM_Stream_Adapter_Message_Abstract extends CM_Stream_Adapter_Abstract {

	/**
	 * @return array
	 */
	abstract public function getOptions();

	/**
	 * Publishes data $data with the respective implemented method over a channel with the given ID $channel
	 * @param string $channel
	 * @param mixed  $data
	 */
	abstract public function publish($channel, $data);

	abstract public function startSynchronization();

	abstract public function synchronize();
}
