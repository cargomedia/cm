<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="description" content="{"{block name='head-description'}{/block}"|escape}">
		<meta name="keywords" content="{"{block name='head-keywords'}{/block}"|escape}">
		<title>{block name='title'}{$pageObj->getTitle()|escape}{/block}</title>
		{resource file='library.css'}
		{resource file='internal.css'}
		{resource file='init.js'}
		{block name='head'}{/block}
	</head>
	<body id="{$pageObj->getAutoId()}" class="{$pageObj->getClassHierarchy()|implode:' '}">
		{block name='body-start'}{/block}
		{block name='body'}{/block}
		{if $smarty.const.IS_DEBUG}{component name='CM_Component_Debug'}{/if}
		{resource file='library.js'}
		{resource file='internal.js'}
		{$render->getJs()->renderScripts()}
		{CM_Tracking::getInstance()->getHtml()}
		{block name='body-end'}{/block}
	</body>
</html>
