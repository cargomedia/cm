<?php

class CMService_AdWords_ConversionTest extends CMTest_TestCase {

    public function testGetSet() {
        $conversion = new CMService_AdWords_Conversion();

        $this->assertSame(null, $conversion->getColor());
        $conversion->setColor('666666');
        $this->assertSame('666666', $conversion->getColor());
        $conversion->setColor(null);
        $this->assertSame(null, $conversion->getColor());

        $this->assertSame(null, $conversion->getConversionCurrency());
        $conversion->setConversionCurrency('USD');
        $this->assertSame('USD', $conversion->getConversionCurrency());
        $conversion->setConversionCurrency(null);
        $this->assertSame(null, $conversion->getConversionCurrency());

        $this->assertSame(null, $conversion->getConversionValue());
        $conversion->setConversionValue(123);
        $this->assertSame(123., $conversion->getConversionValue());
        $conversion->setConversionValue(null);
        $this->assertSame(null, $conversion->getConversionValue());

        $this->assertSame(null, $conversion->getCustomParameterList());
        $conversion->setCustomParameterList(['a' => 1, 'b' => 2]);
        $this->assertSame(['a' => 1, 'b' => 2], $conversion->getCustomParameterList());
        $conversion->setCustomParameterList(null);
        $this->assertSame(null, $conversion->getCustomParameterList());

        $this->assertSame(null, $conversion->getCustomParameter('a'));
        $this->assertSame(null, $conversion->getCustomParameter('b'));
        $this->assertSame(null, $conversion->getCustomParameterList());
        $conversion->setCustomParameter('a', 1);
        $this->assertSame(1, $conversion->getCustomParameter('a'));
        $this->assertSame(null, $conversion->getCustomParameter('b'));
        $this->assertSame(['a' => 1], $conversion->getCustomParameterList());
        $conversion->setCustomParameter('b', 2);
        $this->assertSame(1, $conversion->getCustomParameter('a'));
        $this->assertSame(2, $conversion->getCustomParameter('b'));
        $this->assertSame(['a' => 1, 'b' => 2], $conversion->getCustomParameterList());
        $conversion->setCustomParameter('a', null);
        $this->assertSame(null, $conversion->getCustomParameter('a'));
        $this->assertSame(2, $conversion->getCustomParameter('b'));
        $this->assertSame(['b' => 2], $conversion->getCustomParameterList());
        $conversion->setCustomParameter('b', null);
        $this->assertSame(null, $conversion->getCustomParameter('a'));
        $this->assertSame(null, $conversion->getCustomParameter('b'));
        $this->assertSame(null, $conversion->getCustomParameterList());

        $this->assertSame(null, $conversion->getFormat());
        $conversion->setFormat('1');
        $this->assertSame('1', $conversion->getFormat());
        $conversion->setFormat(null);
        $this->assertSame(null, $conversion->getFormat());

        $this->assertSame(null, $conversion->getId());
        $conversion->setId(123456);
        $this->assertSame(123456, $conversion->getId());
        $conversion->setId(null);
        $this->assertSame(null, $conversion->getId());

        $this->assertSame(null, $conversion->getLabel());
        $conversion->setLabel('label');
        $this->assertSame('label', $conversion->getLabel());
        $conversion->setLabel(null);
        $this->assertSame(null, $conversion->getLabel());

        $this->assertSame(null, $conversion->getLanguage());
        $conversion->setLanguage('en');
        $this->assertSame('en', $conversion->getLanguage());
        $conversion->setLanguage(null);
        $this->assertSame(null, $conversion->getLanguage());

        $this->assertSame(null, $conversion->getRemarketingOnly());
        $conversion->setRemarketingOnly(true);
        $this->assertSame(true, $conversion->getRemarketingOnly());
        $conversion->setRemarketingOnly(false);
        $this->assertSame(false, $conversion->getRemarketingOnly());
        $conversion->setRemarketingOnly(null);
        $this->assertSame(null, $conversion->getRemarketingOnly());
    }

    public function testToArray() {
        $conversion = new CMService_AdWords_Conversion();

        $this->assertSame([], $conversion->toArray());

        $conversion->setColor('666666');
        $conversion->setConversionCurrency('USD');
        $conversion->setConversionValue(123);
        $conversion->setCustomParameterList(['a' => 1, 'b' => 2]);
        $conversion->setFormat('1');
        $conversion->setId(123456);
        $conversion->setLabel('label');
        $conversion->setLanguage('en');
        $conversion->setRemarketingOnly(true);

        $this->assertSame([
            'google_conversion_id'       => 123456,
            'google_conversion_language' => 'en',
            'google_conversion_format'   => '1',
            'google_conversion_color'    => '666666',
            'google_conversion_label'    => 'label',
            'google_remarketing_only'    => true,
            'google_conversion_value'    => 123.,
            'google_conversion_currency' => 'USD',
            'google_custom_params'       => ['a' => 1, 'b' => 2],
        ], $conversion->toArray());
    }

    public function testFromArray() {
        $conversion = CMService_AdWords_Conversion::fromArray([]);

        $this->assertSame(null, $conversion->getId());
        $this->assertSame(null, $conversion->getLanguage());
        $this->assertSame(null, $conversion->getFormat());
        $this->assertSame(null, $conversion->getColor());
        $this->assertSame(null, $conversion->getLabel());
        $this->assertSame(null, $conversion->getRemarketingOnly());
        $this->assertSame(null, $conversion->getConversionValue());
        $this->assertSame(null, $conversion->getConversionCurrency());
        $this->assertSame(null, $conversion->getCustomParameterList());

        $conversion = CMService_AdWords_Conversion::fromArray([
            'google_conversion_id'       => 123456,
            'google_conversion_language' => 'en',
            'google_conversion_format'   => '1',
            'google_conversion_color'    => '666666',
            'google_conversion_label'    => 'label',
            'google_remarketing_only'    => true,
            'google_conversion_value'    => 123.,
            'google_conversion_currency' => 'USD',
            'google_custom_params'       => ['a' => 1, 'b' => 2],
        ]);

        $this->assertSame(123456, $conversion->getId());
        $this->assertSame('en', $conversion->getLanguage());
        $this->assertSame('1', $conversion->getFormat());
        $this->assertSame('666666', $conversion->getColor());
        $this->assertSame('label', $conversion->getLabel());
        $this->assertSame(true, $conversion->getRemarketingOnly());
        $this->assertSame(123., $conversion->getConversionValue());
        $this->assertSame('USD', $conversion->getConversionCurrency());
        $this->assertSame(['a' => 1, 'b' => 2], $conversion->getCustomParameterList());
    }

    public function testToJson() {
        $conversion = new CMService_AdWords_Conversion();

        $this->assertSame('{}', $conversion->toJson());

        $conversion->setColor('666666');
        $conversion->setConversionCurrency('USD');
        $conversion->setConversionValue(123);
        $conversion->setCustomParameterList(['a' => 1, 'b' => 2]);
        $conversion->setFormat('1');
        $conversion->setId(123456);
        $conversion->setLabel('label');
        $conversion->setLanguage('en');
        $conversion->setRemarketingOnly(true);

        $this->assertSame(<<<EOD
{"google_conversion_id":123456,"google_conversion_language":"en","google_conversion_format":"1","google_conversion_color":"666666","google_conversion_label":"label","google_remarketing_only":true,"google_conversion_value":123,"google_conversion_currency":"USD","google_custom_params":{"a":1,"b":2}}
EOD
            , $conversion->toJson());
    }

    public function testFromJson() {
        $conversion = CMService_AdWords_Conversion::fromJson('{}');

        $this->assertSame(null, $conversion->getId());
        $this->assertSame(null, $conversion->getLanguage());
        $this->assertSame(null, $conversion->getFormat());
        $this->assertSame(null, $conversion->getColor());
        $this->assertSame(null, $conversion->getLabel());
        $this->assertSame(null, $conversion->getRemarketingOnly());
        $this->assertSame(null, $conversion->getConversionValue());
        $this->assertSame(null, $conversion->getConversionCurrency());
        $this->assertSame(null, $conversion->getCustomParameterList());

        $conversion = CMService_AdWords_Conversion::fromJson(<<<EOD
{
    "google_conversion_id": 123456,
    "google_conversion_language": "en",
    "google_conversion_format": "1",
    "google_conversion_color": "666666",
    "google_conversion_label": "label",
    "google_remarketing_only": true,
    "google_conversion_value": 123,
    "google_conversion_currency": "USD",
    "google_custom_params": {
        "a": 1,
        "b": 2
    }
}
EOD
        );

        $this->assertSame(123456, $conversion->getId());
        $this->assertSame('en', $conversion->getLanguage());
        $this->assertSame('1', $conversion->getFormat());
        $this->assertSame('666666', $conversion->getColor());
        $this->assertSame('label', $conversion->getLabel());
        $this->assertSame(true, $conversion->getRemarketingOnly());
        $this->assertSame(123., $conversion->getConversionValue());
        $this->assertSame('USD', $conversion->getConversionCurrency());
        $this->assertSame(['a' => 1, 'b' => 2], $conversion->getCustomParameterList());
    }
}
