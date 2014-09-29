<?php

class CMTools_Generator_FilesystemHelper extends CM_Class_Abstract {

    /** @var CM_OutputStream_Interface */
    private $_output;

    /** @var CM_File_Filesystem */
    private $_filesystem;

    /**
     * @param CM_File_Filesystem        $filesystem
     * @param CM_OutputStream_Interface $output
     */
    public function __construct(CM_File_Filesystem $filesystem, CM_OutputStream_Interface $output) {
        $this->_filesystem = $filesystem;
        $this->_output = $output;
    }

    /**
     * @param CM_File $file
     */
    public function createDirectory(CM_File $file) {
        if ($file->isDirectory()) {
            $this->notify('skip', $file->getPath());
        } else {
            $this->notify('mkdir', $file->getPath());
            $this->_filesystem->ensureDirectory($file->getPath());
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
     * @param string  $action
     * @param string $path
     */
    public function notify($action, $path) {
        $actionMessage = str_pad($action, 10, ' ', STR_PAD_LEFT);
        $this->_output->writeln($actionMessage . '  ' . $path);
    }
}
