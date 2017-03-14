<?php

class CM_File_Filesystem_Adapter_AwsS3 extends CM_File_Filesystem_Adapter implements
    CM_File_Filesystem_Adapter_SizeCalculatorInterface,
    CM_File_Filesystem_Adapter_ChecksumCalculatorInterface,
    CM_File_Filesystem_Adapter_StreamInterface {

    /** @var Aws\S3\S3Client */
    private $_client;

    /** @var string */
    private $_bucket;

    /** @var string */
    private $_acl;

    /**
     * @param Aws\S3\S3Client $client
     * @param string          $bucket
     * @param string|null     $acl
     * @param string|null     $pathPrefix
     * @throws CM_Exception
     */
    public function __construct(Aws\S3\S3Client $client, $bucket, $acl = null, $pathPrefix = null) {
        parent::__construct($pathPrefix);
        if (null === $acl) {
            $acl = 'private';
        }
        $this->_client = $client;
        $this->_bucket = (string) $bucket;
        $this->_acl = $acl;
    }

    public function getStreamRead($path) {
        $streamUrl = $this->_getStreamUrl($path);
        if (!in_array('s3', stream_get_wrappers(), true)) {
            throw new CM_Exception('Stream wrapper not enabled');
        }
        $stream = @fopen($streamUrl, 'r');
        if (false === $stream) {
            throw new CM_Exception('Cannot open read stream.', null, ['streamUrl' => $streamUrl]);
        }
        return $stream;
    }

    public function getStreamWrite($path) {
        $streamUrl = $this->_getStreamUrl($path);
        if (!in_array('s3', stream_get_wrappers(), true)) {
            throw new CM_Exception('Stream wrapper not enabled');
        }
        $stream = @fopen($streamUrl, 'w');
        if (false === $stream) {
            throw new CM_Exception('Cannot open write stream.', null, ['streamUrl' => $streamUrl]);
        }
        return $stream;
    }

    public function read($path) {
        $options = $this->_getOptions($path);
        try {
            return (string) $this->_client->getObject($options)->get('Body');
        } catch (\Exception $e) {
            throw new CM_Exception('Cannot read contents of path.', null, [
                'path'                     => $path,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }

    public function write($path, $content) {
        $options = $this->_getOptions($path, array('Body' => $content));

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($content);
        if (false !== $mimeType) {
            $options['ContentType'] = $mimeType;
        }

        try {
            $this->_client->putObject($options);
        } catch (\Exception $e) {
            throw new CM_Exception('Cannot write bytes to the path', null, [
                'bytesCount'               => strlen($content),
                'path'                     => $path,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }

    public function writeStream($path, $stream) {
        $uploader = new \Aws\S3\MultipartUploader($this->_client, $stream, [
            'bucket' => $this->_bucket,
            'key'    => $this->_getAbsolutePath($path),
        ]);

        try {
            $uploader->upload();
        } catch (\Aws\Exception\MultipartUploadException $exception) {
            throw new CM_Exception('AWS S3 Upload to path failed', null, [
                'path'                     => $path,
                'originalExceptionMessage' => $exception->getMessage(),
            ]);
        }
    }

    public function exists($path) {
        try {
            return $this->_client->doesObjectExist($this->_bucket, $this->_getAbsolutePath($path));
        } catch (\Exception $e) {
            throw new CM_Exception('Cannot check existence of path', null, [
                'path'                     => $path,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }

    public function getModified($path) {
        $options = $this->_getOptions($path);
        try {
            $lastModified = $this->_client->headObject($options)->get('LastModified');
            return strtotime($lastModified);
        } catch (\Exception $e) {
            throw new CM_Exception_Invalid('Cannot get modified time of path', null, [
                'path'                     => $path,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }

    public function getChecksum($path) {
        $options = $this->_getOptions($path);
        try {
            return trim($this->_client->headObject($options)->get('ETag'), '"');
        } catch (\Exception $e) {
            throw new CM_Exception('Cannot get AWS::ETag of path', null, [
                'path'                     => $path,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }

    public function delete($path) {
        $options = $this->_getOptions($path);
        try {
            $this->_client->deleteObject($options);
        } catch (\Exception $e) {
            throw new CM_Exception_Invalid('Cannot delete file in path', null, [
                'path'                     => $path,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }

    public function listByPrefix($pathPrefix, $noRecursion = null) {
        $pathPrefix = $this->_getAbsolutePath($pathPrefix);
        if ('' !== $pathPrefix) {
            $pathPrefix .= '/';
        }
        $commandOptions = array(
            'Bucket' => $this->_bucket,
            'Prefix' => $pathPrefix,
        );
        if ($noRecursion) {
            $commandOptions['Delimiter'] = '/';
        }
        $iteratorOptions = array(
            'return_prefixes' => true,
        );

        $fileKeys = $directoryKeys = array();
        foreach ($this->_client->getIterator('ListObjects', $commandOptions, $iteratorOptions) as $file) {
            if (isset($file['Prefix'])) {
                $directoryKeys[] = $this->_getRelativePath($file['Prefix']);
            } else {
                $fileKeys[] = $this->_getRelativePath($file['Key']);
            }
        }

        return array('files' => $fileKeys, 'dirs' => $directoryKeys);
    }

    public function rename($sourcePath, $targetPath) {
        try {
            $this->copy($sourcePath, $targetPath);
            $this->delete($sourcePath);
        } catch (CM_Exception $e) {
            throw new CM_Exception('Cannot rename', null, [
                'sourcePath'               => $sourcePath,
                'targetPath'               => $targetPath,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }

    public function copy($sourcePath, $targetPath) {
        $options = $this->_getOptions($targetPath,
            array('CopySource' => $this->_bucket . '/' . $this->_getAbsolutePath($sourcePath)));

        try {
            $this->_client->copyObject($options);
        } catch (Exception $e) {
            throw new CM_Exception('Cannot copy', null, [
                'sourcePath'               => $sourcePath,
                'targetPath'               => $targetPath,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }

    public function isDirectory($path) {
        $options = array(
            'Bucket'  => $this->_bucket,
            'Prefix'  => $this->_getAbsolutePath($path) . '/',
            'MaxKeys' => 1,
        );

        try {
            $result = $this->_client->listObjects($options);
            return count($result->get('Contents')) > 0;
        } catch (Exception $e) {
            throw new CM_Exception('Cannot get directory-info for the path', null, [
                'path'                     => $path,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }

    public function getSize($path) {
        $options = $this->_getOptions($path);
        try {
            return (int) $this->_client->headObject($options)->get('ContentLength');
        } catch (\Exception $e) {
            throw new CM_Exception('Cannot get size for path', null, [
                'path'                     => $path,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }

    public function ensureDirectory($path) {
    }

    public function setup() {
        $this->_ensureBucket(3);
    }

    /**
     * @return bool
     * @throws CM_Exception
     */
    protected function _getBucketExists() {
        return $this->_client->doesBucketExist($this->_bucket, false);
    }

    /**
     * @throws \Aws\S3\Exception\BucketAlreadyOwnedByYouException
     * @throws \Aws\S3\Exception\OperationAbortedException
     */
    protected function _createBucket() {
        $options = array('Bucket' => $this->_bucket);
        $region = $this->_client->getRegion();
        if ($region != 'us-east-1') {
            $options['LocationConstraint'] = $region;
        }
        $this->_client->createBucket($options);
    }

    /**
     * @param int $retryCount
     * @throws CM_Exception
     */
    protected function _ensureBucket($retryCount) {
        if (!$this->_getBucketExists()) {
            try {
                $this->_createBucket();
            } catch (\Aws\S3\Exception\BucketAlreadyOwnedByYouException $e) {
                // Probably created in parallel?
            } catch (\Aws\S3\Exception\OperationAbortedException $e) {
                if ($retryCount > 0) {
                    $this->_ensureBucket($retryCount - 1);
                } else {
                    throw new CM_Exception('Cannot create bucket', null, ['originalExceptionMessage' => $e->getMessage()]);
                }
            }
        }
    }

    protected function _getAbsolutePath($pathRelative) {
        $path = parent::_getAbsolutePath($pathRelative);
        return ltrim($path, '/');
    }

    /**
     * @param CM_Comparable $other
     * @return boolean
     */
    public function equals(CM_Comparable $other = null) {
        if (empty($other)) {
            return false;
        }
        if (get_class($this) !== get_class($other)) {
            return false;
        }
        /** @var CM_File_Filesystem_Adapter_AwsS3 $other */
        return ($this->_bucket === $other->_bucket) && ($this->getPathPrefix() === $other->getPathPrefix());
    }

    /**
     * @param string $path
     * @param array  $options
     * @return array
     */
    protected function _getOptions($path, array $options = null) {
        if (null === $options) {
            $options = array();
        }
        $options['ACL'] = $this->_acl;
        $options['Bucket'] = $this->_bucket;
        $options['Key'] = $this->_getAbsolutePath($path);

        return $options;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function _getStreamUrl($path) {
        return 's3://' . $this->_bucket . '/' . $this->_getAbsolutePath($path);
    }
}
