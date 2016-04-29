<?php

class CM_Paging_Translation_Language_AllTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testPaging() {
        $language = CM_Model_Language::create('Foo', 'foo', true);
        $languagePagingAll = $language->getTranslations();
        $languagePagingJavascriptOnly = $language->getTranslations(true);
        $this->assertEquals([], $languagePagingAll);
        $this->assertEquals([], $languagePagingJavascriptOnly);

        $languagePagingAll->set('foo', 'foo'); // js
        CM_Db_Db::update('cm_model_languagekey', ['javascript' => 1], ['name' => 'foo']);
        $languagePagingJavascriptOnly->set('bar', 'bar'); // js
        CM_Db_Db::update('cm_model_languagekey', ['javascript' => 1], ['name' => 'bar']);
        $languagePagingAll->set('baz', 'baz'); // no js
        $this->assertSame('foo', $language->getTranslations()->get('foo'));
        $this->assertSame('foo', $language->getTranslations(true)->get('foo'));
        $this->assertSame('bar', $language->getTranslations()->get('bar'));
        $this->assertSame('bar', $language->getTranslations(true)->get('bar'));
        $this->assertSame('baz', $language->getTranslations()->get('baz'));

        $exception = $this->catchException(function() use ($language) {
            $language->getTranslations(true)->get('baz');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);

        $languagePagingJavascriptOnly->set('foo', 'bar');
        $this->assertSame('bar', $language->getTranslations(true)->get('foo'));
        $this->assertSame('bar', $language->getTranslations()->get('foo'));
        $languagePagingAll->set('bar', 'foo');
        $this->assertSame('foo', $language->getTranslations()->get('bar'));
        $this->assertSame('foo', $language->getTranslations(true)->get('bar'));

        $languagePagingAll->remove('foo');
        $this->assertSame(null, $language->getTranslations()->get('foo'));
        $this->assertSame(null, $language->getTranslations(true)->get('foo'));
        $languagePagingJavascriptOnly->remove('bar');
        $this->assertSame(null, $language->getTranslations()->get('bar'));
        $this->assertSame(null, $language->getTranslations(true)->get('bar'));
    }
}
