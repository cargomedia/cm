<?php

class CM_Clockwork_PersistenceAdapter_File extends CM_Clockwork_PersistenceAdapter_Abstract {

    const FOLDER_NAME = 'clockwork';

    const TIME_FORMAT = 'Y-m-d H:i:s';

    public function load($context) {
        $file = $this->_getFile($context);
        if (!$file->getExists()) {
            return array();
        }
        $values = CM_Params::decode($file->read(), true);
        return \Functional\map($values, function ($dateString) {
            return new DateTime($dateString);
        });
    }

    public function save($context, array $data) {
        $values = \Functional\map($data, function (DateTime $dateTime) {
            return $dateTime->format(self::TIME_FORMAT);
        });
        $content = CM_Params::encode($values, true);
        $file = $this->_getFile($context);
        if (!$file->getExists()) {
            $filesystem = $this->_getFileSystem();
            $filesystem->ensureDirectory(self::FOLDER_NAME);
            CM_File::create($file->getPath(), $content, $filesystem);
            return;
        }
        $file->write($content);
    }

    /**
     * @param string $context
     * @return CM_File
     */
    private function _getFile($context) {
        return new CM_File(self::FOLDER_NAME . DIRECTORY_SEPARATOR . $context . '.json', $this->_getFileSystem());
    }

    /**
     * @return CM_File_Filesystem
     */
    private function _getFileSystem() {
        return CM_Service_Manager::getInstance()->getFilesystems()->getData();
    }
}
