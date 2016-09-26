<?php

class CMService_AdWords_Client implements CM_Service_Tracking_ClientInterface {

    use CM_Service_Tracking_QueueTrait;

    /** @var CMService_AdWords_Conversion[] */
    protected $_conversionList = [];

    public function getHtml(CM_Frontend_Environment $environment) {
        $html = '<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion_async.js" charset="utf-8"></script>';
        $js = $this->getJs();
        if ('' !== $js) {
            $html .= <<<EOD
<script type="text/javascript">
/* <![CDATA[ */
{$js}
//]]>
</script>
EOD;
        }
        return $html;
    }

    public function getJs() {
        $js = '';
        foreach ($this->_conversionList as $conversion) {
            $conversionJson = $conversion->toJson();
            $js .= <<<EOD
window.google_trackConversion({$conversionJson});
EOD;
        }
        return $js;
    }

    public function trackAction(CM_Action_Abstract $action) {
    }

    public function trackAffiliate($requestClientId, $affiliateName) {
    }

    public function trackPageView(CM_Frontend_Environment $environment, $path) {
        if ($viewer = $environment->getViewer()) {
            $this->_flushTrackingQueue($viewer);
        }
    }

    public function trackSplittest(CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation) {
    }

    /**
     * @param CMService_AdWords_Conversion $conversion
     */
    protected function _addConversion(CMService_AdWords_Conversion $conversion) {
        $this->_conversionList[] = $conversion;
    }

    /**
     * @param CM_Model_User $user
     */
    protected function _flushTrackingQueue(CM_Model_User $user) {
        while ($trackingData = $this->_popTrackingData($user)) {
            $conversion = CMService_AdWords_Conversion::fromJson($trackingData['conversion']);
            $this->_addConversion($conversion);
        }
    }

    /**
     * @param CM_Model_User                $user
     * @param CMService_AdWords_Conversion $conversion
     */
    protected function _pushConversion(CM_Model_User $user, CMService_AdWords_Conversion $conversion) {
        $conversionJson = $conversion->toJson();
        $this->_pushTrackingData($user, ['conversion' => $conversionJson]);
    }
}
