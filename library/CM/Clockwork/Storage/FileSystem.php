<?php

class CM_Clockwork_Storage_FileSystem extends CM_Clockwork_Storage_Abstract {

    protected function _load() {
        $data = [];
        $file = $this->_getStorageFile();
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
        $content = CM_Params::jsonEncode($values, true);
        $file = $this->_getStorageFile();
        if (!$file->getExists()) {
            $file->ensureParentDirectory();
        }
        $file->write($content);
    }

    /**
     * @return CM_File
     */
    private function _getStorageFile() {
        $filename = md5($this->_context);
        return new CM_File("clockwork/{$filename}.json", $this->getServiceManager()->getFilesystems()->getData());
    }
}
