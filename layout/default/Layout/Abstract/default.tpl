<!doctype html>
<html {if $render->getLanguage()}lang="{$render->getLanguage()->getAbbreviation()}"{/if} class="{block name='html-class'}{/block}">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge; requiresActiveX=true" />
		{if strlen($pageDescription)}<meta name="description" content="{$pageDescription|escape}">{/if}
		{if strlen($pageKeywords)}<meta name="keywords" content="{$pageKeywords|escape}">{/if}
		<meta name="msapplication-TileColor" content="{block name='tileColor'}#ffffff{/block}">
		<meta name="msapplication-TileImage" content="{resourceUrl path='img/tileImage.png' type='layout'}">
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<link rel="apple-touch-icon" href="{resourceUrl path='img/touch-icon-57.png' type='layout'}" />
		<link rel="apple-touch-icon" sizes="72x72" href="{resourceUrl path='img/touch-icon-72.png' type='layout'}" />
		<link rel="apple-touch-icon" sizes="114x114" href="{resourceUrl path='img/touch-icon-114.png' type='layout'}" />
		<link rel="apple-touch-icon" sizes="144x144" href="{resourceUrl path='img/touch-icon-144.png' type='layout'}" />
		<link rel="shortcut icon" href="{resourceUrl path='img/favicon.ico' type='layout'}">
		<title>{$title|escape}</title>
		{resourceCss file='all.css' type="vendor"}
		{resourceCss file='all.css' type="library"}
		{resourceJs file='before-body.js' type="vendor"}
		{block name='head'}{/block}
	</head>
	<body id="{$viewObj->getAutoId()}" class="{$viewObj->getClassHierarchy()|implode:' '}">

		{if CM_Request_Abstract::hasInstance() && !CM_Request_Abstract::getInstance()->isSupported()}
			<div id="windowBar">
				<div class="content sheet">
					<h2><span class="icon-report"></span> {translate 'Your browser is no longer supported.'}</h2>
					<p>{translate 'We recommend upgrading to the latest Internet Explorer, Google Chrome, Firefox, or Opera. Click here for <a href="{$url}">more information</a>.' url='http://whatbrowser.org'}
					<p>{translate 'If you are using IE 9 or later, make sure you <a href="{$url}">turn off "Compatibility View"</a>.' url='http://windows.microsoft.com/en-us/internet-explorer/use-compatibility-view'}</p>
				</div>
			</div>
		{/if}

		{block name='body-start'}{/block}
		<div id="body-container">
			{block name='body'}
				{component name=$viewObj->getPage()}
			{/block}
		</div>
		{if $smarty.const.IS_DEBUG}{component name='CM_Component_Debug'}{/if}
		{resourceJs file='after-body.js' type="vendor"}
		{resourceJs file='library.js' type="library"}
		{if $render->getLanguage()}
			{resourceJs file="translations/{CM_Model_Language::getVersionJavascript()}.js" type="library"}
		{/if}
		{$render->getJs()->renderScripts()}
		{$render->getJs()->getTracking()->getHtml()}
		{block name='body-end'}{/block}
	</body>
</html>
