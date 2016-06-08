<?php

class CM_Model_StreamChannel_Message_Model extends CM_Model_StreamChannel_Message {

    const SALT = 'wd&swjflq74daZnmSZ6nWoERO3yPC7ha';

    public function onPublish(CM_Model_Stream_Publish $streamPublish) {
    }

    public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
    }

    public function onUnpublish(CM_Model_Stream_Publish $streamPublish) {
    }

    public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
    }

    /**
     * @param string     $event
     * @param mixed|null $data
     */
    public function notify($event, $data = null) {
        $this->_publish($event, $data);
    }

    /**
     * @param int $modelType
     * @return self
     */
    public static function create($modelType) {
        return self::createStatic([
            'key'         => self::getKeyByModelType($modelType),
            'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic(),
        ]);
    }

    /**
     * @param int $modelType
     * @return string
     */
    public static function getKeyByModelType($modelType) {
        return self::_encryptKey($modelType, self::SALT);
    }
}
