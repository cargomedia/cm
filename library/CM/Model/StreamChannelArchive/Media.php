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
     * @return bool
     */
    public function hasFile() {
        return $this->_has('file') && null !== $this->_get('file');
    }

    /**
     * @param CM_File_UserContent|null $file
     */
    public function setFile(CM_File_UserContent $file = null) {
        $filename = null !== $file ? $file->getFileName() : null;
        CM_Db_Db::update('cm_streamChannelArchive_media', ['file' => $filename], ['id' => $this->getId()]);
        $this->_change();
    }

    /**
     * @return CM_File_UserContent
     * @throws CM_Exception_Invalid
     */
    public function getFile() {
        if (null === $this->_get('file')) {
            throw new CM_Exception_Invalid('File does not exist');
        }
        return new CM_File_UserContent('streamChannels', $this->_get('file'), $this->getId());
    }

    /**
     * @return string
     */
    public function getHash() {
        return (string) $this->_get('hash');
    }

    /**
     * @return string
     */
    public function getKey() {
        return (string) $this->_get('key');
    }

    /**
     * @return string|null
     */
    public function getMediaId() {
        $mediaId = $this->_get('mediaId');
        $mediaId = (null !== $mediaId) ? (string) $mediaId : null;
        return $mediaId;
    }

    /**
     * @return int
     */
    public function getStreamChannelType() {
        return (int) $this->_get('streamChannelType');
    }

    /**
     * @return CM_StreamChannel_ThumbnailList_Channel
     */
    public function getThumbnails() {
        return new CM_StreamChannel_ThumbnailList_Channel($this->getId());
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
     * @return array
     */
    protected function _loadData() {
        return CM_Db_Db::select('cm_streamChannelArchive_media', '*', array('id' => $this->getId()))->fetch();
    }

    protected function _onDeleteBefore() {
        if ($this->hasFile()) {
            $this->getFile()->delete();
        }

        $thumbnailDir = new CM_File_UserContent('streamChannels', $this->getId() . '-thumbs', $this->getId());
        $thumbnailDir->delete(true);
        /** @var CM_StreamChannel_Thumbnail $thumbnail */
        foreach ($this->getThumbnails() as $thumbnail) {
            $thumbnail->delete();
        }
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
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = $data['streamChannel'];
        $createStamp = $streamChannel->getCreateStamp();
        $userId = null;
        if ($streamChannel->hasStreamPublish()) {
            $streamPublish = $streamChannel->getStreamPublish();
            $createStamp = $streamPublish->getStart();
            $userId = $streamPublish->getUserId();
        }
        $file = isset($data['file']) ? $data['file'] : null;
        $end = time();
        $duration = $end - $createStamp;
        CM_Db_Db::insert('cm_streamChannelArchive_media', [
            'id'                => $streamChannel->getId(),
            'userId'            => $userId,
            'duration'          => $duration,
            'hash'              => $streamChannel->getHash(),
            'file'              => $file,
            'streamChannelType' => $streamChannel->getType(),
            'createStamp'       => $createStamp,
            'key'               => $streamChannel->getKey(),
            'mediaId'           => $streamChannel->getMediaId(),
        ]);
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

    /**
     * @param string $mediaId
     * @return CM_Model_StreamChannelArchive_Media|null
     */
    public static function findByMediaId($mediaId) {
        $streamChannelArchiveId = CM_Db_Db::select('cm_streamChannelArchive_media', 'id', ['mediaId' => (string) $mediaId])->fetchColumn();
        if (!$streamChannelArchiveId) {
            return null;
        }
        return new self($streamChannelArchiveId);
    }
}
