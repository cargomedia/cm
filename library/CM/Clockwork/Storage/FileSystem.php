<?php

class CM_Clockwork_Storage_FileSystem extends CM_Clockwork_Storage_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    protected function _load() {
        $data = [];
        $file = $this->_getStorageFile();
        if ($file->exists()) {
            $values = (array) CM_Params::jsonDecode($file->read());
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
        if (!$file->exists()) {
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
