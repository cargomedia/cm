<?php

class CMService_GoogleTagManager_Client implements CM_Service_Tracking_ClientInterface {

    /** @var string */
    protected $_code;

    /** @var array */
    protected $_dataLayer = [];

    /**
     * @param string $code
     */
    public function __construct($code) {
        $this->_code = (string) $code;
    }

    public function getHtml(CM_Frontend_Environment $environment) {
        $html = <<<EOF
<noscript><iframe src="//www.googletagmanager.com/ns.html?id={$this->_code}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script type="text/javascript">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$this->_code}');
EOF;
        $html .= $this->getJs() . '</script>';
        return $html;
    }

    public function getJs() {
        if (empty($this->_dataLayer)) {
            return '';
        }
        $dataLayerJson = CM_Util::jsonEncode($this->_dataLayer);
        return "dataLayer.push({$dataLayerJson});";
    }

    public function trackAction(CM_Action_Abstract $action) {
    }

    public function trackPageView(CM_Frontend_Environment $environment, $path) {
        $this->_setDataLayerVariable('event', 'Page View');
        if ($environment->hasViewer()) {
            $fixture = new CM_Splittest_Fixture($environment->getViewer());
        } elseif (($clientDevice = $environment->getClientDevice()) && $clientDevice->hasId()) {
            $fixture = new CM_Splittest_Fixture($clientDevice->getId(), CM_Splittest_Fixture::TYPE_REQUEST_CLIENT);
        }
        if (isset($fixture)) {
            $variationDataList = CM_Model_Splittest::getVariationDataListFixture($fixture);
            foreach ($variationDataList as $variationData) {
                $this->_setDataLayerVariable('Splittest ' . $variationData['splittest'], $variationData['variation']);
            }
        }
    }

    public function trackSplittest(CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation) {
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    protected function _setDataLayerVariable($name, $value) {
        $name = (string) $name;
        $value = (string) $value;
        $this->_dataLayer[$name] = $value;
        return $this;
    }
}
