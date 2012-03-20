<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width">
		<meta name="description" content="{$pageObj->getDescription()|escape}">
		<meta name="keywords" content="{$pageObj->getKeywords()|escape}">
		<title>{block name='title'}{$pageObj->getTitle()|escape}{/block}</title>
		{resource file='library.css'}
		{resource file='internal.css'}
		{resource file='init.js'}
		{block name='head'}{/block}
	</head>
	<body id="{$pageObj->getAutoId()}">
		{block name='body-start'}{/block}
		{block name='body'}{/block}
		{resource file='library.js'}
		{resource file='internal.js'}
		{$render->getJs()->renderScripts()}
		{CM_Tracking::getInstance()->getHtml()}
		{block name='body-end'}{/block}
	</body>
</html>
