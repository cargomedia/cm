<?php

class CM_Clockwork_Storage_FileSystem extends CM_Clockwork_Storage_Abstract {

    const FOLDER_NAME = 'clockwork';

    protected function _load() {
        $data = [];
        $file = $this->_getFile();
        if ($file->getExists()) {
            $values = CM_Params::decode($file->read(), true);
            $data = \Functional\map($values, function ($timeStamp) {
                return new DateTime('@' . $timeStamp);
            });
        }
        return $data;
    }

    protected function _save(array $data) {
        $values = \Functional\map($data, function (DateTime $dateTime) {
            return $dateTime->getTimestamp();
        });
        $content = CM_Params::encode($values, true);
        $file = $this->_getFile();
        if (!$file->getExists()) {
            $filesystem = $this->_getFileSystem();
            $filesystem->ensureDirectory(self::FOLDER_NAME);
            CM_File::create($file->getPath(), $content, $filesystem);
        } else {
            $file->write($content);
        }
    }

    /**
     * @return CM_File
     */
    private function _getFile() {
        return new CM_File(self::FOLDER_NAME . DIRECTORY_SEPARATOR . $this->_context . '.json', $this->_getFileSystem());
    }

    /**
     * @return CM_File_Filesystem
     */
    private function _getFileSystem() {
        return CM_Service_Manager::getInstance()->getFilesystems()->getData();
    }
}
