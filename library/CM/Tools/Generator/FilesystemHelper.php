<?php

class CM_Tools_Generator_FilesystemHelper extends CM_Class_Abstract {

    /** @var CM_OutputStream_Interface */
    private $_output;

    /**
     * @param CM_OutputStream_Interface $output
     */
    public function __construct(CM_OutputStream_Interface $output) {
        $this->_output = $output;
    }

    /**
     * @param CM_File $directory
     */
    public function createDirectory(CM_File $directory) {
        if ($directory->isDirectory()) {
            $this->notify('skip', $directory->getPath());
        } else {
            $gitKeepFile = $directory->joinPath('.gitkeep');
            $this->notify('mkdir', $gitKeepFile->getParentDirectory()->getPath());
            $gitKeepFile->ensureParentDirectory();
            $this->createFile($gitKeepFile);
        }
    }

    /**
     * @param CM_File     $file
     * @param string|null $content
     * @param bool|null   $overwrite
     */
    public function createFile(CM_File $file, $content = null, $overwrite = null) {
        $parentDirectory = $file->getParentDirectory();
        if (!$parentDirectory->getExists()) {
            $this->createDirectory($parentDirectory);
        }
        if ($file->getExists()) {
            if (!$overwrite) {
                $this->notify('skip', $file->getPath());
            } else {
                $this->notify('modify', $file->getPath());
                $file->write($content);
            }
        } else {
            $this->notify('create', $file->getPath());
            $file->write($content);
        }
    }

    /**
     * @param string $action
     * @param string $path
     */
    public function notify($action, $path) {
        $actionMessage = str_pad($action, 10, ' ', STR_PAD_LEFT);
        $this->_output->writeln($actionMessage . '  ' . $path);
    }
}
