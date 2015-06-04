<?php

abstract class CM_MessageStream_Adapter_Abstract extends CM_Stream_Adapter_Abstract {

    /**
     * @return array
     */
    abstract public function getOptions();

    /**
     * @param string $channel
     * @param string $event
     * @param mixed  $data
     */
    abstract public function publish($channel, $event, $data);

    abstract public function startSynchronization();

    abstract public function synchronize();
}
