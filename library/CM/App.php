<?php

class CM_App implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @var CM_App
     */
    private static $_instance;

    public function __construct() {
        $this->setServiceManager(CM_Service_Manager::getInstance());
    }

    /**
     * @return CM_App
     */
    public static function getInstance() {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @throws CM_Exception_Invalid
     * @return CM_Provision_Loader
     */
    public function getProvisionLoader() {
        $loader = new CM_Provision_Loader();
        $loader->registerScriptFromClassNames(CM_Config::get()->CM_App->setupScriptClasses, $this->getServiceManager());
        return $loader;
    }

    public function fillCaches() {
        /** @var CM_Asset_Javascript_Abstract[] $assetList */
        $assetList = array();

        $debug = CM_Bootloader::getInstance()->isDebug();
        $siteList = CM_Site_Abstract::getAll();
        $languageList = new CM_Paging_Language_Enabled();

        foreach ($siteList as $site) {
            $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));
            $assetList[] = new CM_Asset_Javascript_Internal($site, $debug);
            $assetList[] = new CM_Asset_Javascript_Library($site, $debug);
            $assetList[] = new CM_Asset_Javascript_Vendor_BeforeBody($site, $debug);
            $assetList[] = new CM_Asset_Javascript_Vendor_AfterBody($site, $debug);
            $assetList[] = new CM_Asset_Css_Vendor($render, $debug);
            $assetList[] = new CM_Asset_Css_Library($render, $debug);
            /** @var CM_Model_Language $language */
            foreach ($languageList as $language) {
                $assetList[] = new CM_Asset_Javascript_Translations($site, $debug, $language);
            }
        }

        /** @var CM_Model_Language $language */
        foreach ($languageList as $language) {
            $language->getTranslations()->getItemsRaw();
            $language->getTranslations(true)->getItemsRaw();
        }

        foreach ($assetList as $asset) {
            $asset->get();
        }
        CM_Bootloader::getInstance()->getModules();
    }

    /**
     * @throws CM_Exception_Invalid
     * @return string
     */
    public function getName() {
        $config = CM_Bootloader::getInstance()->getConfig()->get();
        if (!isset($config->installationName)) {
            throw new CM_Exception_Invalid('The `installationName` config property is required.');
        }
        return $config->installationName;
    }

    /**
     * @return int
     */
    public function getDeployVersion() {
        return (int) CM_Config::get()->deployVersion;
    }

    /**
     * @return CM_Http_Handler
     */
    public function getHttpHandler() {
        return new CM_Http_Handler(CM_Service_Manager::getInstance());
    }
}
