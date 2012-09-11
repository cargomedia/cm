<!doctype html>
<html {if $render->getLanguage()}lang="{$render->getLanguage()->getAbbreviation()}"{/if} class="{block name='html-class'}{/block}">
	<head>
		<meta charset="utf-8">
		{if strlen($pageDescription)}<meta name="description" content="{$pageDescription|escape}">{/if}
		{if strlen($pageKeywords)}<meta name="keywords" content="{$pageKeywords|escape}">{/if}
		<title>{block name='title'}{$pageTitle|escape}{/block}</title>
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
		{CM_Tracking::getInstance()->getHtml()}
		{block name='body-end'}{/block}
	</body>
</html>
