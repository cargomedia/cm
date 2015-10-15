<?php

class CM_Model_StreamChannelArchive_Media extends CM_Model_StreamChannelArchive_Abstract {

    /**
     * @return int
     */
    public function getCreated() {
        return (int) $this->_get('createStamp');
    }

    /**
     * @return int
     */
    public function getDuration() {
        return (int) $this->_get('duration');
    }

    /**
     * @return CM_File_UserContent
     */
    public function getVideo() {
        $filename = $this->getId() . '-' . $this->getHash() . '-original.mp4';
        return new CM_File_UserContent('streamChannels', $filename, $this->getId());
    }

    /**
     * @return string
     */
    public function getHash() {
        return (string) $this->_get('hash');
    }

    /**
     * @return int
     */
    public function getStreamChannelType() {
        return (int) $this->_get('streamChannelType');
    }

    /**
     * @return int
     */
    public function getThumbnailCount() {
        $params = $this->_getDataColumn();
        return $params->getInt('thumbnailCount', 0);
    }

    /**
     * @param int $index
     * @return CM_File_UserContent
     */
    public function getThumbnail($index) {
        $index = (int) $index;
        $filename = $this->getId() . '-' . $this->getHash() . '-thumbs' . DIRECTORY_SEPARATOR . $index . '.png';
        return new CM_File_UserContent('streamChannels', $filename, $this->getId());
    }

    /**
     * @return CM_Paging_FileUserContent_StreamChannelArchiveMediaThumbnails
     */
    public function getThumbnails() {
        return new CM_Paging_FileUserContent_StreamChannelArchiveMediaThumbnails($this);
    }

    /**
     * @return CM_Model_User|null
     */
    public function getUser() {
        $userId = $this->getUserId();
        if (null === $userId) {
            return null;
        }
        try {
            return CM_Model_User::factory($userId);
        } catch (CM_Exception_Nonexistent $ex) {
            return null;
        }
    }

    /**
     * @return int|null
     */
    public function getUserId() {
        $userId = $this->_get('userId');
        if (null === $userId) {
            return null;
        }
        return (int) $userId;
    }

    /**
     * @return CM_Params
     */
    protected function _getDataColumn() {
        if (!$this->_has('data')) {
            return CM_Params::factory();
        } else {
            return CM_Params::factory(CM_Params::jsonDecode($this->_get('data')));
        }
    }

    /**
     * @return array
     */
    protected function _loadData() {
        return CM_Db_Db::select('cm_streamChannelArchive_media', '*', array('id' => $this->getId()))->fetch();
    }

    protected function _onDeleteBefore() {
        $this->getVideo()->delete();

        $thumbnailDir = new CM_File_UserContent('streamChannels', $this->getId() . '-' . $this->getHash() . '-thumbs/', $this->getId());
        $thumbnailDir->delete(true);
    }

    protected function _onDelete() {
        CM_Db_Db::delete('cm_streamChannelArchive_media', array('id' => $this->getId()));
    }

    /**
     * @param int $id
     * @return null|static
     */
    public static function findById($id) {
        if (!CM_Db_Db::count('cm_streamChannelArchive_media', array('id' => $id))) {
            return null;
        }
        return new static($id);
    }

    protected static function _createStatic(array $data) {
        /** @var CM_Model_StreamChannel_Video $streamChannel */
        $streamChannel = $data['streamChannel'];
        $streamPublish = $streamChannel->getStreamPublish();
        $createStamp = $streamPublish->getStart();
        $thumbnailCount = $streamChannel->getThumbnailCount();
        $end = time();
        $duration = $end - $createStamp;
        CM_Db_Db::insert('cm_streamChannelArchive_media', array(
            'id'                => $streamChannel->getId(),
            'userId'            => $streamPublish->getUserId(),
            'duration'          => $duration,
            'data'              => CM_Params::jsonEncode(['thumbnailCount' => $thumbnailCount]),
            'hash'              => $streamChannel->getHash(),
            'streamChannelType' => $streamChannel->getType(), 'createStamp' => $createStamp,
        ));
        return new self($streamChannel->getId());
    }

    /**
     * @param int $age
     * @param int $streamChannelType
     */
    public static function deleteOlder($age, $streamChannelType) {
        $age = (int) $age;
        $streamChannelType = (int) $streamChannelType;
        $ageMax = time() - $age - 1;
        $streamChannelArchives = new CM_Paging_StreamChannelArchiveMedia_Type($streamChannelType, $ageMax);
        /** @var CM_Model_StreamChannelArchive_Media $streamChannelArchive */
        foreach ($streamChannelArchives as $streamChannelArchive) {
            $streamChannelArchive->delete();
        }
    }
}
