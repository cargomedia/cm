<!doctype html>
<html {if $render->getLanguage()}lang="{$render->getLanguage()->getAbbreviation()}"{/if} class="{block name='html-class'}{/block}">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="requiresActiveX=true" />
		{if strlen($pageDescription)}<meta name="description" content="{$pageDescription|escape}">{/if}
		{if strlen($pageKeywords)}<meta name="keywords" content="{$pageKeywords|escape}">{/if}
		<meta name="msapplication-TileColor" content="{block name='tileColor'}#ffffff{/block}">
		<meta name="msapplication-TileImage" content="{imgUrl path='tileImage.png'}">
		<link rel="apple-touch-icon" href="{imgUrl path='touch-icon-57.png'}" />
		<link rel="apple-touch-icon" sizes="72x72" href="{imgUrl path='touch-icon-72.png'}" />
		<link rel="apple-touch-icon" sizes="114x114" href="{imgUrl path='touch-icon-114.png'}" />
		<link rel="apple-touch-icon" sizes="144x144" href="{imgUrl path='touch-icon-144.png'}" />
		<link rel="shortcut icon" href="{imgUrl path='favicon.ico'}">
		<title>{$title|escape}</title>
		{resource file='library.css'}
		{resource file='internal.css'}
		{resource file='init.js'}
		{block name='head'}{/block}
	</head>
	<body id="{$viewObj->getAutoId()}" class="{$viewObj->getClassHierarchy()|implode:' '}">
		{block name='body-start'}{/block}
		{block name='body'}
			{component name=$viewObj->getPage()}
		{/block}
		{if $smarty.const.IS_DEBUG}{component name='CM_Component_Debug'}{/if}
		{resource file='library.js'}
		{resource file='internal.js'}
		{if $render->getLanguage()}
			{resource file="translations/{CM_Model_Language::getVersionJavascript()}.js"}
		{/if}
		{$render->getJs()->renderScripts()}
		{$render->getJs()->getTracking()->getHtml()}
		{block name='body-end'}{/block}
	</body>
</html>
