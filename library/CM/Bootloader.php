<?php
require_once 'Util.php';

class CM_Bootloader {

    /** @var CM_Config|null */
    private $_config = null;

    /** @var bool */
    private $_debug;

    /** @var CM_ExceptionHandling_Handler_Abstract */
    private $_exceptionHandler;

    /** @var CM_EventHandler_EventHandler */
    private $_eventHandler;

    /** @var CM_Bootloader */
    protected static $_instance;

    /**
     * @param string $pathRoot
     * @throws CM_Exception_Invalid
     */
    public function __construct($pathRoot) {
        if (self::$_instance) {
            throw new CM_Exception_Invalid('Bootloader already instantiated');
        }
        self::$_instance = $this;

        mb_internal_encoding('UTF-8');
        umask(0);

        define('DIR_ROOT', $pathRoot);
        $this->_debug = (bool) getenv('CM_DEBUG');
    }

    public function load() {
        $this->_constants();
        $this->_exceptionHandler();
        $this->_errorHandler();
        $this->_registerServices();
        $this->_defaults();
    }

    /**
     * @return CM_Config
     */
    public function getConfig() {
        if (null === $this->_config) {
            $this->_config = new CM_Config();
        }
        return $this->_config;
    }

    /**
     * @return CM_ExceptionHandling_Handler_Abstract
     */
    public function getExceptionHandler() {
        if (!$this->_exceptionHandler) {
            if ($this->isCli()) {
                $exceptionHandler = new CM_ExceptionHandling_Handler_Cli();
            } else {
                $exceptionHandler = new CM_ExceptionHandling_Handler_Http();
            }
            $exceptionHandler->setServiceManager(CM_Service_Manager::getInstance());
            $this->_exceptionHandler = $exceptionHandler;
        }
        return $this->_exceptionHandler;
    }

    /**
     * @param CM_ExceptionHandling_Handler_Abstract $exceptionHandler
     */
    public function setExceptionHandler(CM_ExceptionHandling_Handler_Abstract $exceptionHandler) {
        $this->_exceptionHandler = $exceptionHandler;
    }

    /**
     * @return CM_EventHandler_EventHandler
     */
    public function getEventHandler() {
        if (null === $this->_eventHandler) {
            $this->_eventHandler = new CM_EventHandler_EventHandler();
        }
        return $this->_eventHandler;
    }

    /**
     * @return bool
     */
    public function isDebug() {
        return $this->_debug;
    }

    /**
     * @param bool $state
     */
    public function setDebug($state) {
        $this->_debug = $state;
    }

    /**
     * @return bool
     */
    public function isCli() {
        return PHP_SAPI === 'cli';
    }

    /**
     * @return string
     */
    public function getDataPrefix() {
        return '';
    }

    /**
     * @return string[]
     */
    public function getModules() {
        return array_keys($this->_getModulePaths());
    }

    public function reloadModulePaths() {
        $cacheKey = CM_CacheConst::Modules;
        $cache = new CM_Cache_Storage_Apc();
        $cache->delete($cacheKey);
    }

    /**
     * @return string
     */
    public function getDirTmp() {
        return DIR_ROOT . 'tmp/';
    }

    /**
     * @param string $name
     * @throws CM_Exception_Invalid
     * @return string
     */
    public function getModulePath($name) {
        $namespacePaths = $this->_getModulePaths();
        if (!array_key_exists($name, $namespacePaths)) {
            throw new CM_Exception_Invalid('Module not found within module paths', null, ['moduleName' => $name]);
        }
        return $namespacePaths[$name];
    }

    /**
     * @return DateTimeZone
     */
    public function getTimeZone() {
        return new DateTimeZone(CM_Config::get()->timeZone);
    }

    /**
     * @param Closure $code
     * @return int
     */
    public function execute(Closure $code) {
        try {
            $code();
            return 0;
        } catch (Exception $e) {
            $this->getExceptionHandler()->handleException($e);
            return 1;
        }
    }

    protected function _constants() {
        define('DIR_VENDOR', DIR_ROOT . 'vendor/');
        define('DIR_PUBLIC', DIR_ROOT . 'public/');
    }

    protected function _errorHandler() {
        error_reporting(E_ALL | E_STRICT);
        set_error_handler(array($this->getExceptionHandler(), 'handleErrorRaw'));
        register_shutdown_function(array($this->getExceptionHandler(), 'handleErrorFatal'));
    }

    protected function _exceptionHandler() {
        $errorHandler = $this->getExceptionHandler();
        set_exception_handler(function (Exception $exception) use ($errorHandler) {
            $errorHandler->handleExceptionWithSeverity($exception, CM_Exception::FATAL);
            exit(1);
        });
    }

    protected function _registerServices() {
        $serviceManager = CM_Service_Manager::getInstance();
        $serviceManager->register('debug', 'CM_Debug', ['enabled' => $this->isDebug()]);
        $serviceManager->register('filesystems', 'CM_Service_Filesystems');
        $serviceManager->register('filesystem-tmp', 'CM_File_Filesystem', [
            'adapter' => new CM_File_Filesystem_Adapter_Local($this->getDirTmp())
        ]);
        foreach (CM_Config::get()->services as $serviceKey => $serviceDefinition) {
            $serviceManager->registerDefinition($serviceKey, $serviceDefinition);
        }
    }

    protected function _defaults() {
        date_default_timezone_set($this->getTimeZone()->getName());
        CM_Service_Manager::getInstance()->getNewrelic()->setConfig();
    }

    /**
     * @return array
     */
    private function _getModulePaths() {
        $cacheKey = CM_CacheConst::Modules;
        $apcCache = new CM_Cache_Storage_Apc();
        if (false === ($modulePaths = $apcCache->get($cacheKey))) {
            $fileCache = new CM_Cache_Storage_File();
            $installation = new CM_App_Installation(DIR_ROOT);
            if ($installation->getUpdateStamp() > $fileCache->getCreateStamp($cacheKey) || false === ($modulePaths = $fileCache->get($cacheKey))) {
                $modulePaths = $installation->getModulePaths();
                $fileCache->set($cacheKey, $modulePaths);
            }
            $apcCache->set($cacheKey, $modulePaths);
        }
        return $modulePaths;
    }

    /**
     * @return CM_Bootloader
     * @throws Exception
     */
    public static function getInstance() {
        if (!self::$_instance) {
            throw new Exception('No bootloader instance');
        }
        return self::$_instance;
    }
}
