<!doctype html>{block name="before-html"}{/block}
<html {if $render->getLanguage()}lang="{$render->getLanguage()->getAbbreviation()}"{/if} class="{$viewResponse->getCssClasses()|implode:' '} {block name='html-class'}{/block}" id="{$viewResponse->getAutoId()}" {if isset($webFontLoaderConfig)}data-web-font-loader='{$webFontLoaderConfig}'{/if}>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge; requiresActiveX=true">
    {if isset($metaDescription)}<meta name="description" content="{$metaDescription|escape}">{/if}
    {if isset($metaKeywords)}<meta name="keywords" content="{$metaKeywords|escape}">{/if}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{$render->getSite()->getName()|escape}">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="msapplication-TileColor" content="{block name='tileColor'}{lessVariable name='colorBrand'}{/block}">
    <meta name="msapplication-TileImage" content="{resourceUrl path='img/meta/square-144-transparent.png' type='layout'}">
    <meta name="msapplication-config" content="{resourceUrl path='browserconfig.xml' type='layout'}">
    <meta name="theme-color" content="{block name='themeColor'}{lessVariable name='colorBrand'}{/block}">

    {**
     * See https://developer.apple.com/library/prerelease/ios/documentation/UserExperience/Conceptual/MobileHIG/IconMatrix.html
     *}
    <link rel="apple-touch-icon" sizes="76x76" href="{resourceUrl path='img/meta/square-76.png' type='layout'}">
    <link rel="apple-touch-icon" sizes="120x120" href="{resourceUrl path='img/meta/square-120.png' type='layout'}">
    <link rel="apple-touch-icon" sizes="152x152" href="{resourceUrl path='img/meta/square-152.png' type='layout'}">
    <link rel="apple-touch-icon" sizes="167x167" href="{resourceUrl path='img/meta/square-167.png' type='layout'}">
    <link rel="apple-touch-icon" sizes="180x180" href="{resourceUrl path='img/meta/square-180.png' type='layout'}">

    <link rel="icon" type="image/png" href="{resourceUrl path='img/meta/square-32.png' type='layout'}" sizes="32x32">
    <link rel="icon" type="image/png" href="{resourceUrl path='img/meta/square-96.png' type='layout'}" sizes="96x96">
    <link rel="icon" type="image/png" href="{resourceUrl path='img/meta/square-16.png' type='layout'}" sizes="16x16">

    <link rel="manifest" href="{resourceUrl path='manifest.json' type='layout' sameOrigin=true}">
    <link rel="mask-icon" href="{resourceUrl path='img/favicon.svg' type='layout'}" color="{lessVariable name='colorBrand'}">

    <link rel="alternate" href="{$renderDefault->getUrlPage($page, $page->getParams()->getParamsEncoded())|escape}" hreflang="x-default">
    {foreach $languageList as $language}
      <link rel="alternate" href="{$renderDefault->getUrlPage($page, $page->getParams()->getParamsEncoded(), null, $language)|escape}" hreflang="{$language->getAbbreviation()}">
    {/foreach}

    <title>{$title|escape}</title>
    {resourceCss file='all.css' type="vendor"}
    {resourceCss file='all.css' type="library"}
    {resourceJs file='before-body.js' type="vendor"}
    {block name='head'}{/block}
  </head>
  <body>
    {$render->getServiceManager()->getTrackings()->getHtml($render->getEnvironment())}
    {if CM_Http_Request_Abstract::hasInstance() && !CM_Http_Request_Abstract::getInstance()->isSupported()}
      <div id="browserNotSupported">
        <h2><span class="icon-warning"></span> {translate 'Your browser is no longer supported.'}</h2>
        <p>{translate 'We recommend upgrading to the latest Internet Explorer, Google Chrome, Firefox, or Opera. Click here for <a href="{$url}">more information</a>.' url='http://whatbrowser.org'}
      </div>
    {/if}

    {block name='body-start'}{/block}
    {$layoutContent}
    {if CM_Bootloader::getInstance()->isDebug()}{component name='CM_Component_Debug'}{/if}
    {resourceJs file='after-body.js' type="vendor"}
    {resourceJs file='library.js' type="library"}
    {if $render->getLanguage()}
      {resourceJs file="translations/{CM_Model_Language::getVersionJavascript()}.js" type="library"}
    {/if}
    {$render->getGlobalResponse()->getHtml()}
    {block name='body-end'}{/block}
  </body>
</html>
