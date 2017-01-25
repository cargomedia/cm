<?php

class CMService_AdWords_ClientTest extends CMTest_TestCase {

    public function testNoConversion() {
        $client = new CMService_AdWords_Client();
        $this->assertSame('', $client->getJs());
        $this->assertSame(<<<EOD
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion_async.js" charset="utf-8"></script>
EOD
            , $client->getHtml(new CM_Frontend_Environment()));
    }

    public function testAddConversion() {
        $client = new CMService_AdWords_Client();
        $addConversion = new ReflectionMethod($client, '_addConversion');
        $addConversion->setAccessible(true);
        $addConversion->invoke($client, CMService_AdWords_Conversion::fromJson('{"google_conversion_id":123456,"google_conversion_language":"en","google_conversion_format":"1","google_conversion_color":"666666","google_conversion_label":"label","google_remarketing_only":true,"google_conversion_value":123,"google_conversion_currency":"USD","google_custom_params":{"a":1,"b":2}}'));
        $addConversion->invoke($client, CMService_AdWords_Conversion::fromJson('{"google_conversion_id":789}'));
        $this->assertSame(<<<EOD
window.google_trackConversion({"google_conversion_id":123456,"google_conversion_language":"en","google_conversion_format":"1","google_conversion_color":"666666","google_conversion_label":"label","google_remarketing_only":true,"google_conversion_value":123,"google_conversion_currency":"USD","google_custom_params":{"a":1,"b":2}});window.google_trackConversion({"google_conversion_id":789});
EOD
            , $client->getJs());
        $this->assertSame(<<<EOD
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion_async.js" charset="utf-8"></script><script type="text/javascript">
/* <![CDATA[ */
window.google_trackConversion({"google_conversion_id":123456,"google_conversion_language":"en","google_conversion_format":"1","google_conversion_color":"666666","google_conversion_label":"label","google_remarketing_only":true,"google_conversion_value":123,"google_conversion_currency":"USD","google_custom_params":{"a":1,"b":2}});window.google_trackConversion({"google_conversion_id":789});
//]]>
</script>
EOD
            , $client->getHtml(new CM_Frontend_Environment()));
    }

    public function testTrackPageView() {
        $viewer = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment(null, $viewer);
        $client = new CMService_AdWords_Client();
        $pushConversion = new ReflectionMethod($client, '_pushConversion');
        $pushConversion->setAccessible(true);
        $pushConversion->invoke($client, $viewer, CMService_AdWords_Conversion::fromJson('{"google_conversion_id":123456,"google_conversion_language":"en","google_conversion_format":"1","google_conversion_color":"666666","google_conversion_label":"label","google_remarketing_only":true,"google_conversion_value":123,"google_conversion_currency":"USD","google_custom_params":{"a":1,"b":2}}'));
        $pushConversion->invoke($client, $viewer, CMService_AdWords_Conversion::fromJson('{"google_conversion_id":789}'));
        $this->assertSame('', $client->getJs());
        $this->assertSame(<<<EOD
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion_async.js" charset="utf-8"></script>
EOD
            , $client->getHtml($environment));
        $client->trackPageView($environment, '/');
        $this->assertSame(<<<EOD
window.google_trackConversion({"google_conversion_id":123456,"google_conversion_language":"en","google_conversion_format":"1","google_conversion_color":"666666","google_conversion_label":"label","google_remarketing_only":true,"google_conversion_value":123,"google_conversion_currency":"USD","google_custom_params":{"a":1,"b":2}});window.google_trackConversion({"google_conversion_id":789});
EOD
            , $client->getJs());
        $this->assertSame(<<<EOD
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion_async.js" charset="utf-8"></script><script type="text/javascript">
/* <![CDATA[ */
window.google_trackConversion({"google_conversion_id":123456,"google_conversion_language":"en","google_conversion_format":"1","google_conversion_color":"666666","google_conversion_label":"label","google_remarketing_only":true,"google_conversion_value":123,"google_conversion_currency":"USD","google_custom_params":{"a":1,"b":2}});window.google_trackConversion({"google_conversion_id":789});
//]]>
</script>
EOD
            , $client->getHtml($environment));
    }
}
